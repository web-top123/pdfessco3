<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalTimeout extends Model
{
    use HasFactory;
    protected $table = 'global_timeouts';
}
