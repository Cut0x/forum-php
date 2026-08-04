/**
 * Intercepte les formulaires marqués data-remote et les envoie en fetch,
 * pour que publier/modifier/supprimer ne recharge jamais la page.
 *
 * data-remote="replace" data-target="closest:article" -> remplace l'élément cible par le HTML retourné
 * data-remote="replace" data-target="#selecteur"       -> idem, cible par sélecteur CSS
 * data-remote="append"  data-target="#selecteur"       -> ajoute le HTML retourné en fin de cible
 * data-remote="prepend" data-target="#selecteur"       -> ajoute le HTML retourné en début de cible
 * data-remote="remove"  data-target="closest:article"  -> supprime l'élément cible (aucun contenu attendu)
 * data-remote="none"                                    -> n'effectue aucune manipulation du DOM (juste le fetch + toast)
 *
 * Le serveur peut renvoyer les en-têtes X-Flash-Message / X-Flash-Type pour afficher un toast.
 * Après un remplacement, l'événement "remote:replaced" est déclenché sur le nouvel élément.
 */
function resolveTarget(form, selector) {
    if (!selector) return null;
    if (selector.startsWith('closest:')) {
        return form.closest(selector.slice('closest:'.length));
    }

    return document.querySelector(selector);
}

function toast(message, type) {
    if (!message) return;
    window.dispatchEvent(new CustomEvent('toast', { detail: { message, type: type || 'success' } }));
}

async function handleSubmit(form, submitter) {
    const mode = form.dataset.remote;
    const targetSelector = form.dataset.target;

    const formData = new FormData(form);
    if (submitter && submitter.name) {
        formData.append(submitter.name, submitter.value);
    }

    const buttons = form.querySelectorAll('button[type=submit]');
    buttons.forEach((button) => (button.disabled = true));

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json, text/html;q=0.9',
            },
            body: formData,
        });

        if (response.status === 422) {
            const data = await response.json();
            const firstError = Object.values(data.errors || {})[0]?.[0] || data.message || 'Le formulaire contient des erreurs.';
            toast(firstError, 'error');
            return;
        }

        if (!response.ok) {
            toast('Une erreur est survenue, merci de réessayer.', 'error');
            return;
        }

        const rawFlashMessage = response.headers.get('X-Flash-Message');
        const flashMessage = rawFlashMessage ? decodeURIComponent(rawFlashMessage) : null;
        const flashType = response.headers.get('X-Flash-Type') || 'success';
        const unreadCount = response.headers.get('X-Unread-Count');
        if (unreadCount !== null) {
            window.dispatchEvent(new CustomEvent('unread-count', { detail: { count: parseInt(unreadCount, 10) } }));
        }

        if (mode === 'remove') {
            resolveTarget(form, targetSelector)?.remove();
        } else if (mode !== 'none') {
            const html = (await response.text()).trim();
            const target = resolveTarget(form, targetSelector);
            if (target && html !== '') {
                if (mode === 'append') {
                    target.insertAdjacentHTML('beforeend', html);
                } else if (mode === 'prepend') {
                    target.insertAdjacentHTML('afterbegin', html);
                } else {
                    target.outerHTML = html;
                }
            }
            if (form.dataset.resetOnSuccess !== undefined && mode !== 'replace') {
                form.reset();
            }
        }

        toast(flashMessage, flashType);
    } catch (error) {
        toast('Impossible de contacter le serveur.', 'error');
    } finally {
        buttons.forEach((button) => (button.disabled = false));
    }
}

export function initRemoteForms() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-remote]');
        if (!form) return;

        event.preventDefault();
        handleSubmit(form, event.submitter);
    });
}
