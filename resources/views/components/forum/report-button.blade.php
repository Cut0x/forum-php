@props(['reportable', 'route'])

<div class="relative" x-data="{ open: false }">
    <button type="button" @click="open = !open" @click.outside="open = false" class="text-muted hover:text-red-600">
        Signaler
    </button>
    <div x-show="open" x-cloak x-transition class="card absolute right-0 z-10 mt-2 w-56 p-3 text-left shadow-lg">
        <form method="post" action="{{ route($route, $reportable) }}" data-remote="none" @submit="open = false">
            @csrf
            <label class="text-xs font-medium text-ink">Raison</label>
            <select name="reason" class="field mt-1 text-xs" required>
                <option value="spam">Spam</option>
                <option value="abus">Abus / harcèlement</option>
                <option value="hors_sujet">Hors sujet</option>
                <option value="autre">Autre</option>
            </select>
            <textarea name="note" rows="2" placeholder="Précisions (optionnel)" class="field mt-2 text-xs"></textarea>
            <button type="submit" class="btn-primary mt-2 w-full !py-1.5 text-xs">Envoyer le signalement</button>
        </form>
    </div>
</div>
