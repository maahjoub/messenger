<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class message extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => __('user')
        ]);
    }

    public function recipients()
    {
        return $this->belongsToMany(User::class, 'recipients')->withPivot([
            'red_at', 'deleted_at'
        ]);
    }

}
