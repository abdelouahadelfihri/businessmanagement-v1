<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';
    protected $primaryKey = 'unit_id';

    protected $fillable = [
        'name',
        'abbreviation',
    ];

    // If you ever want relations later, add them here.
}