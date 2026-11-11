<?php

namespace App\Console\Commands;

use App\Models\Office;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateOfficeQRCodes extends Command
{
    protected $signature = 'qrcode:generate-offices {--force : Forcer la régénération}';
    protected $description = 'Génère les QR codes pour tous les bureaux actifs';

    public function handle()
    {
        $this->info('Génération des QR codes pour les bureaux...');

        // Créer le dossier
        if (!Storage::disk('public')->exists('qrcodes/offices')) {
            Storage::disk('public')->makeDirectory('qrcodes/offices');
            $this->info('📁 Dossier créé: qrcodes/offices');
        }

        $offices = Office::where('is_active', true)->get();
        
        if ($offices->isEmpty()) {
            $this->warn('⚠️ Aucun bureau actif trouvé.');
            return 0;
        }

        $this->info("📋 {$offices->count()} bureaux trouvés.");
        
        $bar = $this->output->createProgressBar($offices->count());
        $bar->start();

        $generated = 0;
        $errors = 0;

        foreach ($offices as $office) {
            try {
                // Si --force est utilisé, supprimer l'ancien QR
                if ($this->option('force') && $office->qr_code_path) {
                    Storage::disk('public')->delete($office->qr_code_path);
                }

                // Générer l'URL
                $url = route('presente.scanner', ['office' => $office->id]);
                
                // Générer le QR code avec Simple QR Code
                $qrCode = QrCode::format('png')
                    ->size(300)
                    ->margin(10)
                    ->errorCorrection('H')
                    ->generate($url);
                
                // Nom du fichier
                $filename = 'office_' . $office->id . '_' . $office->code . '.png';
                $path = 'qrcodes/offices/' . $filename;
                
                // Sauvegarder
                Storage::disk('public')->put($path, $qrCode);
                
                // Mettre à jour le modèle
                $office->update(['qr_code_path' => $path]);
                
                $generated++;
                
            } catch (\Exception $e) {
                $this->error("\n Erreur pour {$office->name}: " . $e->getMessage());
                $errors++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        
        $this->newLine(2);
        $this->info("✅ QR codes générés: {$generated}");
        
        if ($errors > 0) {
            $this->warn("⚠️ Erreurs: {$errors}");
        }

        return 0;
    }
}