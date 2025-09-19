<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::latest()->get();

        if (request()->wantsJson()) {

            return datatables()->of($categories)
            ->addColumn('status', function ($category) {
                return $category->status ? 
                '<span class="badge badge-success">' . __('common.Active') . '</span>' : 
                '<span class="badge badge-danger">' . __('common.Inactive') . '</span>';
            })
            ->addColumn('description', function ($category) {
                return Str::limit($category->description, 30);
            })
            ->addColumn('image', function ($category) {
                return "<img src='" . $category->getImageUrl() . "' width='50' height='50'>";
            })
            ->addColumn('action', function ($category) {
                return "
                <button class='btn btn-primary btn-sm' onclick='editCategory($category->id)'>Edit</button>
                <button class='btn btn-danger btn-sm' onclick='deleteCategory($category->id)'>Delete</button>";
            })
            ->rawColumns(['action', 'image', 'status', 'description'])
            ->make(true);
            
        }

        return view('categories.index', compact('categories'));
    }

    public function categoriesFront()
    {
        $categories = Category::latest()->get();
        return response()->json($categories);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'category' => $category
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        if (request()->ajax()) {
            return response()->json([
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'status' => $category->status,
                    'image_url' => $category->getImageUrl()
                ]
            ]);
        }

        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'category' => $category
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Delete image if exists
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully');
    }
}
