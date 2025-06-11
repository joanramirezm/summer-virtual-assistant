<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'language',
        'preferred_interface',
        'learning_topics',
        'tech_skill_level'
    ];

    protected $casts = [
        'learning_topics' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}