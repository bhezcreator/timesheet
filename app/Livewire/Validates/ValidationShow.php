<?php

namespace App\Livewire\Validates;

use App\Models\MonthlyReport;
use App\Models\ReportValidation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

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

        // 2. Mise à jour du statut du rapport global
        $this->report->update([
            'status' => $this->decision == 'Validé' ? 'approuvé' : 'rejeté'
        ]);

        // 3. Notification Flash de succès
        session()->flash('message', 'Le traitement du rapport a été effectué avec succès.');

        // 4. Redirection demandée
        return redirect()->route('validations.supervisor');
    }

    public function render()
    {
        return view('livewire.validates.validation-show'); // Ajustez selon votre layout global
    }
}
