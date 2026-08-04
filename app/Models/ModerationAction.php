<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['moderator_id', 'action', 'target_type', 'target_id', 'meta'])]
class ModerationAction extends Model
{
    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public static function log(User $moderator, string $action, ?Model $target = null, array $meta = []): self
    {
        return static::create([
            'moderator_id' => $moderator->id,
            'action' => $action,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'meta' => $meta,
        ]);
    }

    public function targetLabel(): string
    {
        if (! $this->target_type || ! $this->target_id) {
            return '';
        }

        $target = $this->target_type::find($this->target_id);
        if (! $target) {
            return class_basename($this->target_type).' supprimé';
        }

        return match ($this->target_type) {
            User::class => $target->displayName(),
            Topic::class => $target->title,
            default => class_basename($this->target_type).' #'.$this->target_id,
        };
    }
}
