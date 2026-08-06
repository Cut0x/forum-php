<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['category_id', 'user_id', 'title', 'slug', 'pinned_at', 'locked_at', 'edited_at'])]
class Topic extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'pinned_at' => 'datetime',
            'locked_at' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(TopicVote::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * Calcule le score au vol (requête à part). Ne pas nommer cette méthode "score" :
     * Eloquent interprète toute méthode publique sans argument accédée comme propriété
     * ($topic->score) comme une tentative de relation, et plante si `score` n'a pas été
     * pré-chargé en attribut via withSum/loadSum('votes as score', 'value').
     */
    public function totalScore(): int
    {
        return (int) $this->votes()->sum('value');
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    public function isPinned(): bool
    {
        return $this->pinned_at !== null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'sujet';
        $slug = $base;
        $i = 1;
        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
