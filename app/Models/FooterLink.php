<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FooterLink extends Model
{
    protected $table = 'footer_links';
    protected $fillable = [
        'footer_link_group_id',
        'label',
        'url',
        'open_new_tab',
        'sort_order',
    ];

    protected $casts = [
        'open_new_tab' => 'boolean',
        'sort_order'   => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(FooterLinkGroup::class, 'footer_link_group_id');
    }
}