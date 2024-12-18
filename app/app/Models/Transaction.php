<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'leads_count_last',
        'leads_count_new',
        'lead_id',
        'contact_id',
        'agreement',
        'part_sum',
        'all_sum',
        'status',
        'body',
    ];
}
