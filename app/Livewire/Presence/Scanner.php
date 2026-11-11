<?php

namespace App\Livewire\Presence;

use App\Models\AttendanceRecord;
use App\Models\Office;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Scanner extends Component
{
    public $scanResult = '';
    public $isProcessing = false;
    public $showResultModal = false;
    public $attendanceStatus = null;
    public $attendanceMessage = '';
    public $attendanceDetails = [];

    // Données GPS
    public $latitude = null;
    public $longitude = null;
    public $accuracy = null;

    protected $listeners = [
        'processScan' => 'processScan',
        'updateLocation' => 'updateLocation'
    ];

    public function mount()
    {
        // Vérifier que l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        return view('livewire.presence.scanner');
    }

    /**
     * Met à jour la position GPS depuis le JavaScript
     */
    public function updateLocation($latitude, $longitude, $accuracy)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->accuracy = $accuracy;
    }

    /**
     * Traite le QR code scanné
     */
    public function processScan($qrContent)
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;
        $this->scanResult = $qrContent;

        try {
            // 1. Vérifier que l'utilisateur est connecté
            $user = Auth::user();
            if (!$user) {
                $this->showResult('error', 'Vous devez être connecté pour effectuer un pointage.');
                return;
            }

            // 2. Extraire l'ID du bureau depuis le QR code
            $officeId = $this->extractOfficeIdFromQR($qrContent);
            if (!$officeId) {
                $this->showResult('error', 'QR Code invalide. Veuillez scanner un QR code valide.');
                return;
            }

            // 3. Récupérer le bureau
            $office = Office::where('id', $officeId)->where('is_active', true)->first();
            if (!$office) {
                $this->showResult('error', 'Bureau non trouvé ou désactivé.');
                return;
            }

            // 4. Vérifier la position GPS
            $gpsCheck = $this->checkGPS($office);
            if (!$gpsCheck['valid']) {
                $this->showResult('error', $gpsCheck['message'], $gpsCheck['details']);
                return;
            }

            // 5. Vérifier les horaires
            $scheduleCheck = $this->checkSchedule($office);
            if (!$scheduleCheck['valid']) {
                $this->showResult('error', $scheduleCheck['message'], $scheduleCheck['details']);
                return;
            }

            // 6. Vérifier les doublons
            $duplicateCheck = $this->checkDuplicate($office);
            if (!$duplicateCheck['valid']) {
                $this->showResult('warning', $duplicateCheck['message'], $duplicateCheck['details']);
                return;
            }

            // 7. Détection de fraude
            $fraudCheck = $this->checkFraud($office);
            if (!$fraudCheck['valid']) {
                $this->showResult('error', $fraudCheck['message'], $fraudCheck['details']);
                return;
            }

            // 8. Enregistrer la présence
            $attendance = $this->saveAttendance($office, $user);

            // 9. Succès
            $this->showResult('success', 'Pointage enregistré avec succès !', [
                'office' => $office->name,
                'time' => Carbon::now()->format('H:i:s'),
                'date' => Carbon::now()->format('d/m/Y'),
                'distance' => $gpsCheck['distance'] ?? null,
                'status' => $attendance->status,
                'type' => $this->getTypeLabel($attendance->type),
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du scan QR:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->showResult('error', 'Une erreur est survenue lors du traitement. Veuillez réessayer.');
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Extrait l'ID du bureau depuis le QR code
     */
    protected function extractOfficeIdFromQR($qrContent)
    {
        // Format attendu: https://exemple.com/presence/scanner/123 ou presence/office/123
        preg_match('/(?:presence|office)[\/:](\d+)/i', $qrContent, $matches);
        
        if (isset($matches[1])) {
            return (int) $matches[1];
        }

        // Essayer de parser comme JSON
        $data = json_decode($qrContent, true);
        if ($data && isset($data['office_id'])) {
            return (int) $data['office_id'];
        }

        // Essayer de parser comme paramètre URL
        parse_str(parse_url($qrContent, PHP_URL_QUERY) ?? '', $params);
        if (isset($params['office'])) {
            return (int) $params['office'];
        }

        return null;
    }

    /**
     * Vérifie la position GPS
     */
    protected function checkGPS($office)
    {
        // Si le bureau n'a pas de coordonnées GPS, on passe
        if (!$office->latitude || !$office->longitude) {
            return ['valid' => true, 'message' => 'GPS non requis pour ce bureau.'];
        }

        // Vérifier que l'utilisateur a partagé sa position
        if ($this->latitude === null || $this->longitude === null) {
            return [
                'valid' => false, 
                'message' => 'Veuillez autoriser l\'accès à votre position GPS.',
                'details' => ['gps_required' => true]
            ];
        }

        // Calculer la distance
        $distance = $this->calculateDistance(
            $office->latitude,
            $office->longitude,
            $this->latitude,
            $this->longitude
        );

        $radius = $office->radius ?? 100; // Rayon par défaut: 100m

        if ($distance > $radius) {
            return [
                'valid' => false,
                'message' => "Vous êtes trop éloigné du bureau ({$distance}m). Distance maximum: {$radius}m.",
                'details' => [
                    'distance' => round($distance, 0),
                    'max_distance' => $radius,
                    'office_location' => [$office->latitude, $office->longitude],
                    'user_location' => [$this->latitude, $this->longitude]
                ]
            ];
        }

        // Vérifier la précision GPS
        if ($this->accuracy && $this->accuracy > 50) {
            return [
                'valid' => false,
                'message' => "La précision GPS est insuffisante ({$this->accuracy}m). Précision requise: < 50m.",
                'details' => ['accuracy' => $this->accuracy, 'required_accuracy' => 50]
            ];
        }

        return [
            'valid' => true,
            'message' => 'Position GPS validée.',
            'distance' => $distance
        ];
    }

    /**
     * Calcule la distance entre deux points GPS (formule de Haversine)
     */
    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Rayon de la Terre en mètres

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Vérifie les horaires de travail
     */
    protected function checkSchedule($office)
    {
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek; // 0 = Dimanche, 1 = Lundi, ...

        // Récupérer l'horaire pour ce jour
        $schedule = WorkSchedule::where('office_id', $office->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return [
                'valid' => false,
                'message' => 'Aucun horaire défini pour ce jour dans ce bureau.',
                'details' => ['day' => $this->getDayName($dayOfWeek)]
            ];
        }

        $startTime = Carbon::parse($schedule->start_time);
        $endTime = Carbon::parse($schedule->end_time);
        $gracePeriod = $schedule->grace_period ?? 15;

        // Vérifier si on est dans la plage horaire (avec période de grâce)
        $startWithGrace = $startTime->copy()->addMinutes($gracePeriod);

        // Déterminer le type de pointage (arrivée ou départ)
        $type = 'check_in';
        $status = 'present';

        if ($now->lt($startTime)) {
            // Trop tôt
            return [
                'valid' => false,
                'message' => "Le pointage commence à {$startTime->format('H:i')}. Veuillez patienter.",
                'details' => ['start_time' => $startTime->format('H:i')]
            ];
        } elseif ($now->gt($startTime) && $now->lte($startWithGrace)) {
            // Dans la période de grâce
            $status = 'present';
        } elseif ($now->gt($startWithGrace) && $now->lte($endTime)) {
            // En retard
            $status = 'late';
        } elseif ($now->gt($endTime)) {
            // Après la fin
            return [
                'valid' => false,
                'message' => "Le pointage est terminé depuis {$endTime->format('H:i')}.",
                'details' => ['end_time' => $endTime->format('H:i')]
            ];
        }

        // Vérifier si c'est un départ (après midi)
        $midDay = Carbon::parse('12:00');
        if ($now->gt($midDay)) {
            $type = 'check_out';
        }

        return [
            'valid' => true,
            'message' => 'Horaire validé.',
            'details' => [
                'schedule' => $schedule->name,
                'type' => $type,
                'status' => $status,
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'grace_period' => $gracePeriod
            ],
            'type' => $type,
            'status' => $status
        ];
    }

    /**
     * Vérifie les doublons
     */
    protected function checkDuplicate($office)
    {
        $user = Auth::user();
        $today = Carbon::now()->format('Y-m-d');

        // Vérifier si l'utilisateur a déjà pointé aujourd'hui
        $existing = AttendanceRecord::where('user_id', $user->id)
            ->where('office_id', $office->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existing) {
            return [
                'valid' => false,
                'message' => "Vous avez déjà pointé aujourd'hui à {$existing->recorded_at->format('H:i')}.",
                'details' => [
                    'previous_scan' => $existing->recorded_at->format('H:i:s'),
                    'status' => $existing->status
                ]
            ];
        }

        // Vérifier les doublons potentiels (même IP, même GPS, etc.)
        $recentScans = AttendanceRecord::where('user_id', $user->id)
            ->where('ip_address', request()->ip())
            ->where('recorded_at', '>=', Carbon::now()->subMinutes(5))
            ->count();

        if ($recentScans > 3) {
            return [
                'valid' => false,
                'message' => 'Trop de tentatives de pointage en peu de temps. Veuillez patienter.',
                'details' => ['attempts' => $recentScans]
            ];
        }

        return ['valid' => true, 'message' => 'Aucun doublon détecté.'];
    }

    /**
     * Détection de fraude
     */
    protected function checkFraud($office)
    {
        $user = Auth::user();

        // 1. Vérifier les changements d'IP rapides
        $ipChanges = AttendanceRecord::where('user_id', $user->id)
            ->where('recorded_at', '>=', Carbon::now()->subMinutes(10))
            ->distinct('ip_address')
            ->count();

        if ($ipChanges > 2) {
            return [
                'valid' => false,
                'message' => 'Détection de multiples adresses IP. Opération suspecte.',
                'details' => ['ip_changes' => $ipChanges]
            ];
        }

        // 2. Vérifier la cohérence GPS
        if ($this->latitude && $this->longitude) {
            $lastLocation = AttendanceRecord::where('user_id', $user->id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->latest('recorded_at')
                ->first();

            if ($lastLocation) {
                $distance = $this->calculateDistance(
                    $lastLocation->latitude,
                    $lastLocation->longitude,
                    $this->latitude,
                    $this->longitude
                );

                // Si l'utilisateur a bougé de plus de 500m en moins de 5 minutes
                if ($distance > 500 && $lastLocation->recorded_at->diffInMinutes(Carbon::now()) < 5) {
                    return [
                        'valid' => false,
                        'message' => "Déplacement suspect détecté ({$distance}m en moins de 5 minutes).",
                        'details' => ['distance' => round($distance, 0)]
                    ];
                }
            }
        }

        // 3. Vérifier le nombre de pointages par jour
        $todayCount = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('attendance_date', Carbon::today())
            ->count();

        if ($todayCount > 5) {
            return [
                'valid' => false,
                'message' => 'Trop de pointages pour aujourd\'hui. Opération suspecte.',
                'details' => ['today_count' => $todayCount]
            ];
        }

        return ['valid' => true, 'message' => 'Aucune fraude détectée.'];
    }

    /**
     * Sauvegarde la présence
     */
    protected function saveAttendance($office, $user)
    {
        // Déterminer le type et le statut
        $scheduleCheck = $this->checkSchedule($office);
        $type = $scheduleCheck['type'] ?? 'check_in';
        $status = $scheduleCheck['status'] ?? 'present';

        // Vérifier si c'est le premier pointage de la journée (check_in)
        $today = Carbon::now()->format('Y-m-d');
        $existing = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->exists();

        if ($existing) {
            $type = 'check_out';
        }

        // Générer un token unique pour le QR
        $qrToken = bin2hex(random_bytes(16));

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'office_id' => $office->id,
            'type' => $type,
            'attendance_date' => Carbon::today(),
            'recorded_at' => Carbon::now(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'accuracy' => $this->accuracy,
            'distance' => $this->calculateDistance(
                $office->latitude ?? 0,
                $office->longitude ?? 0,
                $this->latitude ?? 0,
                $this->longitude ?? 0
            ),
            'qr_token' => $qrToken,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => $status,
            'reason' => $status === 'late' ? 'Retard' : null,
        ]);

        // Log d'audit
        Log::info('Nouvelle présence enregistrée', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'office_id' => $office->id,
            'office_name' => $office->name,
            'type' => $type,
            'status' => $status,
            'ip_address' => request()->ip(),
            'attendance_id' => $attendance->id
        ]);

        return $attendance;
    }

    /**
     * Affiche le résultat
     */
    protected function showResult($status, $message, $details = [])
    {
        $this->attendanceStatus = $status;
        $this->attendanceMessage = $message;
        $this->attendanceDetails = $details;
        $this->showResultModal = true;

        // Dispatch d'événement pour le frontend
        $this->dispatch('attendanceResult', [
            'status' => $status,
            'message' => $message,
            'details' => $details
        ]);
    }

    /**
     * Ferme la modale de résultat
     */
    public function closeResultModal()
    {
        $this->showResultModal = false;
        $this->attendanceStatus = null;
        $this->attendanceMessage = '';
        $this->attendanceDetails = [];
        $this->scanResult = '';

        // Rediriger vers le dashboard après succès
        if ($this->attendanceStatus === 'success') {
            return redirect()->route('dashboard');
        }
    }

    /**
     * Retourne le nom du jour
     */
    protected function getDayName($day)
    {
        $days = [
            0 => 'Dimanche',
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
        ];
        return $days[$day] ?? 'Inconnu';
    }

    /**
     * Retourne le libellé du type
     */
    protected function getTypeLabel($type)
    {
        return match ($type) {
            'check_in' => 'Arrivée',
            'check_out' => 'Départ',
            default => ucfirst($type),
        };
    }

    /**
     * Retourne la couleur du statut
     */
    public function getStatusColor($status)
    {
        return match ($status) {
            'success' => 'emerald',
            'error' => 'red',
            'warning' => 'amber',
            default => 'gray',
        };
    }

    /**
     * Retourne l'icône du statut
     */
    public function getStatusIcon($status)
    {
        return match ($status) {
            'success' => 'la-check-circle',
            'error' => 'la-times-circle',
            'warning' => 'la-exclamation-triangle',
            default => 'la-info-circle',
        };
    }

    /**
     * Retourne le titre du statut
     */
    public function getStatusTitle($status)
    {
        return match ($status) {
            'success' => 'Félicitations !',
            'error' => 'Erreur',
            'warning' => 'Attention',
            default => 'Information',
        };
    }
}