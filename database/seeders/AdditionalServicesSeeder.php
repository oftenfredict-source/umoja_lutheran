<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceCatalog;

class AdditionalServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'service_key' => 'parking',
                'service_name' => 'Parking Service',
                'description' => 'Secure vehicle parking facility',
                'pricing_type' => 'fixed',
                'price_tanzanian' => 5000,
                'price_international' => 2,
                'payment_required_upfront' => true,
                'requires_items' => false,
                'is_active' => true,
                'display_order' => 5,
            ],
            [
                'service_key' => 'garden',
                'service_name' => 'Garden Service',
                'description' => 'Access to garden for events or leisure',
                'pricing_type' => 'fixed',
                'price_tanzanian' => 50000,
                'price_international' => 20,
                'payment_required_upfront' => true,
                'requires_items' => false,
                'is_active' => true,
                'display_order' => 6,
            ],
            [
                'service_key' => 'conference_room',
                'service_name' => 'Conference Room',
                'description' => 'Professional conference and meeting space',
                'pricing_type' => 'per_hour',
                'price_tanzanian' => 25000,
                'price_international' => 10,
                'payment_required_upfront' => true,
                'requires_items' => false,
                'is_active' => true,
                'display_order' => 7,
            ],
        ];

        foreach ($services as $service) {
            ServiceCatalog::firstOrCreate(
                ['service_key' => $service['service_key']],
                $service
            );
        }
    }
}
