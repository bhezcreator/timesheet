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
        // Ouverture
        this.openButtons.forEach((button) => {
            button.addEventListener("click", () => {
                this.open();
            });
        });

        // Fermeture boutons
        this.closeButtons.forEach((button) => {
            button.addEventListener("click", () => {
                this.close();
            });
        });

        // Ouverture depuis Livewire
        window.addEventListener("open-modal", (event) => {
            if (event.detail.id === this.id) {
                this.open();
            }
        });

        // Fermeture depuis Livewire
        window.addEventListener("close-modal", (event) => {
            if (event.detail === this.id) {
                this.close();
            }
        });

        // Fermeture overlay
        this.modal
            .querySelector(".modal-overlay")
            ?.addEventListener("click", () => {
                this.close();
            });

        // Escape
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                this.close();
            }
        });
    }

    open() {
        this.modal.classList.remove("hidden");

        document.body.classList.add("overflow-hidden");
    }

    close() {
        this.modal.classList.add("hidden");

        document.body.classList.remove("overflow-hidden");
    }
}

// Initialisation automatique
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-modal]").forEach((element) => {
        new Modal(element);
    });
});
