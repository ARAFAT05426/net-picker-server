<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    // Make 'ip_address', 'country', and 'visit_date' fillable
    protected $fillable = ['ip_address', 'country', 'visit_date'];

    protected $casts = [
        'visit_date' => 'date',
    ];
}
