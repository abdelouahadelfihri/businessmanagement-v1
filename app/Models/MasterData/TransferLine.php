<?php
namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Product;

class TransferLine extends Model
{
    protected $fillable = ['transfer_id', 'product_id', 'quantity'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}