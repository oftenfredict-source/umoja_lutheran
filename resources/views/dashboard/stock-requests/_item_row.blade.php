<tr class="item-row">
    <td>
        <select name="items[{{ $index }}][product_variant_id]" class="form-control select2-item" required>
            <option value="">-- Select --</option>
            @foreach($products as $variant)
                @php
                    $category = $variant->product->category ?? '';
                    $foodCategories = ['food', 'meat_poultry', 'seafood', 'vegetables', 'dairy', 'pantry_baking', 'spices_herbs', 'oils_fats', 'kitchen', 'snacks'];
                    $isFood = in_array($category, $foodCategories);
                    $vName = $variant->variant_name;
                    if ($category === 'cleaning_supplies') {
                        $vName = trim(str_ireplace(['(ml)', 'ml', '(l)', 'l'], '', $vName));
                    } else {
                        $vName = trim(str_ireplace(['(Kg)', 'kg'], '', $vName));
                    }
                    $label = $variant->product->name ?? $variant->product->product_name ?? 'Item';
                    if (strtolower($vName) !== 'unit' && $vName !== '') {
                        $label .= ' – ' . $vName;
                    }
                    if ($category === 'cleaning_supplies') {
                        $vName = trim(str_ireplace(['(ml)', 'ml', '(l)', 'l'], '', $vName));
                        $measure = '';
                    } else {
                        $measure = trim($variant->measurement);
                    }
                    $details = [];
                    if (!$isFood) {
                        $pName = trim($variant->packaging_name);
                        if ($pName !== '' && !in_array(strtolower($pName), ['standard', 'unit']))
                            $details[] = $pName;
                        if ($measure !== '' && !in_array(strtolower($measure), ['0 ml', '0ml', '0', 'ml', 'litres', 'ltr', 'grams', 'g', 'kg']))
                            $details[] = $measure;
                        if ($details)
                            $label .= ' (' . implode(' – ', $details) . ')';
                    }
                    $beverageCategories = ['spirits', 'wines', 'non_alcoholic_beverage', 'alcoholic_beverage', 'energy_drinks', 'juices', 'water', 'hot_beverages', 'cocktails'];
                    $isBeverage = in_array($category, $beverageCategories);
                    $unitPrice = $variant->getLatestUnitCost();
                    $sellingPrice = $variant->selling_price_per_pic ?? 0;
                @endphp
                <option value="{{ $variant->id }}" data-unit-cost="{{ $unitPrice }}"
                    data-selling-price="{{ $sellingPrice }}" data-is-beverage="{{ $isBeverage ? 1 : 0 }}"
                    data-ratio="{{ $variant->items_per_package ?? 1 }}">
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" name="items[{{ $index }}][quantity]" class="form-control qty-input" step="0.01" min="0.01"
            placeholder="0.00" required>
    </td>
    <td>
        <select name="items[{{ $index }}][unit]" class="form-control unit-select" required>
            @if($isChef)
                <option value="kg">Kilograms (Kg)</option>
                <option value="grams">Grams (g)</option>
                <option value="ltr">Litres (L)</option>
                <option value="pcs">Pieces (Pcs)</option>
                <option value="packets">Packets (Pkt)</option>
                <option value="bags">Bags (Bag)</option>
                <option value="boxes">Boxes (Box)</option>
                <option value="packages">Bulk/Crates (Ratio)</option>
                <option value="other">Other</option>
            @elseif(isset($isHousekeeper) && $isHousekeeper)
                <option value="packages">Boxes / Bundles</option>
                <option value="bottles">Individual Units (Pcs)</option>
                <option value="other">Other</option>
            @else
                <option value="packages">Crates / Packages</option>
                <option value="bottles">Individual Bottles/Units</option>
                <option value="other">Other</option>
            @endif
        </select>
    </td>
    <td class="text-right vertical-align-middle">
        <span class="unit-cost-display">0.00</span>
    </td>
    @if(!$isChef && !(isset($isHousekeeper) && $isHousekeeper))
        <td class="text-right vertical-align-middle text-info">
            <span class="revenue-display">0.00</span>
        </td>
        <td class="text-right vertical-align-middle text-success">
            <span class="profit-display">0.00</span>
        </td>
    @endif
    <td class="text-right vertical-align-middle font-weight-bold">
        <span class="subtotal-display">0.00</span>
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove">
            <i class="fa fa-times"></i>
        </button>
    </td>
</tr>