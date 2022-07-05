<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Relations\HasMany;

class Lang extends Eloquent
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'languages';
    protected $guarded = [];

    /**
     * Get all of the snippets for the Lang
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function snippets(): HasMany
    {
        $accepted_fields = [
            'snippet_id',
            'snippet_language',
            'snippet_title',
            'snippet_seo',
            'snippet_thumbnail',
            'snippet_author',
            'author_pic',
            'author_bio',
        ];
        return $this->hasMany(Code::class, 'snippet_language', 'language_name')
            ->select($accepted_fields)
            ->orderBy('created_at', 'desc');
    }
}
