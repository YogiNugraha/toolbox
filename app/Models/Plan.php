<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['slug','name','price','duration_days','description','limits','is_default','is_active','sort_order', 'features', 'discount_type', 'discount_value'];
    
    protected $casts = [
        'price' => 'integer',
        'duration_days' => 'integer',
        'is_default' => 'boolean', 
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'limits' => 'array', 
        'features' => 'array'
    ];

    public function subscriptions() 
    { 
        return $this->hasMany(Subscription::class); 
    }
}
