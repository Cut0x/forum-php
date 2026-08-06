<?php

namespace App\Models;

use App\Services\Markdown\MarkdownRenderer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['topic_id', 'user_id', 'parent_id', 'content', 'edited_at'])]
class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Post::class, 'parent_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PostVote::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * Calcule le score au vol (requête à part). Ne pas nommer cette méthode "score" :
     * Eloquent interprète toute méthode publique sans argument accédée comme propriété
     * ($post->score) comme une tentative de relation, et plante si `score` n'a pas été
     * pré-chargé en attribut via withSum/loadSum('votes as score', 'value').
     */
    public function totalScore(): int
    {
        return (int) $this->votes()->sum('value');
    }

    protected function renderedContent(): Attribute
    {
        return Attribute::get(fn () => app(MarkdownRenderer::class)->toHtml($this->content));
    }
}
