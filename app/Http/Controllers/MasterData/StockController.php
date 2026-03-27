<?php

namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\Product;
use App\Http\Controllers\Controller;
class StockMovementController extends Controller
{
    public function index()
    {
        $products = Product::with('warehouseStocks.warehouse')->get();

        return view('stock.index', compact('products'));
    }
}