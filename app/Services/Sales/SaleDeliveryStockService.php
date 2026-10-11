<?php

namespace App\Services\Sales;

use App\Models\MasterData\StockMovement;
use App\Models\Sales\SaleDelivery;
use DomainException;

/**
 * All stock logic for sales deliveries lives here.
 *
 *  - validating a delivery  → one "out" movement per line (stock decreases)
 *  - cancelling a validated delivery → one "in" movement per original "out" (stock comes back)
 *
 * Movements are never deleted, so the stock history stays auditable.
 *
 * !!! ADAPT HERE if your stock_movements table differs !!!
 * I could not see your StockMovement model, so these are assumptions:
 *   - columns: product_id, warehouse_id, type, quantity (always positive)
 *   - type values: 'in' / 'out'
 *   - morph columns source_type / source_id (from SaleDelivery::stockMovements())
 *   - stock on hand = SUM(in) - SUM(out) per product + warehouse
 * Set 'date' / 'note' to your real column names if you have them (null = not written).
 */
class SaleDeliveryStockService
{
    private const TYPE_IN = 'in';
    private const TYPE_OUT = 'out';

    private const COL = [
        'product' => 'product_id',
        'warehouse' => 'warehouse_id',
        'type' => 'type',
        'quantity' => 'quantity',
        'date' => null,   // e.g. 'movement_date'
        'note' => null,   // e.g. 'note' or 'reason'
    ];

    /** Stock on hand for a product in a warehouse. */
    public function available(int $productId, int $warehouseId): float
    {
        $base = StockMovement::query()
            ->where(self::COL['product'], $productId)
            ->where(self::COL['warehouse'], $warehouseId);

        $in = (clone $base)->where(self::COL['type'], self::TYPE_IN)->sum(self::COL['quantity']);
        $out = (clone $base)->where(self::COL['type'], self::TYPE_OUT)->sum(self::COL['quantity']);

        return (float) $in - (float) $out;
    }

    /**
     * Check the stock, then record the "out" movements.
     * Call inside a DB transaction. Throws DomainException when stock is insufficient.
     */
    public function issue(SaleDelivery $delivery): void
    {
        $delivery->loadMissing('lines.product');

        // The same product can appear on several lines: check the cumulated quantity
        $needs = $delivery->lines
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->map(fn($group) => (float) $group->sum('quantity'));

        $errors = [];
        foreach ($needs as $productId => $qty) {
            $available = $this->available((int) $productId, (int) $delivery->warehouse_id);

            if ($available + 0.0005 < $qty) {
                $name = optional($delivery->lines->firstWhere('product_id', $productId)->product)->name
                    ?? 'Product #' . $productId;
                $errors[] = sprintf('%s (needed %s, available %s)', $name, $this->fmt($qty), $this->fmt($available));
            }
        }

        if ($errors) {
            throw new DomainException('Insufficient stock in this warehouse: ' . implode('; ', $errors) . '.');
        }

        foreach ($delivery->lines as $line) {
            if ($line->product_id && (float) $line->quantity > 0) {
                $this->record($delivery, (int) $line->product_id, (float) $line->quantity, self::TYPE_OUT);
            }
        }
    }

    /** Give the stock back: one "in" movement for every "out" movement of this delivery. */
    public function reverse(SaleDelivery $delivery): void
    {
        // Already reversed once? Do nothing (idempotent)
        if ($delivery->stockMovements()->where(self::COL['type'], self::TYPE_IN)->exists()) {
            return;
        }

        $outs = $delivery->stockMovements()->where(self::COL['type'], self::TYPE_OUT)->get();

        foreach ($outs as $movement) {
            $this->record(
                $delivery,
                (int) $movement->{self::COL['product']},
                (float) $movement->{self::COL['quantity']},
                self::TYPE_IN,
                (int) $movement->{self::COL['warehouse']}
            );
        }
    }

    private function record(SaleDelivery $delivery, int $productId, float $qty, string $type, ?int $warehouseId = null): void
    {
        $attributes = [
            self::COL['product'] => $productId,
            self::COL['warehouse'] => $warehouseId ?? $delivery->warehouse_id,
            self::COL['type'] => $type,
            self::COL['quantity'] => $qty,
        ];

        if (self::COL['date']) {
            $attributes[self::COL['date']] = now()->toDateString();
        }
        if (self::COL['note']) {
            $attributes[self::COL['note']] = ($type === self::TYPE_OUT ? 'Delivery ' : 'Cancelled delivery ') . $delivery->delivery_number;
        }

        // make() on the morph relation fills source_type / source_id
        $movement = $delivery->stockMovements()->make();
        $movement->forceFill($attributes)->save();
    }

    private function fmt(float $v): string
    {
        return rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.') ?: '0';
    }
}
