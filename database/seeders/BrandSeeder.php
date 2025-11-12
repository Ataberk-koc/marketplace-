<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            'Nike',
            'Adidas',
            'Puma',
            'Reebok',
            'Under Armour',
            'New Balance',
            'Converse',
            'Vans',
            'Zara',
            'H&M',
            'Mango',
            'LC Waikiki',
            'Koton',
            'DeFacto',
            'Polo Ralph Lauren',
        ];

        foreach ($brands as $brandName) {
            Brand::create([
                'name' => $brandName,
                'slug' => Str::slug($brandName),
                'description' => $brandName . ' markası ürünleri',
                'is_active' => true,
            ]);
        }
    }
}
