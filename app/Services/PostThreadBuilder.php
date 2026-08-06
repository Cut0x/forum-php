<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Collection;

class PostThreadBuilder
{
    /**
     * Transforme une liste plate de messages (triée par date de création) en arbre de discussion :
     * chaque message reçoit un attribut dynamique `childrenTree` contenant ses réponses directes,
     * elles-mêmes déjà pourvues de leur propre `childrenTree`, etc.
     *
     * Une seule passe en mémoire (aucune requête récursive) : adapté à la taille de ce forum.
     *
     * @return Collection<int, Post> Les messages racine (sans parent), avec leur arbre attaché.
     */
    public function build(Collection $posts): Collection
    {
        $byParent = $posts->groupBy(fn (Post $post) => $post->parent_id ?? 'root');

        $attach = function (Post $post) use (&$attach, $byParent) {
            $post->childrenTree = ($byParent->get($post->id) ?? collect())->values();
            $post->childrenTree->each($attach);
        };

        $roots = ($byParent->get('root') ?? collect())->values();
        $roots->each($attach);

        return $roots;
    }
}
