<?php

namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\Transfer;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Product;
use Illuminate\Http\Request;
use App\Services\StockService;
use DB;
use Exception;
use App\Http\Controllers\Controller;

class TransferController extends Controller
{
    public function index()
    {
        $transfers = Transfer::latest()->paginate(15);
        return view('transfers.index', compact('transfers'));
    }

    public function create()
    {
        return view('transfers.create', [
            'warehouses' => Warehouse::all(),
            'products' => Product::all()
        ]);
    }

    public function store(Request $request, StockService $stockService)
    {
        $data = $request->validate([
            'from_warehouse_id' => 'required|different:to_warehouse_id',
            'to_warehouse_id' => 'required',
            'transfer_date' => 'required|date',
            'lines.*.product_id' => 'required',
            'lines.*.quantity' => 'required|integer|min:1'
        ]);

        DB::transaction(function () use ($data, $stockService) {

            // Create transfer as draft first
            $transfer = Transfer::create([
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'transfer_date' => $data['transfer_date'],
                'status' => 'draft'  // draft first
            ]);

            // Save transfer lines
            foreach ($data['lines'] as $line) {
                $transfer->lines()->create($line);
            }

            // Complete the transfer (move stock)
            foreach ($transfer->lines as $line) {

                // OUT from source warehouse
                $stockService->move(
                    $line['product_id'],
                    $transfer->from_warehouse_id,
                    $line['quantity'],
                    'out',
                    'transfer',
                    $transfer->id,
                    'Transfer OUT'
                );

                // IN to destination warehouse
                $stockService->move(
                    $line['product_id'],
                    $transfer->to_warehouse_id,
                    $line['quantity'],
                    'in',
                    'transfer',
                    $transfer->id,
                    'Transfer IN'
                );
            }

            // Mark transfer as completed
            $transfer->update(['status' => 'completed']);
        });

        return redirect()->route('transfers.index');
    }

    public function edit(Transfer $transfer)
    {
        return view('transfers.edit', [
            'transfer' => $transfer->load('lines.product'),
            'warehouses' => Warehouse::all(),
            'products' => Product::all()
        ]);
    }

    // Cancel completed transfer (reverse stock)
    public function cancel(Transfer $transfer, StockService $stockService)
    {
        if ($transfer->status !== 'completed') {
            throw new Exception('Only completed transfers can be cancelled.');
        }

        DB::transaction(function () use ($transfer, $stockService) {
            foreach ($transfer->lines as $line) {

                // OUT from destination warehouse
                $stockService->move(
                    $line['product_id'],
                    $transfer->to_warehouse_id,
                    $line['quantity'],
                    'out',
                    'transfer_cancel',
                    $transfer->id,
                    'Cancel Transfer OUT'
                );

                // IN to source warehouse
                $stockService->move(
                    $line['product_id'],
                    $transfer->from_warehouse_id,
                    $line['quantity'],
                    'in',
                    'transfer_cancel',
                    $transfer->id,
                    'Cancel Transfer IN'
                );
            }

            $transfer->update(['status' => 'cancelled']);
        });

        return redirect()->route('transfers.index');
    }
}