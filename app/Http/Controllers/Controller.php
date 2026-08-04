<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Illuminate\View\View;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Réponse AJAX renvoyant un fragment HTML (pour remplacer/ajouter un élément côté client),
     * avec un message flash transmis via en-tête pour ne jamais recharger la page.
     *
     * Le message est encodé en URI : les en-têtes HTTP ne supportent que l'ASCII/Latin-1,
     * ce qui casserait les accents s'il était envoyé tel quel (décodé côté client).
     */
    protected function fragment(View $view, string $message, string $type = 'success'): Response
    {
        return response($view->render())
            ->header('X-Flash-Message', rawurlencode($message))
            ->header('X-Flash-Type', $type);
    }

    /**
     * Réponse AJAX sans contenu (pour une suppression), avec message flash en en-tête.
     */
    protected function ajaxOk(string $message, string $type = 'success'): Response
    {
        return response('', 204)
            ->header('X-Flash-Message', rawurlencode($message))
            ->header('X-Flash-Type', $type);
    }
}
