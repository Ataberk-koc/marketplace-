<?php

// Basit test scripti - Tarayıcıda çalıştırılacak
echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>API Test</title></head><body>";
echo "<h1>Trendyol API Test</h1>";

// Test 1: Brands listesi
echo "<h2>Test 1: Marka Listesi</h2>";
echo "<pre>";
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$brands = \App\Models\Brand::withCount('products')->get();
foreach ($brands as $brand) {
    echo "ID: {$brand->id} | Name: {$brand->name} | Products: {$brand->products_count}\n";
}
echo "</pre>";

// Test 2: API endpoint test (marka 1'in kategorileri)
echo "<h2>Test 2: API Endpoint</h2>";
echo "<button onclick=\"testAPI()\">Test API</button>";
echo "<pre id=\"result\"></pre>";

echo "<script>
function testAPI() {
    fetch('/admin/trendyol/api/categories-by-brand/1')
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            document.getElementById('result').textContent = JSON.stringify(data, null, 2);
        })
        .catch(error => {
            document.getElementById('result').textContent = 'ERROR: ' + error.message;
        });
}
</script>";

echo "</body></html>";
