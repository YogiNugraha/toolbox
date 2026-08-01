<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'user_id', 'tool_slug', 'original_filename',
        'original_size', 'result_size', 'status', 'result_path', 'meta',
    ];
    protected $casts = ['meta' => 'array'];

    public function user() { return $this->belongsTo(User::class); }
}
