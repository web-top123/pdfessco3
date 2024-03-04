<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    protected $fillable = [
        'path',
        'user_id',
        'name',
        'document_history',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getCreatedAtAttribute($value)
    {
        // Parse the timestamp using Carbon and format it with hours and minutes
        return \Carbon\Carbon::parse($value)->format('m.d.Y H:i');
    }
}
