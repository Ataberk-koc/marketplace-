<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ana kategoriler
        $electronics = Category::create([
            'name' => 'Elektronik',
            'slug' => Str::slug('Elektronik'),
            'description' => 'Elektronik ürünler',
            'is_active' => true,
        ]);

        $clothing = Category::create([
            'name' => 'Giyim',
            'slug' => Str::slug('Giyim'),
            'description' => 'Giyim ürünleri',
            'is_active' => true,
        ]);

        $shoes = Category::create([
            'name' => 'Ayakkabı',
            'slug' => Str::slug('Ayakkabı'),
            'description' => 'Ayakkabı ürünleri',
            'is_active' => true,
        ]);

        $accessories = Category::create([
            'name' => 'Aksesuar',
            'slug' => Str::slug('Aksesuar'),
            'description' => 'Aksesuar ürünleri',
            'is_active' => true,
        ]);

        // Alt kategoriler - Giyim
        $menClothing = Category::create([
            'name' => 'Erkek Giyim',
            'slug' => Str::slug('Erkek Giyim'),
            'parent_id' => $clothing->id,
            'is_active' => true,
        ]);

        $womenClothing = Category::create([
            'name' => 'Kadın Giyim',
            'slug' => Str::slug('Kadın Giyim'),
            'parent_id' => $clothing->id,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Çocuk Giyim',
            'slug' => Str::slug('Çocuk Giyim'),
            'parent_id' => $clothing->id,
            'is_active' => true,
        ]);

        // Alt kategoriler - Erkek Giyim
        Category::create([
            'name' => 'Erkek Tişört',
            'slug' => Str::slug('Erkek Tişört'),
            'parent_id' => $menClothing->id,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Erkek Pantolon',
            'slug' => Str::slug('Erkek Pantolon'),
            'parent_id' => $menClothing->id,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Erkek Ceket',
            'slug' => Str::slug('Erkek Ceket'),
            'parent_id' => $menClothing->id,
            'is_active' => true,
        ]);

        // Alt kategoriler - Kadın Giyim
        Category::create([
            'name' => 'Kadın Elbise',
            'slug' => Str::slug('Kadın Elbise'),
            'parent_id' => $womenClothing->id,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Kadın Bluz',
            'slug' => Str::slug('Kadın Bluz'),
            'parent_id' => $womenClothing->id,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Kadın Pantolon',
            'slug' => Str::slug('Kadın Pantolon'),
            'parent_id' => $womenClothing->id,
            'is_active' => true,
        ]);

        // Alt kategoriler - Ayakkabı
        Category::create([
            'name' => 'Erkek Ayakkabı',
            'slug' => Str::slug('Erkek Ayakkabı'),
            'parent_id' => $shoes->id,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Kadın Ayakkabı',
            'slug' => Str::slug('Kadın Ayakkabı'),
            'parent_id' => $shoes->id,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Spor Ayakkabı',
            'slug' => Str::slug('Spor Ayakkabı'),
            'parent_id' => $shoes->id,
            'is_active' => true,
        ]);
    }
}
