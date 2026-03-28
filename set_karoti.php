<?php
$v = \App\Models\ProductVariant::where('variant_name', 'LIKE', '%karoti%')->first();
if ($v) {
    $v->minimum_stock_level = 5;
    $v->save();
    echo "Karoti threshold set to 5 kg.\n";
}
