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
<div class="card"><div class="card-header bg-light"><h5 class="mb-0">Seçenekler</h5><small class="text-muted">Ürün için seçenekleri işaretleyin</small></div>
<div class="card-body">
@foreach($options as $option)
<div class="option-selector">
<h6><input type="checkbox" class="form-check-input option-checkbox" id="opt_{{$option->id}}" value="{{$option->id}}" data-option-name="{{$option->name}}" data-option-type="{{$option->type}}">
<label for="opt_{{$option->id}}" class="form-check-label ms-2"><strong>{{$option->name}}</strong></label></h6>
<div class="option-values mt-3" id="values_{{$option->id}}" style="display:none">
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
@endforeach
@if($options->isEmpty())<div class="alert alert-warning">Opsiyon yok. <a href="{{route('admin.options.create')}}" target="_blank">Ekleyin</a></div>@endif
</div></div>
</div>
<div class="tab-pane fade" id="variants">
<div class="card"><div class="card-header bg-light d-flex justify-content-between align-items-center">
<div><h5 class="mb-0">Varyantlar</h5><small class="text-muted">Seçeneklerden varyant oluştur</small></div>
<button type="button" class="btn btn-primary" id="generateVariantsBtn"><i class="bi bi-gear"></i> Oluştur</button>
</div>
<div class="card-body"><div id="variantsContainer"><div class="alert alert-info"><i class="bi bi-info-circle"></i> Önce Seçenekler sekmesinden seçim yapın</div></div></div>
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
$('.option-checkbox').on('change',function(){const id=$(this).val();const div=$('#values_'+id);$(this).is(':checked')?div.slideDown():(div.slideUp(),div.find('.option-value-checkbox').prop('checked',false))});
$('#generateVariantsBtn').on('click',function(){
const opts={};$('.option-checkbox:checked').each(function(){const id=$(this).val();const name=$(this).data('option-name');const type=$(this).data('option-type');const vals=[];$('#values_'+id+' .option-value-checkbox:checked').each(function(){vals.push({id:$(this).val(),name:$(this).data('value-name'),color:$(this).data('color-code')||null})});if(vals.length>0)opts[id]={name:name,type:type,values:vals}});
if(Object.keys(opts).length===0){alert('Opsiyon seçin!');$('#options-tab').tab('show');return}
const arrays=Object.values(opts).map(o=>o.values);
const combos=arrays.reduce((a,ar)=>a.flatMap(x=>ar.map(y=>[...(Array.isArray(x)?x:[x]),y])),[[]]);
let html='<div class="table-responsive"><table class="table table-bordered table-hover variants-table"><thead><tr><th>#</th>';
Object.values(opts).forEach(o=>html+='<th>'+o.name+'</th>');
html+='<th>Fiyat</th><th>İnd.Fiyat</th><th>Maliyet</th><th>Stok</th><th>SKU</th><th>Barkod</th><th>TNY Kodu</th><th>Ent.Kodu</th><th></th></tr></thead><tbody>';
combos.forEach((c,i)=>{
const name=c.map((v,j)=>Object.values(opts)[j].name+': '+v.name).join(' - ');
const json=JSON.stringify(c.map(v=>({option_value_id:v.id})));
html+='<tr><td>'+(i+1)+'</td>';
c.forEach(v=>{if(v.color)html+='<td><div style="display:inline-block;width:20px;height:20px;background:'+v.color+';border:1px solid #ddd;border-radius:3px;margin-right:5px;vertical-align:middle"></div>'+v.name+'</td>';else html+='<td><span class="badge bg-secondary">'+v.name+'</span></td>'});
html+='<input type="hidden" name="variants['+i+'][option_values]" value=\''+json+'\'>';
html+='<input type="hidden" name="variants['+i+'][variant_name]" value="'+name+'">';
html+='<td><input type="number" class="form-control form-control-sm" name="variants['+i+'][price]" placeholder="0.00" step="0.01" required></td>';
html+='<td><input type="number" class="form-control form-control-sm" name="variants['+i+'][sale_price]" placeholder="0.00" step="0.01"></td>';
html+='<td><input type="number" class="form-control form-control-sm" name="variants['+i+'][cost]" placeholder="0.00" step="0.01"></td>';
html+='<td><input type="number" class="form-control form-control-sm" name="variants['+i+'][stock]" placeholder="0" value="0" required></td>';
html+='<td><input type="text" class="form-control form-control-sm" name="variants['+i+'][sku]" placeholder="SKU" required></td>';
html+='<td><input type="text" class="form-control form-control-sm" name="variants['+i+'][barcode]" placeholder="Barkod" required></td>';
html+='<td><input type="text" class="form-control form-control-sm" name="variants['+i+'][tny_code]" placeholder="TNY"></td>';
html+='<td><input type="text" class="form-control form-control-sm" name="variants['+i+'][integration_code]" placeholder="Kod"></td>';
html+='<td><button type="button" class="btn btn-sm btn-danger remove-variant"><i class="bi bi-trash"></i></button></td></tr>'});
html+='</tbody></table></div><div class="alert alert-success mt-3"><i class="bi bi-check-circle"></i> <strong>'+combos.length+'</strong> varyant oluşturuldu</div>';
$('#variantsContainer').html(html)});
$(document).on('click','.remove-variant',function(){if(confirm('Silmek istediğinizden emin misiniz?'))$(this).closest('tr').remove()});
$('#productForm').on('submit',function(e){if(!$('#name').val().trim()){e.preventDefault();alert('Ürün adı zorunlu!');$('#general-tab').tab('show');return false}if(!$('#model_code').val().trim()){e.preventDefault();alert('Model kodu zorunlu!');$('#general-tab').tab('show');return false}if(!$('#category_id').val()){e.preventDefault();alert('Kategori seçin!');$('#general-tab').tab('show');return false}if($('table.variants-table tbody tr').length===0){e.preventDefault();alert('Varyant oluşturun!');$('#variants-tab').tab('show');return false}$(this).find('button[type="submit"]').prop('disabled',true).html('<i class="bi bi-hourglass-split"></i> Kaydediliyor...')});
$('#category_id').select2({theme:'bootstrap-5'});
});
</script>
@endpush
@endsection
