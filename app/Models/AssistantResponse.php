<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistantResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prompt',
        'response',
        'model_used',
        'tokens_used'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}