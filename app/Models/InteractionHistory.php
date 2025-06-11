<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InteractionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_input',
        'assistant_response',
        'interaction_type',
        'language',
        'topic'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}