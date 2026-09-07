<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BundleMapping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminBundleMappingController extends Controller
{
    public function index(): View
    {
        return view('admin.bundle-mappings.index', [
            'mappings' => BundleMapping::orderBy('network')->orderBy('amount')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.bundle-mappings.form', ['mapping' => new BundleMapping()]);
    }

    public function store(Request $request): RedirectResponse
    {
        BundleMapping::create($this->validated($request));

        return redirect()->route('admin.bundle-mappings.index')->with('success', 'Bundle created.');
    }

    public function edit(BundleMapping $bundleMapping): View
    {
        return view('admin.bundle-mappings.form', ['mapping' => $bundleMapping]);
    }

    public function update(Request $request, BundleMapping $bundleMapping): RedirectResponse
    {
        $bundleMapping->update($this->validated($request, $bundleMapping));

        return redirect()->route('admin.bundle-mappings.index')->with('success', 'Bundle updated.');
    }

    public function destroy(BundleMapping $bundleMapping): RedirectResponse
    {
        $bundleMapping->delete();

        return redirect()->route('admin.bundle-mappings.index')->with('success', 'Bundle deleted.');
    }

    private function validated(Request $request, ?BundleMapping $bundleMapping = null): array
    {
        // Schema (create + product-fields migrations): network, slug, amount, type,
        // package_code, fallback_package_code, available_from, available_until,
        // description, validity. There is no is_available column — availability is
        // computed from available_from/until on the model.
        $id = $bundleMapping?->id;

        $data = $request->validate([
            'network' => 'required|string|in:safaricom',
            'amount' => [
                'required',
                'numeric',
                'min:1',
                Rule::unique('bundle_mappings')
                    ->where(fn ($query) => $query->where('network', $request->input('network')))
                    ->ignore($id),
            ],
            'package_code' => 'required|string|max:64',
            'slug' => [
                'required',
                'string',
                'max:64',
                Rule::unique('bundle_mappings', 'slug')->ignore($id),
            ],
            'description' => 'required|string|max:255',
            'type' => 'required|string|in:data,sms,minutes',
            'validity' => 'nullable|string|max:64',
            'available_from' => 'nullable|date_format:H:i:s,H:i',
            'available_until' => 'nullable|date_format:H:i:s,H:i',
            'fallback_package_code' => 'nullable|string|max:64',
        ]);

        foreach (['validity', 'available_from', 'available_until', 'fallback_package_code'] as $field) {
            if (($data[$field] ?? null) === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }
}
