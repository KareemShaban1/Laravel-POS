<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        $products = Product::with('category');
        $products = $products->latest()->get();
        $categories = Category::where('status', true)->get();

        if ($request->ajax()) {
            return datatables()->of($products)
            ->addColumn('status', function ($product) {
                return $product->status ? 
                '<span class="badge badge-success">' . __('common.Active') . '</span>' : 
                '<span class="badge badge-danger">' . __('common.Inactive') . '</span>';
            })
            ->addColumn('image', function ($product) {
                return "<img src='" . $product->getImageUrl() . "' width='50' height='50'>";
            })
            ->addColumn('has_quantity', function ($product) {
                return $product->has_quantity ? 
                '<span class="badge badge-success">' . __('common.Yes') . '</span>' : 
                '<span class="badge badge-danger">' . __('common.No') . '</span>';
            })
            ->addColumn('created_at', function ($product) {
                return $product->created_at->format('Y-m-d H:i:s');
            })
            ->addColumn('updated_at', function ($product) {
                return $product->updated_at->format('Y-m-d H:i:s');
            })
            ->addColumn('action', function ($product) {
                $editUrl = route('products.edit', $product);
                $deleteUrl = route('products.destroy', $product);
                return "
               <a href='$editUrl' class='btn btn-primary'><i class='fas fa-edit'></i></a>
               <button class='btn btn-danger btn-delete' data-url='$deleteUrl'>
                   <i class='fas fa-trash'></i>
               </button>";
            })
            ->rawColumns(['action', 'status', 'image', 'has_quantity', 'created_at', 'updated_at'])
            ->make(true);
        }

        return view('products.index', compact('products', 'categories'));
    }



    public function cartProducts(Request $request)
    {
        // Create cache key
        $cacheKey = 'cart_products_' . md5($request->getQueryString());

        // Check cache first (only for non-search requests)
        if (!$request->filled('search') && cache()->has($cacheKey)) {
            $products = cache()->get($cacheKey);
        } else {
            $products = Product::with('category')
                ->where('status', true) // Only active products
                ->select(['id', 'name', 'barcode', 'price', 'quantity', 'image', 'category_id', 'status']);

            if ($request->filled('search')) {
                $products->where('name', 'LIKE', "%{$request->search}%");
            }

            if ($request->filled('category_id')) {
                $products->where('category_id', $request->category_id);
            }

            if ($request->boolean('paginate')) {
                $products = $products->latest()->paginate(10);
            } else {
                $products = $products->latest()->get();
            }

            // Cache for 5 minutes (only for non-search requests)
            if (!$request->filled('search')) {
                cache()->put($cacheKey, $products, 300);
            }
        }

        if ($request->wantsJson()) {
            return ProductResource::collection($products);
        }

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('status', true)->select(['id', 'name'])->get();
        return view('products.create', compact('categories'));
    }

    
    public function store(ProductStoreRequest $request)
    {
        try {
            $image_path = '';

            if ($request->hasFile('image')) {
                $image_path = $request->file('image')->store('products', 'public');
            }

            $product = Product::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'image' => $image_path,
                'barcode' => $request->barcode,
                'price' => $request->price,
                'has_quantity' => $request->has_quantity,
                'quantity' => $request->quantity ?? null,
                'status' => $request->status
            ]);

            if (!$product) {
                return redirect()->back()->with('error', __('product.error_creating'));
            }

            // Clear product cache
            $this->clearProductCache();

            return redirect()->route('products.index')->with('success', __('product.success_creating'));
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return redirect()->back()->with('error', __('product.error_creating'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $categories = Category::where('status', true)->select(['id', 'name'])->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $product->name = $request->name;
        $product->description = $request->description;
        $product->barcode = $request->barcode;
        $product->price = $request->price;
        $product->has_quantity = $request->has_quantity;
        $product->quantity = $request->quantity;
        $product->status = $request->status;
        $product->category_id = $request->category_id;

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::delete($product->image);
            }
            // Store image
            $image_path = $request->file('image')->store('products', 'public');
            // Save to Database
            $product->image = $image_path;
        }

        if (!$product->save()) {
            return redirect()->back()->with('error', __('product.error_updating'));
        }

        // Clear product cache
        $this->clearProductCache();

        return redirect()->route('products.index')->with('success', __('product.success_updating'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::delete($product->image);
        }
        $product->delete();

        // Clear product cache
        $this->clearProductCache();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Clear product cache
     */
    private function clearProductCache()
    {
        // Clear all cart product caches
        $cacheKeys = [
            'cart_products_' . md5(''),
            'cart_products_' . md5('category_id='),
        ];

        // Add cache keys for each category
        $categories = \App\Models\Category::pluck('id');
        foreach ($categories as $categoryId) {
            $cacheKeys[] = 'cart_products_' . md5("category_id={$categoryId}");
        }

        foreach ($cacheKeys as $key) {
            cache()->forget($key);
        }
    }
}
