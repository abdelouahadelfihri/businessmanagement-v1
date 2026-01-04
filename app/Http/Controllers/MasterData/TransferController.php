<?php
namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferController extends Controller
{
    public function index()
    {
        return view('transfers.index', [
            'transfers' => Transfer::latest()->get()
        ]);
    }

    public function create()
    {
        return view('transfers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_warehouse_id' => 'required|different:to_warehouse_id',
            'to_warehouse_id' => 'required',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required',
            'lines.*.quantity' => 'required|integer|min:1'
        ]);

        DB::transaction(function () use ($request) {
            $transfer = Transfer::create([
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'status' => 'draft'
            ]);

            foreach ($request->lines as $line) {
                $transfer->lines()->create($line);
            }
        });

        return redirect()->route('transfers.index');
    }

    public function edit(Transfer $transfer)
    {
        abort_if($transfer->status !== 'draft', 403);
        return view('transfers.edit', compact('transfer'));
    }

    public function update(Request $request, Transfer $transfer)
    {
        abort_if($transfer->status !== 'draft', 403);

        DB::transaction(function () use ($request, $transfer) {
            $transfer->lines()->delete();
            foreach ($request->lines as $line) {
                $transfer->lines()->create($line);
            }
        });

        return redirect()->route('transfers.index');
    }

    public function complete(Transfer $transfer, StockService $stock)
    {
        abort_if($transfer->status !== 'draft', 400);

        foreach ($transfer->lines as $line) {
            $available = WarehouseStock::where('warehouse_id', $transfer->from_warehouse_id)
                ->where('product_id', $line->product_id)
                ->value('quantity') ?? 0;

            if ($available < $line->quantity) {
                throw ValidationException::withMessages([
                    'stock' => 'Insufficient stock for transfer'
                ]);
            }
        }

        DB::transaction(function () use ($transfer, $stock) {
            foreach ($transfer->lines as $line) {
                $stock->move(
                    $line->product_id,
                    $transfer->from_warehouse_id,
                    $line->quantity,
                    'out',
                    'transfer',
                    $transfer->id
                );

                $stock->move(
                    $line->product_id,
                    $transfer->to_warehouse_id,
                    $line->quantity,
                    'in',
                    'transfer',
                    $transfer->id
                );
            }

            $transfer->update(['status' => 'completed']);
        });

        return redirect()->route('transfers.index');
    }

    public function cancel(Transfer $transfer, StockService $stock)
    {
        abort_if($transfer->status !== 'completed', 400);

        DB::transaction(function () use ($transfer, $stock) {
            foreach ($transfer->lines as $line) {
                $stock->move(
                    $line->product_id,
                    $transfer->to_warehouse_id,
                    $line->quantity,
                    'out',
                    'transfer_cancel',
                    $transfer->id
                );

                $stock->move(
                    $line->product_id,
                    $transfer->from_warehouse_id,
                    $line->quantity,
                    'in',
                    'transfer_cancel',
                    $transfer->id
                );
            }

            $transfer->update(['status' => 'cancelled']);
        });

        return redirect()->route('transfers.index');
    }
}