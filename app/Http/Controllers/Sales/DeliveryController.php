<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SaleDelivery;
use App\Services\StockService;
use Illuminate\Http\Request;
use App\Models\MasterData\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryController extends Controller
{
    public function index()
    {
        return SaleDelivery::with('salesOrder')->get();
    }

    public function post(SaleDelivery $delivery, StockService $stock)
    {
        abort_if($delivery->status !== 'draft', 400);

        foreach ($delivery->lines as $line) {
            $available = WarehouseStock::where('warehouse_id', $delivery->warehouse_id)
                ->where('product_id', $line->product_id)
                ->value('quantity') ?? 0;

            if ($available < $line->quantity) {
                throw ValidationException::withMessages([
                    'stock' => 'Insufficient stock'
                ]);
            }
        }

        DB::transaction(function () use ($delivery, $stock) {
            foreach ($delivery->lines as $line) {
                $stock->move(
                    $line->product_id,
                    $delivery->warehouse_id,
                    $line->quantity,
                    'out',
                    'sale_delivery',
                    $delivery->id
                );
            }

            $delivery->update(['status' => 'posted']);
        });
    }

    public function cancel(SaleDelivery $delivery, StockService $stock)
    {
        abort_if($delivery->status !== 'posted', 400);

        DB::transaction(function () use ($delivery, $stock) {
            foreach ($delivery->lines as $line) {
                $stock->move(
                    $line->product_id,
                    $delivery->warehouse_id,
                    $line->quantity,
                    'in',
                    'sale_delivery_cancel',
                    $delivery->id
                );
            }

            $delivery->update(['status' => 'cancelled']);
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'delivery_number' => 'required|string|unique:deliveries,delivery_number',
            'date' => 'required|date',
            'status' => 'nullable|string',
            'total' => 'required|numeric'
        ]);

        $delivery = SaleDelivery::create($validated);

        return response()->json($delivery, 201);
    }

    public function show($id)
    {
        return SaleDelivery::with('salesOrder')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $delivery = SaleDelivery::findOrFail($id);

        $validated = $request->validate([
            'sales_order_id' => 'sometimes|exists:sales_orders,id',
            'delivery_number' => 'sometimes|string|unique:deliveries,delivery_number,' . $id,
            'date' => 'sometimes|date',
            'status' => 'sometimes|string',
            'total' => 'sometimes|numeric'
        ]);

        $delivery->update($validated);

        return response()->json($delivery);
    }

    public function destroy($id)
    {
        $delivery = SaleDelivery::findOrFail($id);
        $delivery->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}