<?php

namespace App\Models\Article;

use App\Enums\Fetch\Provider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $casts = ['provider' => Provider::class, 'published_at' => 'datetime',];

    protected $guarded = ['id'];
}
