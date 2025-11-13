<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\OptionValue;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    public function index()
    {
        $options = Option::with('values')->orderBy('sort_order')->get();
        return view('admin.options.index', compact('options'));
    }

    public function create()
    {
        return view('admin.options.create');
    }

    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'name' => 'required|string|max:255|unique:options,name',
            'type' => 'required|in:select,color,image',
            'values_json' => 'required|string',
        ]);

        // Parse values JSON
        $valuesData = json_decode($request->values_json, true);

        // Validate values
        if (!$valuesData || count($valuesData) === 0) {
            return back()->withErrors(['values_json' => 'En az 1 değer eklemelisiniz!'])->withInput();
        }

        \DB::beginTransaction();
        try {
            // Create the option
            $option = Option::create([
                'name' => $request->name,
                'type' => $request->type,
                'sort_order' => Option::max('sort_order') + 1,
                'is_active' => true,
            ]);

            // Create option values
            foreach ($valuesData as $index => $valueData) {
                OptionValue::create([
                    'option_id' => $option->id,
                    'value' => $valueData['value'],
                    'color_code' => $valueData['color_code'] ?? null,
                    'image' => null, // Images can be uploaded later
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }

            \DB::commit();

            return redirect()->route('admin.options.index')
                ->with('success', "'{$option->name}' opsiyonu ve " . count($valuesData) . " değer başarıyla oluşturuldu!");

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Option creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Opsiyon kaydedilemedi: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Option $option)
    {
        $option->load('values');
        return view('admin.options.edit', compact('option'));
    }

    public function update(Request $request, Option $option)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'values' => 'required|array|min:1',
        ]);

        \DB::beginTransaction();
        try {
            $option->update(['name' => $request->name]);
            
            $existingIds = [];
            foreach ($request->values as $index => $valueData) {
                if (isset($valueData['id']) && $valueData['id']) {
                    $value = OptionValue::find($valueData['id']);
                    $value->update([
                        'value' => $valueData['value'],
                        'color_code' => $valueData['color_code'] ?? null,
                        'sort_order' => $index,
                    ]);
                    $existingIds[] = $value->id;
                } else {
                    $value = OptionValue::create([
                        'option_id' => $option->id,
                        'value' => $valueData['value'],
                        'color_code' => $valueData['color_code'] ?? null,
                        'sort_order' => $index,
                        'is_active' => true,
                    ]);
                    $existingIds[] = $value->id;
                }
            }

            OptionValue::where('option_id', $option->id)->whereNotIn('id', $existingIds)->delete();

            \DB::commit();
            return redirect()->route('admin.options.index')->with('success', 'Opsiyon güncellendi!');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Option $option)
    {
        $option->delete();
        return back()->with('success', 'Opsiyon silindi!');
    }

    public function toggleActive(Option $option)
    {
        $option->update(['is_active' => !$option->is_active]);
        return back()->with('success', 'Durum değiştirildi!');
    }

    public function toggleValueActive(OptionValue $value)
    {
        $value->update(['is_active' => !$value->is_active]);
        return back()->with('success', 'Durum değiştirildi!');
    }
}
