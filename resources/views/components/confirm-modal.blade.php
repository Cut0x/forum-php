{{-- Modale de confirmation unique et partagée par toute l'application.
     Déclenchée en émettant un événement "open-confirm" (voir <x-confirm-submit>),
     jamais de window.confirm()/alert() natif qui bloquerait le navigateur. --}}
<div
    x-data="{
        open: false,
        title: '',
        message: '',
        confirmLabel: 'Confirmer',
        cancelLabel: 'Annuler',
        danger: false,
        onConfirm: null,
        show(detail) {
            this.title = detail.title || 'Confirmer';
            this.message = detail.message || '';
            this.confirmLabel = detail.confirmLabel || 'Confirmer';
            this.cancelLabel = detail.cancelLabel || 'Annuler';
            this.danger = !!detail.danger;
            this.onConfirm = detail.onConfirm || null;
            this.open = true;
        },
        confirm() {
            const callback = this.onConfirm;
            this.open = false;
            if (callback) callback();
        },
    }"
    @open-confirm.window="show($event.detail)"
    @keydown.escape.window="open = false"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-[70] bg-ink/40 backdrop-blur-sm"
        @click="open = false"
    ></div>

    <div x-show="open" class="pointer-events-none fixed inset-0 z-[70] flex items-center justify-center p-4">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="card pointer-events-auto w-full max-w-sm p-5 shadow-xl"
            role="alertdialog"
            aria-modal="true"
            :aria-label="title"
        >
            <h2 class="text-base font-semibold text-ink" x-text="title"></h2>
            <p class="mt-2 text-sm text-muted" x-show="message" x-text="message"></p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" class="btn-ghost" @click="open = false" x-text="cancelLabel"></button>
                <button
                    type="button"
                    class="btn text-white"
                    :class="danger ? 'bg-red-600 hover:bg-red-700' : 'bg-brand hover:opacity-90'"
                    @click="confirm()"
                    x-text="confirmLabel"
                ></button>
            </div>
        </div>
    </div>
</div>
