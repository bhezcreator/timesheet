// class Modal {
//     constructor(element) {
//         this.modal = element;
//         this.id = this.modal.dataset.modal;

//         this.openButtons = document.querySelectorAll(
//             `[data-open-modal="${this.id}"]`,
//         );

//         this.closeButtons = this.modal.querySelectorAll("[data-close-modal]");

//         this.init();
//     }

//     init() {
//         // Ouverture
//         this.openButtons.forEach((button) => {
//             button.addEventListener("click", () => {
//                 this.open();
//             });
//         });

//         // Fermeture boutons
//         this.closeButtons.forEach((button) => {
//             button.addEventListener("click", () => {
//                 this.close();
//             });
//         });

//         // Ouverture depuis Livewire
//         window.addEventListener("open-modal", (event) => {
//             if (event.detail.id === this.id) {
//                 this.open();
//             }
//         });

//         // Fermeture depuis Livewire
//         window.addEventListener("close-modal", (event) => {
//             if (event.detail === this.id) {
//                 this.close();
//             }
//         });

//         // Fermeture overlay
//         this.modal
//             .querySelector(".modal-overlay")
//             ?.addEventListener("click", () => {
//                 this.close();
//             });

//         // Escape
//         document.addEventListener("keydown", (event) => {
//             if (event.key === "Escape") {
//                 this.close();
//             }
//         });
//     }

//     open() {
//         this.modal.classList.remove("hidden");

//         document.body.classList.add("overflow-hidden");
//     }

//     close() {
//         this.modal.classList.add("hidden");

//         document.body.classList.remove("overflow-hidden");
//     }
// }

// // Initialisation automatique
// document.addEventListener("DOMContentLoaded", () => {
//     document.querySelectorAll("[data-modal]").forEach((element) => {
//         new Modal(element);
//     });
// });

class Modal {
    constructor(element) {
        this.modal = element;
        this.id = this.modal.dataset.modal;

        this.openButtons = document.querySelectorAll(
            `[data-open-modal="${this.id}"]`,
        );

        this.closeButtons = this.modal.querySelectorAll("[data-close-modal]");

        this.init();
    }

    init() {
        // === NOUVEAU : Vérification de l'état au chargement initial ===
        const savedState = localStorage.getItem(`modal_${this.id}`);
        if (savedState === "open") {
            this.open(false); // On ouvre sans écraser le localStorage inutilement
        }

        // Ouverture via boutons physiques
        this.openButtons.forEach((button) => {
            button.addEventListener("click", () => {
                this.open();
            });
        });

        // Fermeture via boutons physiques
        this.closeButtons.forEach((button) => {
            button.addEventListener("click", () => {
                this.close();
            });
        });

        // Ouverture depuis Livewire (Adapté à votre syntaxe compacte id: 'nom')
        window.addEventListener("open-modal", (event) => {
            if (event.detail.id === this.id || event.detail === this.id) {
                this.open();
            }
        });

        // Fermeture depuis Livewire
        window.addEventListener("close-modal", (event) => {
            if (event.detail.id === this.id || event.detail === this.id) {
                this.close();
            }
        });

        // Fermeture via clic sur l'arrière-plan (overlay)
        this.modal
            .querySelector(".modal-overlay")
            ?.addEventListener("click", () => {
                this.close();
            });

        // Fermeture via la touche Escape
        document.addEventListener("keydown", (event) => {
            if (
                event.key === "Escape" &&
                !this.modal.classList.contains("hidden")
            ) {
                this.close();
            }
        });
    }

    // saveState = true par défaut pour enregistrer l'action de l'utilisateur
    open(saveState = true) {
        this.modal.classList.remove("hidden");
        document.body.classList.add("overflow-hidden");

        if (saveState) {
            localStorage.setItem(`modal_${this.id}`, "open");
        }
    }

    close() {
        this.modal.classList.add("hidden");
        document.body.classList.remove("overflow-hidden");

        // On supprime l'état pour que la modale reste fermée au prochain rechargement
        localStorage.removeItem(`modal_${this.id}`);
    }
}

// Initialisation automatique universelle
function initAllModals() {
    document.querySelectorAll("[data-modal]").forEach((element) => {
        if (!element.dataset.modalInitialized) {
            new Modal(element);
            element.dataset.modalInitialized = "true";
        }
    });
}

document.addEventListener("DOMContentLoaded", initAllModals);
document.addEventListener("livewire:navigated", initAllModals); // Support de la navigation wire:navigate
