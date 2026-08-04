<x-admin-layout title="Réglages du site" active="settings">
    <form method="post" action="{{ route('admin.settings.update') }}" class="card space-y-4 p-5">
        @csrf @method('patch')

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-ink">Titre du site</label>
                <input type="text" name="site_title" value="{{ old('site_title', $siteSettings['site_title']) }}" class="field" required>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-ink">Description</label>
                <input type="text" name="site_description" value="{{ old('site_description', $siteSettings['site_description']) }}" class="field" required>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-ink">Texte du footer</label>
                <input type="text" name="footer_text" value="{{ old('footer_text', $siteSettings['footer_text']) }}" class="field" required>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-ink">Lien du footer</label>
                <input type="url" name="footer_link" value="{{ old('footer_link', $siteSettings['footer_link']) }}" class="field" placeholder="https://…">
            </div>
        </div>

        <div class="border-t border-ink/10 pt-4">
            <label class="flex items-center gap-2 text-sm text-ink">
                <input type="checkbox" name="stripe_enabled" value="1" {{ $siteSettings['stripe_enabled'] === '1' ? 'checked' : '' }} class="rounded border-ink/20">
                Activer le bouton de soutien (Stripe)
            </label>
            <div class="mt-2">
                <label class="mb-1 block text-xs font-medium text-ink">URL Stripe</label>
                <input type="url" name="stripe_url" value="{{ old('stripe_url', $siteSettings['stripe_url']) }}" class="field" placeholder="https://buy.stripe.com/…">
            </div>
        </div>

        <button type="submit" class="btn-primary">Enregistrer</button>
    </form>
</x-admin-layout>
