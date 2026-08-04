<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'file', 'title', 'is_enabled'])]
class Emote extends Model
{
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function url(): string
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->url('emotes/'.$this->file);
    }
}
