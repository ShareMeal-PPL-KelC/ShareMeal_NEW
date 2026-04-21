<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'status',
        'published_on',
        'author',
        'content',
        'image',
        'read_time',
    ];

    protected $casts = [
        'published_on' => 'date',
    ];
}
