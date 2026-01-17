<?php
namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\WarehouseStock;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class WarehouseStockController extends Controller
{
    public function index()
    {
        $stocks = WarehouseStock::with('product', 'warehouse')
            ->orderBy('warehouse_id')
            ->orderBy('product_id')
            ->paginate(50);

        return view('warehouse_stocks.index', compact('stocks'));
    }
}