<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:product-index')->only('index');
        $this->middleware('permission:product-store')->only('store');
        $this->middleware('permission:product-update')->only('update');
        $this->middleware('permission:product-delete')->only('destroy');
    }

    public function index()
    {
        return view('admin.products.index');
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return redirect()
                ->route('admin.products.index')
                ->with('success_alert', __('translation.product_deleted_successfully'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.products.index')
                ->with('error_alert', __('translation.error_deleting_product'));
        }
    }
}
