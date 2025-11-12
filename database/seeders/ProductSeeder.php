<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Size;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sellers = User::where('role', 'seller')->get();
        $brands = Brand::all();
        $categories = Category::whereNotNull('parent_id')->get(); // Sadece alt kategoriler
        $clothingSizes = Size::where('type', 'clothing')->get();
        $shoeSizes = Size::where('type', 'shoe')->get();

        $products = [
            ['Nike Spor Tişört', 'Nike spor tişört, %100 pamuk', 199.99, 149.99],
            ['Adidas Koşu Ayakkabısı', 'Adidas koşu ayakkabısı, hafif ve rahat', 599.99, 499.99],
            ['Puma Eşofman Takımı', 'Puma eşofman takımı, spor ve günlük kullanım', 799.99, 699.99],
            ['Zara Kot Pantolon', 'Zara slim fit kot pantolon', 299.99, null],
            ['H&M Basic Tişört', 'H&M basic tişört, çeşitli renklerde', 79.99, 59.99],
            ['LC Waikiki Sweatshirt', 'LC Waikiki kapüşonlu sweatshirt', 249.99, 199.99],
            ['Nike Air Max', 'Nike Air Max spor ayakkabı', 1299.99, 1099.99],
            ['Adidas Originals Ceket', 'Adidas originals mont ceket', 899.99, 799.99],
            ['Mango Kadın Elbise', 'Mango şık kadın elbise', 449.99, 349.99],
            ['Koton Erkek Gömlek', 'Koton klasik erkek gömlek', 199.99, 149.99],
        ];

        foreach ($products as $index => $productData) {
            [$name, $description, $price, $discountPrice] = $productData;

            $category = $categories->random();
            $brand = $brands->random();
            $seller = $sellers->random();

            $product = Product::create([
                'user_id' => $seller->id,
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'name' => $name,
                'slug' => Str::slug($name) . '-' . Str::random(5),
                'sku' => 'SKU-' . strtoupper(Str::random(8)),
                'description' => $description,
                'price' => $price,
                'discount_price' => $discountPrice,
                'stock_quantity' => rand(10, 100),
                'images' => [
                    'https://via.placeholder.com/600x600?text=' . urlencode($name),
                    'https://via.placeholder.com/600x600?text=' . urlencode($name) . '+2',
                ],
                'is_active' => true,
                'is_featured' => $index < 5, // İlk 5 ürün öne çıkan
            ]);

            // Ayakkabı kategorisiyse ayakkabı bedenleri, değilse giyim bedenleri ekle
            $isShoe = stripos($category->name, 'ayakkabı') !== false;
            $sizes = $isShoe ? $shoeSizes : $clothingSizes;

            // Rastgele 3-5 beden ekle
            $randomSizes = $sizes->random(rand(3, min(5, $sizes->count())));
            foreach ($randomSizes as $size) {
                $product->sizes()->attach($size->id, [
                    'stock_quantity' => rand(5, 20),
                    'additional_price' => 0,
                ]);
            }
        }

        // Ek rastgele ürünler oluştur
        for ($i = 0; $i < 40; $i++) {
            $category = $categories->random();
            $brand = $brands->random();
            $seller = $sellers->random();

            $productName = $brand->name . ' Ürün ' . ($i + 11);

            $product = Product::create([
                'user_id' => $seller->id,
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'name' => $productName,
                'slug' => Str::slug($productName) . '-' . Str::random(5),
                'sku' => 'SKU-' . strtoupper(Str::random(8)),
                'description' => $productName . ' açıklama metni',
                'price' => rand(50, 1000) + 0.99,
                'discount_price' => rand(0, 1) ? rand(50, 900) + 0.99 : null,
                'stock_quantity' => rand(10, 100),
                'images' => [
                    'https://via.placeholder.com/600x600?text=' . urlencode($productName),
                ],
                'is_active' => true,
                'is_featured' => rand(0, 10) > 7,
            ]);

            // Rastgele bedenler ekle
            $isShoe = stripos($category->name, 'ayakkabı') !== false;
            $sizes = $isShoe ? $shoeSizes : $clothingSizes;

            $randomSizes = $sizes->random(rand(2, min(4, $sizes->count())));
            foreach ($randomSizes as $size) {
                $product->sizes()->attach($size->id, [
                    'stock_quantity' => rand(5, 20),
                    'additional_price' => 0,
                ]);
            }
        }
    }
}
