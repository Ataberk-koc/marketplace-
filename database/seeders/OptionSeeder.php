<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Beden Opsiyonu
        $beden = \App\Models\Option::firstOrCreate(
            ['name' => 'Beden'],
            [
                'type' => 'select',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $bedenler = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL'];
        foreach ($bedenler as $index => $value) {
            \App\Models\OptionValue::firstOrCreate(
                ['option_id' => $beden->id, 'value' => $value],
                [
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }

        // Renk Opsiyonu
        $renk = \App\Models\Option::firstOrCreate(
            ['name' => 'Renk'],
            [
                'type' => 'color',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        $renkler = [
            ['value' => 'Siyah', 'code' => '#000000'],
            ['value' => 'Beyaz', 'code' => '#FFFFFF'],
            ['value' => 'Kırmızı', 'code' => '#FF0000'],
            ['value' => 'Mavi', 'code' => '#0000FF'],
            ['value' => 'Yeşil', 'code' => '#00FF00'],
            ['value' => 'Sarı', 'code' => '#FFFF00'],
            ['value' => 'Turuncu', 'code' => '#FFA500'],
            ['value' => 'Mor', 'code' => '#800080'],
            ['value' => 'Pembe', 'code' => '#FFC0CB'],
            ['value' => 'Kahverengi', 'code' => '#8B4513'],
            ['value' => 'Gri', 'code' => '#808080'],
            ['value' => 'Lacivert', 'code' => '#000080'],
        ];

        foreach ($renkler as $index => $renk_data) {
            \App\Models\OptionValue::firstOrCreate(
                ['option_id' => $renk->id, 'value' => $renk_data['value']],
                [
                    'color_code' => $renk_data['code'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }
    }
}
