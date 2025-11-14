<?php
// Kategorileri kontrol et
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== LOCAL KATEGORİLER ===\n\n";

$categories = \App\Models\Category::orderBy('id')->get(['id', 'name', 'parent_id']);

foreach ($categories as $cat) {
    $mappingStatus = \App\Models\CategoryMapping::where('category_id', $cat->id)->exists() ? '✅ Eşleştirilmiş' : '❌ Eşleştirilmemiş';
    echo "ID: {$cat->id} | {$cat->name} | {$mappingStatus}\n";
}

echo "\n=== ÖNERİLEN TRENDYOL KATEGORİLERİ (Örnekler) ===\n\n";
echo "522 - Gömlek (Erkek)\n";
echo "523 - T-shirt (Erkek)\n";
echo "524 - Pantolon (Erkek)\n";
echo "525 - Ayakkabı (Erkek)\n";
echo "1017 - Elbise (Kadın)\n";
echo "1018 - Bluz (Kadın)\n";
echo "1095 - Elektronik Aksesuar\n";
echo "411 - Cep Telefonu\n\n";

echo "💡 TİP: Trendyol kategorileri için admin panelde 'Sync Categories' butonuna basın.\n";
echo "📍 Daha sonra: Admin > Trendyol > Category Mapping sayfasından eşleştirme yapın.\n";
