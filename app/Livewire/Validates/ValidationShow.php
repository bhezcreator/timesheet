<?php

namespace App\Livewire\Validates;

use App\Models\MonthlyReport;
use App\Models\ReportValidation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Events\ReportValidatedOrRejected; // Remplacez par votre classe d'événement réelle
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class ValidationShow extends Component
{
    public MonthlyReport $report;

    // Propriétés pour le formulaire de validation
    public $decision = '';
    public $comment = '';

    protected $rules = [
        'decision' => 'required|in:approuvé,rejeté',
        'comment' => 'required_if:decision,rejeté|nullable|string|min:10',
    ];

    protected $messages = [
        'decision.required' => 'Veuillez choisir une action (Valider ou Rejeter).',
        'comment.required_if' => 'Un motif est obligatoire en cas de rejet du rapport.',
        'comment.min' => 'Le motif de rejet doit faire au moins 10 caractères.',
    ];

    public function mount(MonthlyReport $report)
    {
        // Chargement optimisé des relations pour éviter le problème N+1
        $this->report = $report->load(['user', 'activities', 'media', 'validation.validator']);
    }

    public function submitValidation()
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Enregistrement ou mise à jour de la validation
            ReportValidation::updateOrCreate(
                ['monthly_report_id' => $this->report->id],
                [
                    'validator_id' => Auth::id(),
                    'decision' => $this->decision,
                    'comment' => $this->comment,
                    'validated_at' => now(),
                ]
            );

            $targetStatus = $this->decision === 'Validé' ? 'approuvé' : 'rejeté';

            // 2. Mise à jour du statut du rapport global
            $this->report->update([
                'status' => $targetStatus
            ]);

            // 3. Préparation des données pour les activités
            $activityUpdateData = ['status' => $targetStatus];

            // Copie du commentaire dans la colonne rejection_reason uniquement en cas de rejet
            if ($this->decision === 'Rejeté') {
                $activityUpdateData['rejection_reason'] = $this->comment;
            }

            // Mise à jour en cascade de toutes les activités rattachées
            $this->report->activities()->update($activityUpdateData);
        });

        // 4. Déclenchement de l'événement (à l'extérieur de la transaction pour éviter les faux-départs)
        event(new ReportValidatedOrRejected($this->report));

        // 5. Notification Flash de succès & Redirection
        session()->flash('message', 'Le traitement du rapport a été sécurisé et enregistré avec succès.');

        return redirect()->route('validations.supervisor');
    }


    public function render()
    {
        return view('livewire.validates.validation-show'); // Ajustez selon votre layout global
    }
}
