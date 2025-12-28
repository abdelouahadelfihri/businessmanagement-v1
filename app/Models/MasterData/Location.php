<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    // Specify the table name if it's not the plural of the model name
    protected $table = 'locations';

    // Specify the custom primary key
    protected $primaryKey = 'location_id';

    // Allow mass assignment for these fields
    protected $fillable = [
        'name',
        'address',
    ];

}