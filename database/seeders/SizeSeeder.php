<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Giyim bedenleri
        $clothingSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
        foreach ($clothingSizes as $index => $size) {
            Size::create([
                'name' => $size,
                'code' => strtolower($size),
                'type' => 'clothing',
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        // Ayakkabı bedenleri (EU)
        for ($i = 36; $i <= 46; $i++) {
            Size::create([
                'name' => (string)$i,
                'code' => (string)$i,
                'type' => 'shoe',
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        // Standart bedenler (Tek beden)
        Size::create([
            'name' => 'Tek Beden',
            'code' => 'onesize',
            'type' => 'standard',
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }
}
