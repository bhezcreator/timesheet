<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\MonthlyReport;
use App\Events\UniversalModelStatusChanged;

class TestNotificationSound extends Command
{
    /**
     * Le nom et la signature de la commande de console.
     */
    protected $signature = 'test:notify {user_id}';

    /**
     * La description de la commande de console.
     */
    protected $signature_description = 'Déclenche une notification universelle temps réel pour tester le son et le système de diffusion.';

    /**
     * Exécutez la commande de console.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("Utilisateur introuvable avec l'ID : {$userId}");
            return Command::FAILURE;
        }

        // Récupération d'un rapport existant pour le test, ou création d'une instance à la volée
        $report = MonthlyReport::first() ?? new MonthlyReport(['id' => 1, 'month' => 12, 'year' => 2026]);

        $this->info("Envoi du signal de notification à {$user->name}...");

        // Déclenchement de l'événement universel que nous avons créé
        event(new UniversalModelStatusChanged(
            model: $report,
            recipient: $user,
            title: "Test de Signal Audio",
            messageContent: "Félicitations, votre système d'alerte en temps réel avec Pusher fonctionne parfaitement !",
            status: "approuvé",
            comment: "Test réussi avec succès.",
            routeUrl: "#",
            icon: "las la-volume-up text-indigo-600 animate-bounce"
        ));

        $this->info("Événement diffusé avec succès ! Vérifiez votre navigateur.");
        return Command::SUCCESS;
    }
}
