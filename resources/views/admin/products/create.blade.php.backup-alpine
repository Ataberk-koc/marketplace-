@extends('layouts.admin')
@section('title', 'Yeni Ürün Ekle')
@push('styles')
<style>
.option-selector{border:1px solid #dee2e6;border-radius:8px;padding:15px;margin-bottom:15px}
.option-value-checkbox{display:none}
.option-value-label{display:inline-block;padding:8px 16px;margin:5px;border:2px solid #dee2e6;border-radius:6px;cursor:pointer;background:#fff;transition:all 0.3s}
.option-value-checkbox:checked+.option-value-label{border-color:#0d6efd;background:#0d6efd;color:#fff;font-weight:bold}
.color-box{width:40px;height:40px;border-radius:6px;display:inline-block;border:2px solid #dee2e6;cursor:pointer;margin:5px}
.option-value-checkbox:checked+.color-box{border-color:#0d6efd;border-width:3px;box-shadow:0 0 10px rgba(13,110,253,.5)}
table.variants-table{width:100%;font-size:13px}
table.variants-table th{background:#f8f9fa;padding:10px;font-size:12px;border-bottom:2px solid #dee2e6;white-space:nowrap}
table.variants-table td{padding:8px;border-bottom:1px solid #eee;vertical-align:middle}
table.variants-table input{padding:6px 8px;font-size:13px}
table.variants-table tr:hover{background:#f8f9fa}
.option-inline{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.option-inline .form-check-label{margin-bottom:0}
</style>
@endpush
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
<h2><i class="bi bi-plus-circle"></i> Yeni Ürün Ekle</h2>
<a href="{{ route('admin.products.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Geri</a>
</div>
<form action="{{ route('admin.products.store') }}" method="POST" id="productForm" enctype="multipart/form-data">
@csrf
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show"><strong>Hata!</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
<ul class="nav nav-tabs mb-3">
<li class="nav-item"><button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button"><i class="bi bi-info-circle"></i> Genel</button></li>
<li class="nav-item"><button class="nav-link" id="options-tab" data-bs-toggle="tab" data-bs-target="#options" type="button"><i class="bi bi-sliders"></i> Seçenekler</button></li>
<li class="nav-item"><button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#variants" type="button"><i class="bi bi-grid-3x3"></i> Varyantlar</button></li>
<li class="nav-item"><button class="nav-link" id="attributes-tab" data-bs-toggle="tab" data-bs-target="#attributes" type="button"><i class="bi bi-list-stars"></i> Özellikler</button></li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="general">
<div class="card"><div class="card-body"><div class="row">
<div class="col-md-6"><div class="mb-3"><label class="form-label">Ürün Adı <span class="text-danger">*</span></label><input type="text" class="form-control" id="name" name="name"></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label">Model Kodu <span class="text-danger">*</span></label><input type="text" class="form-control" id="model_code" name="model_code"></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label">Kategori <span class="text-danger">*</span></label><select class="form-select" id="category_id" name="category_id"><option value="">Seçin</option>@foreach($categories as $cat)<option value="{{$cat->id}}">{{$cat->name}}</option>@endforeach</select></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label">Marka</label><select class="form-select" name="brand_id"><option value="">Seçin</option>@foreach($brands as $brand)<option value="{{$brand->id}}">{{$brand->name}}</option>@endforeach</select></div></div>
<div class="col-12"><div class="mb-3"><label class="form-label">Açıklama</label><textarea class="form-control" name="description" rows="3"></textarea></div></div>
</div></div></div>
</div>
<div class="tab-pane fade" id="options">
<div class="card"><div class="card-header bg-light"><h5 class="mb-0">Beden seçin (otomatik varyantlar oluşturulacak)</h5></div>
<div class="card-body">
@foreach($options as $option)
<div class="option-selector">
<div class="option-inline">
<input type="checkbox" class="form-check-input option-checkbox" id="opt_{{$option->id}}" value="{{$option->id}}" data-option-name="{{$option->name}}" data-option-type="{{$option->type}}">
<label for="opt_{{$option->id}}" class="form-check-label"><strong>{{$option->name}}</strong></label>
<div class="option-values" id="values_{{$option->id}}" style="display:none">
@if($option->type==='color')
@foreach($option->activeValues as $val)
<input type="checkbox" class="option-value-checkbox" id="val_{{$val->id}}" value="{{$val->id}}" data-option-id="{{$option->id}}" data-value-name="{{$val->value}}" data-color-code="{{$val->color_code}}">
<label for="val_{{$val->id}}" class="color-box" style="background-color:{{$val->color_code}}" title="{{$val->value}}"></label>
@endforeach
@else
@foreach($option->activeValues as $val)
<input type="checkbox" class="option-value-checkbox" id="val_{{$val->id}}" value="{{$val->id}}" data-option-id="{{$option->id}}" data-value-name="{{$val->value}}">
<label for="val_{{$val->id}}" class="option-value-label">{{$val->value}}</label>
@endforeach
@endif
</div>
</div>
</div>
@endforeach
@if($options->isEmpty())<div class="alert alert-warning">Opsiyon yok. <a href="{{route('admin.options.create')}}" target="_blank">Ekleyin</a></div>@endif
</div></div>
</div>
<div class="tab-pane fade" id="variants">
<div class="card mb-3"><div class="card-header bg-light">
<h5 class="mb-0">Manuel Varyant Ekleme</h5>
</div>
<div class="card-body">
<div id="addVariantFormContainer">
<div class="alert alert-info"><i class="bi bi-info-circle"></i> Seçenekler sekmesinden beden/renk seçin</div>
</div>
</div>
</div>

<div class="card"><div class="card-header bg-light">
<h5 class="mb-0">Eklenen Varyantlar</h5>
</div>
<div class="card-body">
<div id="variantsContainer">
<div class="alert alert-secondary"><i class="bi bi-info-circle"></i> Henüz varyant eklenmedi</div>
</div>
</div>
</div>
</div>
<div class="tab-pane fade" id="attributes">
<div class="card"><div class="card-header bg-light"><h5 class="mb-0">Ürün Özellikleri</h5></div>
<div class="card-body"><div class="row">
<div class="col-md-6">
<div class="mb-3"><label class="form-label">Materyal</label><select class="form-select" name="attributes[materyal]"><option value="">Seçin</option><option value="100% Pamuk">100% Pamuk</option><option value="Polyester">Polyester</option><option value="Pamuk/Polyester">Pamuk/Polyester</option></select></div>
<div class="mb-3"><label class="form-label">Kalıp</label><select class="form-select" name="attributes[kalip]"><option value="">Seçin</option><option value="Slim Fit">Slim Fit</option><option value="Regular">Regular</option><option value="Oversize">Oversize</option></select></div>
</div>
<div class="col-md-6">
<div class="mb-3"><label class="form-label">Cinsiyet</label><select class="form-select" name="attributes[cinsiyet]"><option value="">Seçin</option><option value="Erkek">Erkek</option><option value="Kadın">Kadın</option><option value="Unisex">Unisex</option></select></div>
<div class="mb-3"><label class="form-label">Desen</label><select class="form-select" name="attributes[desen]"><option value="">Seçin</option><option value="Düz">Düz</option><option value="Çizgili">Çizgili</option><option value="Desenli">Desenli</option></select></div>
</div>
</div></div></div>
</div>
</div>
<div class="card mt-3"><div class="card-body"><button type="submit" class="btn btn-success btn-lg w-100"><i class="bi bi-check-circle"></i> Kaydet</button></div></div>
</form>
@push('scripts')
<script>
$(document).ready(function(){
updateAddVariantForm(); // İlk yükleme

// Seçenek checkbox değiştiğinde
$('.option-checkbox').on('change',function(){
const id=$(this).val();
const div=$('#values_'+id);
if($(this).is(':checked')){
div.show();
}else{
div.hide();
div.find('.option-value-checkbox').prop('checked',false);
}
updateAddVariantForm();
});

// Opsiyon değeri değiştiğinde
$(document).on('change','.option-value-checkbox',function(){
updateAddVariantForm();
});

let variantCounter = 0;

// Varyant ekleme formunu güncelle
function updateAddVariantForm(){
const selectedOptions = [];
$('.option-checkbox:checked').each(function(){
const id=$(this).val();
const name=$(this).data('option-name');
const type=$(this).data('option-type');
const values=[];
$('#values_'+id+' .option-value-checkbox:checked').each(function(){
values.push({
id:$(this).val(),
name:$(this).data('value-name'),
color:$(this).data('color-code')||null
});
});
if(values.length>0){
selectedOptions.push({id:id,name:name,type:type,values:values});
}
});

if(selectedOptions.length===0){
$('#addVariantFormContainer').html('<div class="alert alert-info"><i class="bi bi-info-circle"></i> Seçenekler sekmesinden beden/renk seçin</div>');
return;
}

let html='<div class="card"><div class="card-body"><h6 class="card-title mb-3">Yeni Varyant Ekle</h6><div class="row g-3">';
selectedOptions.forEach(opt=>{
html+='<div class="col-md-6"><label class="form-label">'+opt.name+'</label><select class="form-select variant-option" data-option-id="'+opt.id+'" data-option-name="'+opt.name+'">';
html+='<option value="">Seçiniz</option>';
opt.values.forEach(v=>{
if(v.color){
html+='<option value="'+v.id+'" data-value-name="'+v.name+'" data-color="'+v.color+'">'+v.name+'</option>';
}else{
html+='<option value="'+v.id+'" data-value-name="'+v.name+'">'+v.name+'</option>';
}
});
html+='</select></div>';
});
html+='<div class="col-md-3"><label class="form-label">Fiyat <span class="text-danger">*</span></label><input type="number" class="form-control" id="new_price" step="0.01" placeholder="0.00"></div>';
html+='<div class="col-md-3"><label class="form-label">İndirimli Fiyat</label><input type="number" class="form-control" id="new_sale_price" step="0.01" placeholder="0.00"></div>';
html+='<div class="col-md-3"><label class="form-label">Maliyet</label><input type="number" class="form-control" id="new_cost" step="0.01" placeholder="0.00"></div>';
html+='<div class="col-md-3"><label class="form-label">Stok <span class="text-danger">*</span></label><input type="number" class="form-control" id="new_stock" value="0" placeholder="0"></div>';
html+='<div class="col-md-3"><label class="form-label">SKU <span class="text-danger">*</span></label><input type="text" class="form-control" id="new_sku" placeholder="SKU"></div>';
html+='<div class="col-md-3"><label class="form-label">Barkod <span class="text-danger">*</span></label><input type="text" class="form-control" id="new_barcode" placeholder="Barkod"></div>';
html+='<div class="col-md-3"><label class="form-label">TNY Kodu</label><input type="text" class="form-control" id="new_tny_code" placeholder="TNY"></div>';
html+='<div class="col-md-3"><label class="form-label">Entegrasyon Kodu</label><input type="text" class="form-control" id="new_integration_code" placeholder="Kod"></div>';
html+='<div class="col-12"><button type="button" class="btn btn-primary" id="addVariantBtn"><i class="bi bi-plus-circle"></i> Varyant Ekle</button></div>';
html+='</div></div></div>';
$('#addVariantFormContainer').html(html);
}

// Varyant ekle butonu
$(document).on('click','#addVariantBtn',function(){
const selectedValues=[];
const optionNames=[];
let isValid=true;

$('.variant-option').each(function(){
const val=$(this).val();
const optName=$(this).data('option-name');
const valName=$(this).find('option:selected').data('value-name');
const color=$(this).find('option:selected').data('color')||null;
if(!val){
alert(optName+' seçiniz!');
isValid=false;
return false;
}
selectedValues.push({option_value_id:val,name:valName,color:color,option_name:optName});
optionNames.push(optName+': '+valName);
});

if(!isValid)return;

const price=$('#new_price').val();
const stock=$('#new_stock').val();
const sku=$('#new_sku').val();
const barcode=$('#new_barcode').val();

if(!price||!stock||!sku||!barcode){
alert('Fiyat, stok, SKU ve barkod zorunlu!');
return;
}

const variantName=optionNames.join(' - ');
const optionValuesJson=JSON.stringify(selectedValues.map(v=>({option_value_id:v.option_value_id})));

// Tabloyu oluştur (yoksa)
if($('.variants-table').length===0){
let tableHtml='<div class="table-responsive"><table class="table table-bordered table-hover variants-table"><thead><tr><th>#</th>';
$('.variant-option').each(function(){
tableHtml+='<th>'+$(this).data('option-name')+'</th>';
});
tableHtml+='<th>Fiyat</th><th>İnd.Fiyat</th><th>Maliyet</th><th>Stok</th><th>SKU</th><th>Barkod</th><th>TNY</th><th>Ent.Kodu</th><th></th></tr></thead><tbody></tbody></table></div>';
$('#variantsContainer').html(tableHtml);
}

// Yeni satır ekle
let row='<tr><td>'+($('.variants-table tbody tr').length+1)+'</td>';
selectedValues.forEach(v=>{
if(v.color){
row+='<td><div style="display:inline-block;width:20px;height:20px;background:'+v.color+';border:1px solid #ddd;border-radius:3px;margin-right:5px;vertical-align:middle"></div>'+v.name+'</td>';
}else{
row+='<td><span class="badge bg-secondary">'+v.name+'</span></td>';
}
});

const idx=variantCounter++;
row+='<input type="hidden" name="variants['+idx+'][option_values]" value=\''+optionValuesJson+'\'>';
row+='<input type="hidden" name="variants['+idx+'][variant_name]" value="'+variantName+'">';
row+='<input type="hidden" name="variants['+idx+'][price]" value="'+price+'">';
row+='<input type="hidden" name="variants['+idx+'][sale_price]" value="'+($('#new_sale_price').val()||'')+'">';
row+='<input type="hidden" name="variants['+idx+'][cost]" value="'+($('#new_cost').val()||'')+'">';
row+='<input type="hidden" name="variants['+idx+'][stock]" value="'+stock+'">';
row+='<input type="hidden" name="variants['+idx+'][sku]" value="'+sku+'">';
row+='<input type="hidden" name="variants['+idx+'][barcode]" value="'+barcode+'">';
row+='<input type="hidden" name="variants['+idx+'][tny_code]" value="'+($('#new_tny_code').val()||'')+'">';
row+='<input type="hidden" name="variants['+idx+'][integration_code]" value="'+($('#new_integration_code').val()||'')+'">';
row+='<td>'+price+'₺</td>';
row+='<td>'+($('#new_sale_price').val()||'-')+'</td>';
row+='<td>'+($('#new_cost').val()||'-')+'</td>';
row+='<td>'+stock+'</td>';
row+='<td>'+sku+'</td>';
row+='<td>'+barcode+'</td>';
row+='<td>'+($('#new_tny_code').val()||'-')+'</td>';
row+='<td>'+($('#new_integration_code').val()||'-')+'</td>';
row+='<td><button type="button" class="btn btn-sm btn-danger remove-variant"><i class="bi bi-trash"></i></button></td></tr>';

$('.variants-table tbody').append(row);

// Formu temizle
$('.variant-option').val('');
$('#new_price,#new_sale_price,#new_cost,#new_sku,#new_barcode,#new_tny_code,#new_integration_code').val('');
$('#new_stock').val('0');

updateVariantNumbers();
});

// Varyant sil
$(document).on('click','.remove-variant',function(){
if(confirm('Silmek istediğinizden emin misiniz?')){
$(this).closest('tr').remove();
updateVariantNumbers();
}
});

// Varyant numaralarını güncelle
function updateVariantNumbers(){
$('.variants-table tbody tr').each(function(i){
$(this).find('td:first').text(i+1);
});
}

$('#productForm').on('submit',function(e){
if(!$('#name').val().trim()){
e.preventDefault();
alert('Ürün adı zorunlu!');
$('#general-tab').tab('show');
return false
}
if(!$('#model_code').val().trim()){
e.preventDefault();
alert('Model kodu zorunlu!');
$('#general-tab').tab('show');
return false
}
if(!$('#category_id').val()){
e.preventDefault();
alert('Kategori seçin!');
$('#general-tab').tab('show');
return false
}
if($('table.variants-table tbody tr').length===0){
e.preventDefault();
alert('Varyant oluşturun!');
$('#options-tab').tab('show');
return false
}
$(this).find('button[type="submit"]').prop('disabled',true).html('<i class="bi bi-hourglass-split"></i> Kaydediliyor...')
});

$('#category_id').select2({theme:'bootstrap-5'});
});
</script>
@endpush
@endsection
