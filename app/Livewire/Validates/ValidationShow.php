<?php

namespace App\Livewire\Validates;

use App\Events\UniversalModelStatusChanged;
use App\Models\MonthlyReport;
use App\Models\ReportValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ValidationShow extends Component
{
    public MonthlyReport $report;

    // Propriétés pour le formulaire de validation
    public $decision = '';
    public $comment = '';

    protected $rules = [
        'decision' => 'required|in:Validé,Rejeté',
        'comment' => 'required_if:Validé,Rejeté|nullable|string|min:10',
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
            $targetStatus = $this->decision === 'Validé' ? 'approuvé' : 'rejeté';
            // 1. Enregistrement ou mise à jour de la validation
            ReportValidation::updateOrCreate(
                ['monthly_report_id' => $this->report->id],
                [
                    'validator_id' => Auth::id(),
                    'decision' => $targetStatus,
                    'comment' => $this->comment,
                    'validated_at' => now(),
                ]
            );

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

        // 4. Déclenchement universel pour votre cas de Rapport Mensuel
        event(new UniversalModelStatusChanged(
            model: $this->report,
            recipient: $this->report->user, // L'agent recevra la notification
            title: "Mise à jour : " . $this->report->full_title,
            messageContent: "Votre rapport mensuel a été traité par le superviseur.",
            status: $this->decision === 'Validé' ? 'approuvé' : 'rejeté',
            comment: $this->comment,
            routeUrl: route('validations.show', $this->report->id),
            icon: $this->decision === 'Validé' ? 'las la-check-circle text-emerald-500' : 'las la-times-circle text-rose-500'
        ));

        // 5. Notification Flash de succès & Redirection
        session()->flash('message', 'Le traitement du rapport a été sécurisé et enregistré avec succès.');
        return redirect()->route('validations.supervisor');
    }

    public function render()
    {
        return view('livewire.validates.validation-show');
    }
}
