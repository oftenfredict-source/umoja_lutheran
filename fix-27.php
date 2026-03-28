<?php
$t = \App\Models\StockTransfer::find(27);
if ($t) {
    echo "Found Transfer 27. Previous profit: " . $t->expected_profit_pic_sale . "\n";
    if (empty($t->unit_cost) && $t->productVariant) {
        $latestReceipt = \App\Models\StockReceipt::where('product_variant_id', $t->product_variant_id)->orderBy('receipt_date', 'desc')->first();
        if ($latestReceipt) {
            $raw_price = $latestReceipt->buying_price_per_bottle;
            $sp = $t->productVariant->selling_price_per_pic ?? 0;
            $isPackagePrice = false;
            if (($t->productVariant->items_per_package ?? 0) > 0 && $sp > 0 && $raw_price > $sp) {
                $isPackagePrice = true;
            }
            if ($t->quantity_unit === 'packages') {
                $t->unit_cost = $isPackagePrice ? $raw_price : $raw_price * ($t->productVariant->items_per_package ?? 1);
            } else {
                $t->unit_cost = $isPackagePrice ? $raw_price / ($t->productVariant->items_per_package ?? 1) : $raw_price;
            }
        }
    }
    // Also clear out the broken explicitly-saved expected_profit so calculateRevenueProjections overwrites it cleanly
    $t->expected_profit_pic_sale = null;
    $t->calculateRevenueProjections();
    $t->save();
    echo "Fixed Transfer 27. New profit: " . $t->expected_profit_pic_sale . "\n";
} else {
    echo "Transfer 27 not found.\n";
}
