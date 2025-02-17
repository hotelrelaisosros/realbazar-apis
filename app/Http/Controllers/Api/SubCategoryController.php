<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubCategoryResource;
use App\Http\Resources\CategoryResource;

use App\Models\Category;
use App\Models\Product;

use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ImageHelper;

class SubCategoryController extends Controller
{
    public function show()
    {
        $subcategory = SubCategory::orderBy('id', 'DESC')->has('categories')->with('categories')->get();
        if (count($subcategory)) return response()->json(['status' => true, 'Message' => 'SubCategory found', 'SubCategory' => SubCategoryResource::collection($subcategory)], 200);
        return response()->json(['status' => false, 'Message' => 'SubCategory not found']);
    }

    public function showCategoryFront()
    {
        $cat = Category::has('subCategory')->get();

        if (count($cat)) return response()->json(['status' => true, 'Message' => 'SubCategory found', 'SubCategory' => CategoryResource::collection($cat)], 200);

        return response()->json(['status' => false, 'Message' => 'SubCategory not found']);
    }

    public function showSubCategoryFront($id)
    {

        if (empty($id)) return response()->json(['status' => false, 'Message' => 'Id not found']);

        $sub_cat = Product::with(['images', 'variation'])->where('sub_category_id', $id)->get();

        $format = new ImageHelper();

        foreach ($sub_cat as $product) {
            $product["variation_count"] = $product["variation"]->count();
            if (!empty($product->images)) {
                $product["images"] = $format->formatProductImages($product->images);
            }
        }

        if ($sub_cat->count()) return response()->json(['status' => true, 'Message' => 'SubCategory found', 'SubCategory' => $sub_cat], 200);
        return response()->json(['status' => false, 'Message' => 'SubCategory not found']);
    }

    public function fetchSubCategory($id)
    {
        if (empty($id)) return response()->json(['status' => false, 'Message' => 'Id not found']);
        $subcategory = SubCategory::has('categories')->with('categories')->where('category_id', $id)->get();


        $new_sub = SubCategoryResource::collection($subcategory);

        if (count($new_sub)) return response()->json(['status' => true, 'Message' => 'SubCategory found', 'SubCategory' => $new_sub], 200);
        return response()->json(['status' => false, 'Message' => 'SubCategory not found']);
    }

    public function add(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'category_id' => 'required',
            'sub_category' => 'required',
            'subcategory_image' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $category = Category::where('id', $request->category_id)->first();
        $subcategory = SubCategory::whereHas('categories', function ($query) use ($category) {
            $query->where('id', $category->id);
        })->where('name', $request->sub_category)->first();
        if (!is_object($subcategory)) {
            $subcategory = new SubCategory();
            $subcategory->category_id = $category->id;
            $subcategory->name = $request->sub_category;
            $subcategory->url = strtolower(preg_replace('/\s*/', '', $category->name . '/' . $request->sub_category));
            if (!empty($request->subcategory_image)) {
                $image = $request->subcategory_image;
                $filename = "SubCategory-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('subcategory', $filename, "public");
                $subcategory->image = "subcategory/" . $filename;
            }
            if (!$subcategory->save()) return response()->json(['status' => false, 'Message' => 'Sub Category not Added!']);
            $subcategories = SubCategory::has('categories')->with('categories')->where('id', $subcategory->id)->get();
            return response()->json(['status' => true, 'Message' => 'New Sub Category Added Successfully!', 'SubCategory' => SubCategoryResource::collection($subcategories)], 200);
        } else return response()->json(['status' => false, 'Message' => 'Sub Category already exist!']);
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'category_id' => 'required',
            'subcategory' => 'required|unique:categories,name,' . $request->id,
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $category = Category::where('id', $request->category_id)->first();
        $subcategory = SubCategory::where('id', $request->id)->first();
        if (empty($subcategory)) return response()->json(['status' => false, 'Message' => 'Sub Category not found']);
        $subcategory->category_id = $category->id;
        $subcategory->name = $request->subcategory;
        $subcategory->url = strtolower(preg_replace('/\s*/', '', $category->name . '/' . $request->subcategory));
        if (!empty($request->subcategory_image)) {
            $image = $request->subcategory_image;
            $filename = "SubCategory-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
            $image->storeAs('subcategory', $filename, "public");
            $subcategory->image = "subcategory/" . $filename;
        }
        if ($subcategory->save()) return response()->json(['status' => true, 'Message' => 'Sub Category Updated Successfully!', 'subcategory' => $subcategory ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Sub Category not Updated!']);
    }

    public function delete(Request $request)
    {
        $sub_category = SubCategory::where('id', $request->id)->first();
        if (!empty($sub_category)) {
            if ($sub_category->delete()) return response()->json(['status' => true, 'Message' => 'Sub Category Deleted'], 200);
            else return response()->json(['status' => false, 'Message' => 'Sub Category not deleted']);
        } else {
            return response()->json(['status' => false, 'Message' => 'Sub Category not found']);
        }
    }
}
