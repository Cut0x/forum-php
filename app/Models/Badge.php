<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'code', 'icon', 'color', 'rule_type', 'rule_value'])]
class Badge extends Model
{
    public const RULE_MANUAL = 'manual';

    public const RULE_POSTS_COUNT = 'posts_count';

    public const RULE_TOPICS_COUNT = 'topics_count';

    public const RULE_ACCOUNT_AGE_DAYS = 'account_age_days';

    public const RULE_ROLE = 'role';

    /**
     * Types de règle disponibles pour l'attribution automatique d'un badge,
     * avec leur libellé pour le sélecteur du panel admin.
     */
    public static function ruleTypes(): array
    {
        return [
            self::RULE_MANUAL => 'Attribution manuelle uniquement',
            self::RULE_POSTS_COUNT => 'Nombre de messages ≥',
            self::RULE_TOPICS_COUNT => 'Nombre de sujets ≥',
            self::RULE_ACCOUNT_AGE_DAYS => 'Ancienneté du compte (jours) ≥',
            self::RULE_ROLE => 'Attribué automatiquement au rôle',
        ];
    }

    /**
     * Types de règle dont la valeur est un seuil numérique (par opposition à "role",
     * dont la valeur est un nom de rôle) — utile pour la validation et l'affichage du formulaire.
     */
    public static function numericRuleTypes(): array
    {
        return [self::RULE_POSTS_COUNT, self::RULE_TOPICS_COUNT, self::RULE_ACCOUNT_AGE_DAYS];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')->withPivot('awarded_at');
    }

    /**
     * URL de l'icône : les badges uploadés depuis /admin/badges sont stockés sur le disque "public"
     * (chemin contenant un "/"), les badges historiques réfèrent un fichier statique dans
     * public/images/badges/ (simple nom de fichier, sans "/").
     */
    protected function iconUrl(): Attribute
    {
        return Attribute::get(fn () => str_contains($this->icon ?? '', '/')
            ? asset('storage/'.$this->icon)
            : asset('images/badges/'.$this->icon));
    }
}
