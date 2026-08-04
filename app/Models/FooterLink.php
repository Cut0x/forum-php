<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['footer_category_id', 'label', 'url', 'sort_order'])]
class FooterLink extends Model
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(FooterCategory::class, 'footer_category_id');
    }
}
