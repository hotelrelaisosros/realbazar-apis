<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductsResource;
use App\Http\Resources\ProductVariationResource;
use App\Http\Resources\ProductImageResource;

use App\Http\Resources\ProductNonRingResource;
use App\Http\Resources\ProductWithImageResource;
use App\Http\Resources\SubCategoryResource;
use App\Http\Resources\MetalKarateResource;
use App\Http\Resources\ClarityResource;



use App\Models\AppNotification;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomePageImage;
use App\Models\LikeProduct;
use App\Models\Order;
use App\Models\Package;
use App\Models\PackagePayment;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductReview;
use App\Models\ProductVariation;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\UserProductHistory;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\NotiSend;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use App\Models\AccentStoneTypes;
use App\Models\BandWidth;
use App\Models\BespokeCustomization;
use App\Models\BespokeCustomizationType;
use App\Models\BirthStone;
use App\Models\ProductEnum;
use App\Models\ProngStyle;
use App\Models\RingSize;
use App\Models\SettingHeight;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function get_all_ring_product_images()
    {
        $products =   Product::with(['images', 'variation', 'subCategories.categories'])
            ->where('is_delete', false)
            ->where('sub_category_id', '=', 1)
            ->get();


        $format = new ImageHelper();


        foreach ($products as $product) {
            if (!empty($product["images"])) {
                $product["images"] = $format->formatProductImages($product["images"]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => count(value: $products) > 0 ? 'Products Found' : 'No Ring Category found',
            'products' => $products,
        ], 200);
    }

    public function get_all_products()
    {
        $products = Product::with(['images', 'variation', 'subCategories.categories', 'product_enum'])
            ->where('is_delete', false)
            ->get();

        $format = new ImageHelper();
        foreach ($products as $product) {
            if (!empty($product["images"])) {
                $product["images"] = $format->formatProductImages($product["images"]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => count($products) > 0 ? 'Products found' : 'No products found',
            'products' => $products,
        ], 200);
    }

    public function get_one_ring_product($productId)
    {
        $productExists = Product::isRing($productId)->exists();

        if ($productExists) {
            $ring = Product::with(['images', 'variation', 'subCategories.categories'])
                ->where('id', $productId)
                ->first();

            if ($ring) {
                $format = new ImageHelper();
                $ring->images = $format->formatProductImages($ring->images);

                return response()->json([
                    'status' => true,
                    'message' => 'Product found',
                    'product' => $ring,
                ], 200);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Product does not belong to ring category',
        ], 404);
    }


    public function get_one_product($productId)
    {
        $product = Product::with(['images', 'variation', 'subCategories.categories'])
            ->where('is_delete', false)
            ->where('id', $productId)
            ->first();

        if ($product) {
            return response()->json([
                'status' => true,
                'message' => 'Product found',
                'product' => $product,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }
    }

    public function get_by_category(Request $request)
    {
        // dd($request->all());

        $validated = $request->validate([
            'id' => 'nullable|integer',
            'value' => 'nullable|string|max:15',
        ]);

        $products = []; // Start with an empty collection

        if (!empty($validated['id'])) {

            $products = Product::where('sub_category_id', "=", $validated['id'])->get();
        } elseif (!empty($validated['value'])) {
            $subCategoryIds = DB::table('sub_categories')
                ->where('name', 'like', '%' . $validated['value'] . '%')
                ->pluck('id');

            if ($subCategoryIds->isNotEmpty()) {
                $products = Product::whereIn('sub_category_id', $subCategoryIds)->get();
            }
        }

        $format = new ImageHelper();
        foreach ($products as $product) {
            if (!empty($product["images"])) {
                $product["images"] = $format->formatProductImages($product["images"]);
            }
        }
        return response()->json([
            'status' => true,
            'message' => count($products) > 0 ? 'Products found' : 'No products found',
            'products' => $products,
        ], 200);
    }

    // Filter products by gem shape and metal type

    // it will serve the images first and the image names will be appended to it
    public function get_all_ring_products(Request $request) {}
    public function search_products_step_one(Request $request)
    {
        $validated = $request->validate([
            'gem_shape_id' => 'nullable|integer',
            'metal_type_id' => 'nullable|integer',
        ]);



        $prod = collect();


        if (!empty($validated['gem_shape_id'])) {
            $gem_shape = Product::where('shape_id', $validated['gem_shape_id'])->get();
            $prod = $prod->merge($gem_shape);
        }

        if (!empty($validated['metal_type_id'])) {
            $metalTypeProducts =  Product::where('metal_type_id', $validated['metal_type_id'])->get();
            $prod = $prod->merge($metalTypeProducts);
        }

        $format = new ImageHelper();

        foreach ($prod as $product) {
            if (!empty($product["images"])) {
                $product["images"] = $format->formatProductImages($product["images"]);
            }
        }
        return response()->json([
            'status' => true,
            'message' => count($prod) > 0 ? 'Products found' : 'No products found',
            'products' => $prod,
        ], 200);
    }


    public function search_products_homepage(Request $request)
    {
        $validated = $request->validate([
            'search_query' => 'required|string',
            'limit' => 'nullable|integer'
        ]);

        $products = collect();

        // Search for products by name
        $product_search = Product::where('title', 'like', '%' . $validated['search_query'] . '%')->get();
        $products = $products->merge($product_search);

        // Search for products by category name
        $category_search = collect(DB::select(
            "
        SELECT p.* 
        FROM products p
        JOIN sub_categories sc ON sc.id = p.sub_category_id
        WHERE sc.name LIKE ?",
            ['%' . $validated['search_query'] . '%']
        ));
        $products = $products->merge($category_search);

        if (!empty($validated['limit'])) {
            $products = $products->take($validated['limit']);
        }
        $format = new ImageHelper();
        foreach ($products as $product) {
            $product["images"] = $format->formatProductImages($product["images"]);
        }
        return response()->json([
            'status' => true,
            'message' => count($products) > 0 ? 'Products found' : 'No products found',
            'products' => $products
        ], 200);
    }


    public function home($role = null)
    {
        $all_product = [];
        $feature_product = [];
        $discount_product = [];
        $newArrivalProduct = [];
        $topRatingProduct = [];
        $justForYouProduct = [];
        $justForYouSlider = [];
        $trendingProduct = [];
        $bestSeller = [];
        $banner_header = [];
        $banner_body = [];
        $banner_footer = [];
        if ($role == 'retailer') {
            $all_product = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->take(5)->get();
            $feature_product = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_featured', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->take(5)->get();
            $discount_product = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('discount_price', '!=', null)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->take(5)->get();
            $newArrivalProduct = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_new_arrival', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->take(5)->get();
            $topRatingProduct = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->take(5)->get();
            $trendingProduct = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_trending', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->take(5)->get();
            $banner_header = Banner::orderBy('id', 'DESC')->where('is_header', true)->take(5)->get();
            $banner_body = Banner::orderBy('id', 'DESC')->where('is_body', true)->take(5)->get();
            $banner_footer = Banner::orderBy('id', 'DESC')->where('is_footer', true)->take(5)->get();
            $justForYouSlider = HomePageImage::orderBy('id', 'DESC')->where('is_just_for_you', true)->where('is_retailer', true)->where('is_app', true)->take(1)->get();
            $justForYouProduct = HomePageImage::orderBy('id', 'DESC')->where('is_just_for_you', true)->where('is_retailer', true)->where('is_app', true)->skip(1)->take(6)->get();
            $bestSeller = HomePageImage::orderBy('id', 'DESC')->where('is_best_seller', true)->where('is_retailer', true)->take(5)->get();
        }
        if ($role == 'wholesaler') {
            $all_product = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->take(5)->get();
            $feature_product = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_featured', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->take(5)->get();
            $discount_product = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('discount_price', '!=', null)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->take(5)->get();
            $newArrivalProduct = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_new_arrival', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->take(5)->get();
            $topRatingProduct = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->take(5)->get();
            $trendingProduct = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_trending', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->take(5)->get();
            $banner_header = Banner::orderBy('id', 'DESC')->where('is_header', true)->take(5)->get();
            $banner_body = Banner::orderBy('id', 'DESC')->where('is_body', true)->take(5)->get();
            $banner_footer = Banner::orderBy('id', 'DESC')->where('is_footer', true)->take(5)->get();
            $justForYouSlider = HomePageImage::orderBy('id', 'DESC')->where('is_just_for_you', true)->where('is_wholesaler', true)->where('is_app', true)->take(3)->get();
            $justForYouProduct = HomePageImage::orderBy('id', 'DESC')->where('is_just_for_you', true)->where('is_wholesaler', true)->where('is_app', true)->skip(3)->take(6)->get();
            $bestSeller = HomePageImage::orderBy('id', 'DESC')->where('is_best_seller', true)->where('is_wholesaler', true)->take(5)->get();
        }

        return response()->json([
            'status' => true,
            'Message' => 'Product found',
            'all_product' => ProductsResource::collection($all_product),
            'feature_product' => ProductsResource::collection($feature_product),
            'discount_product' => ProductsResource::collection($discount_product),
            'newArrivalProduct' => ProductsResource::collection($newArrivalProduct),
            'topRatingProduct' => ProductsResource::collection($topRatingProduct),
            'justForYouProduct' => $justForYouProduct ?? [],
            'justForYouSlider' => $justForYouSlider ?? [],
            'trendingProduct' => $trendingProduct ?? [],
            'bestSeller' => $bestSeller ?? [],
            'banner_header' => $banner_header ?? [],
            'banner_body' => $banner_body ?? [],
            'banner_footer' => $banner_footer ?? [],
        ], 200);
    }

    public function show($role = null, $skip = 0, $take = 0)
    {
        $all_product = [];
        if ($role == 'retailer') {
            $all_product = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->skip($skip)->take($take)->get();
            $all_product_count = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->count();
        }
        if ($role == 'wholesaler') {
            $all_product = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->skip($skip)->take($take)->get();
            $all_product_count = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->count();
        }
        return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($all_product), 'ProductsCount' => $all_product_count], 200);
    }

    public function wholesalerProducts()
    {
        $all_product = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->whereHas('user', function ($q) {
            $q->whereRelation('role', 'name', 'wholesaler');
        })->get();
        if (count($all_product)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($all_product)], 200);
        else return response()->json(['status' => false, 'Message' => 'Product not found', 'Products' => $all_product ?? []]);
    }

    public function appWholesalerProducts()
    {
        $wholesalers = User::has('products')->with(['role', 'products.images'])->where('role_id', 4)->get();
        if (count($wholesalers)) return response()->json(['status' => true, 'wholesalers' => $wholesalers ?? []], 200);
        return response()->json(['status' => false, 'Message' => 'not found']);
    }

    public function showAdminProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'skip' => 'required',
            'take' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $skip = $request->skip;
        $take = $request->take;
        $status = $request->status;
        // $role = $request->role;
        $search = $request->search;
        $all_product = Product::with(['images', 'variation', 'subCategories.categories'])->where('is_delete', false);
        $all_product_count = Product::with(['images', 'variation', 'subCategories.categories'])->where('is_delete', false);

        if (!empty($status)) {
            $all_product->where('status', $status);
            $all_product_count->where('status', $status);
        }

        if (!empty($search)) {
            $all_product->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('price', 'like', '%' . $search . '%')
                    ->orWhere('discount_price', 'like', '%' . $search . '%')
                    ->orWhere('desc', 'like', '%' . $search . '%')
                    ->orWhere('tags', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
            $all_product_count->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('price', 'like', '%' . $search . '%')
                    ->orWhere('discount_price', 'like', '%' . $search . '%')
                    ->orWhere('desc', 'like', '%' . $search . '%')
                    ->orWhere('tags', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }
        $all_products = $all_product->skip($skip)->take($take)->get();
        $format = new ImageHelper();


        foreach ($all_products as $product) {
            $product["images"] = $format->formatProductImages($product["images"]);
        }

        $all_products_counts = $all_product_count->count();
        if (count($all_products)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($all_products), 'ProductsCount' => $all_products_counts ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Product not found', 'Products' => $all_products ?? [], 'ProductsCount' => $all_products_counts ?? []]);
    }

    public function showSellerProduct($skip = 0, $take = 0)
    {
        $all_product = [];
        $all_product_count = [];
        if ($skip == 0 && $take == 0) {
            $all_product = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->where('user_id', auth()->user()->id)->get();
            $all_product_count = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->where('user_id', auth()->user()->id)
                ->count();
        } else {
            $all_product = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->where('user_id', auth()->user()->id)
                ->skip($skip)->take($take)->get();
            $all_product_count = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->where('user_id', auth()->user()->id)
                ->count();
        }
        if (count($all_product)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($all_product), 'ProductsCount' => $all_product_count ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Product not found', 'Products' => $all_product ?? [], 'ProductsCount' => $all_product_count ?? []]);
    }

    public function featuredProduct($role = null, $skip = 0, $take = 0)
    {
        $feature_product = [];
        if ($role == 'retailer') {
            $feature_product = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_featured', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->skip($skip)->take($take)->get();
            $feature_product_count = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_featured', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->count();
        }
        if ($role == 'wholesaler') {
            $feature_product = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_featured', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->skip($skip)->take($take)->get();
            $feature_product_count = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_featured', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->count();
        }
        if (count($feature_product)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($feature_product), 'ProductsCount' => $feature_product_count ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Product not found', 'Products' => $feature_product ?? [], 'ProductsCount' => $feature_product_count ?? []]);
    }

    public function discountProduct($role = null, $skip = 0, $take = 0)
    {
        $discount_product = [];
        if ($role == 'retailer') {
            $discount_product = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('discount_price', '!=', null)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->skip($skip)->take($take)->get();
            $discount_product_count = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('discount_price', '!=', null)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->count();
        }
        if ($role == 'wholesaler') {
            $discount_product = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('discount_price', '!=', null)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->skip($skip)->take($take)->get();
            $discount_product_count = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('discount_price', '!=', null)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->count();
        }
        if (count($discount_product)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($discount_product), 'ProductsCount' => $discount_product_count ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Product not found', 'Products' => $discount_product ?? [], 'ProductsCount' => $discount_product_count ?? []]);
    }

    public function newArrivalProduct($role = null, $skip = 0, $take = 0)
    {
        $newArrivalProduct = [];
        if ($role == 'retailer') {
            $newArrivalProduct = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_new_arrival', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->skip($skip)->take($take)->get();
            $newArrivalProductCount = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_new_arrival', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->count();
        }
        if ($role == 'wholesaler') {
            $newArrivalProduct = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_new_arrival', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->skip($skip)->take($take)->get();
            $newArrivalProductCount = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_new_arrival', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->count();
        }
        if (count($newArrivalProduct)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($newArrivalProduct), 'ProductsCount' => $newArrivalProductCount ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Product not found', 'Products' => $newArrivalProduct ?? [], 'ProductsCount' => $newArrivalProductCount ?? []]);
    }

    public function topRatingProduct($role = null, $skip = 0, $take = 0)
    {
        $topRatingProduct = [];
        if ($role == 'retailer') {
            $topRatingProduct = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->skip($skip)->take($take)->get();
            $topRatingProduct_count = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->count();
        }
        if ($role == 'wholesaler') {
            $topRatingProduct = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->skip($skip)->take($take)->get();
            $topRatingProduct_count = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->count();
        }
        if (count($topRatingProduct)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($topRatingProduct), 'ProductsCount' => $topRatingProduct_count ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Product not found', 'Products' => $topRatingProduct ?? [], 'ProductsCount' => $topRatingProduct_count ?? []]);
    }

    public function trendingProduct($role = null, $skip = 0, $take = 0)
    {
        $trendingProduct = [];
        if ($role == 'retailer') {
            $trendingProduct = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_trending', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->skip($skip)->take($take)->get();
            $trendingProduct_count = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_trending', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'retailer');
            })->count();
        }
        if ($role == 'wholesaler') {
            $trendingProduct = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_trending', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->skip($skip)->take($take)->get();
            $trendingProduct_count = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('is_trending', true)->whereHas('user', function ($q) {
                $q->whereRelation('role', 'name', 'wholesaler');
            })->count();
        }
        if (count($trendingProduct)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($trendingProduct), 'ProductsCount' => $trendingProduct_count ?? []], 200);
        else return response()->json(['status' => false, 'Message' => 'Product not found', 'Products' => $trendingProduct ?? [], 'ProductsCount' => $trendingProduct_count ?? []]);
    }

    public function vendorProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $all_product = Product::has('user')->with('user', 'images', 'variation', 'subCategories.categories', 'reviews.users')->where('user_id', $request->id)->get();
        if (count($all_product)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($all_product)], 200);
        return response()->json(['status' => false, 'Message' => 'Product not found', 'Products' => $all_product ?? []]);
    }

    public function showProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $id = explode(',', $request->id);
        $all_product = Product::whereIn('id', $id)->with(['user', 'images', 'variation', 'subCategories.categories'])->get();
        if (count($all_product)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($all_product)], 200);
        return response()->json(['status' => false, 'Message' => 'Product not found']);
    }

    public function search($name, $role = null)
    {
        if (!empty($name)) {
            $product = [];
            $names = explode(',', $name);
            if ($role == 'retailer') {
                $product = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->where(function ($query) use ($names) {
                    foreach ($names as $tag) {
                        $query->where('title', 'LIKE', '%' . $tag . '%')->orWhere('tags', 'LIKE', '%' . $tag . '%');
                    }
                })->whereHas('user', function ($q) {
                    $q->whereRelation('role', 'name', 'retailer');
                })->get();
            } else if ($role == 'wholesaler') {
                $product = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->where(function ($query) use ($names) {
                    foreach ($names as $tag) {
                        $query->where('title', 'LIKE', '%' . $tag . '%')->orWhere('tags', 'LIKE', '%' . $tag . '%');
                    }
                })->whereHas('user', function ($q) {
                    $q->whereRelation('role', 'name', 'wholesaler');
                })->get();
            } else {
                $product = Product::where(function ($query) use ($names) {
                    foreach ($names as $tag) {
                        $query->where('title', 'LIKE', '%' . $tag . '%')->orWhere('tags', 'LIKE', '%' . $tag . '%');
                    }
                })->get();
            }
            if (count($product)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($product)], 200);
            else return response()->json(['status' => false, 'Message' => 'Product not found', 'Products' => $product ?? []]);
        } else return response()->json(['status' => false, 'Message' => 'Parameter is null']);
    }

    public function add(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'title' => 'required',
            'price' => 'required',
            'discount' => 'nullable',
            'product_desc' => 'required',
            'product_single_image' => 'nullable',
            'product_multiple_images' => 'nullable|array',
            'variations' => 'nullable',
            'tags' => 'nullable',
            'sub_category_id' => 'required',
            // 'brand' => 'required',
            // 'product_status' => 'required',
            // 'product_selected_qty' => 'nullable',
            // 'category' => 'required',
            // 'featured' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        try {
            DB::beginTransaction();
            // $user = auth()->user();

            if (true) {


                $new_product = new Product();
                $new_product->user_id = '1';
                $new_product->sub_category_id = $request->sub_category_id;
                $new_product->title = $request->title;
                $new_product->price = $request->price;
                $new_product->discount_price = $request->discount ?? 0;
                $new_product->color = $request->color;
                $new_product->tags = json_encode($request->tags);
                $new_product->desc = $request->product_desc;
                $new_product->is_featured = $request->featured ?? false;

                // if (!$new_product->sub_category_id == 1) {
                if (!$new_product->save()) throw new Error("Product not added!");
                // }


                if ($request->hasFile('product_single_image')) {
                    $product_image = new ProductImage();
                    $product_image->product_id = $new_product->id;

                    // Single image save
                    $filename = "Product-" . time() . "-" . rand() . "." . $request->product_single_image->getClientOriginalExtension();
                    $request->product_single_image->storeAs('product/' . $new_product->id, $filename, "public");
                    $product_image->image = "product/" . $new_product->id . "/" . $filename;

                    // Multiple images save (if provided)
                    $multiple_images = [];
                    if ($request->has('product_multiple_images') && is_array($request->product_multiple_images)) {
                        foreach ($request->product_multiple_images as $image) {
                            $imageFilename = "Product-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                            $image->storeAs('product/' . $new_product->id . '/additional', $imageFilename, "public");
                            $multiple_images[] = "product/" . $new_product->id . '/additional' . "/" . $imageFilename;
                        }
                        $product_image->image_collection = str_replace('\/', '/', json_encode($multiple_images));
                    }

                    //update it as enum
                    if ($request->sub_category_id == 1) {
                        $product_image->name = "Platinum";
                        $product_image->small_image = "small_image.jpg";
                    }

                    if (!$product_image->save()) throw new Error("Product image not saved!");
                }


                // if (!empty($request->variations)) {
                //     foreach ($request->variations as $variation) {
                //         if (is_object($variation)) $variation = $variation->toArray();
                //         $newVariation = new ProductVariation();
                //         $newVariation->product_id = $new_product->id;
                //         $newVariation->size = $variation['size'];
                //         $newVariation->stock = $variation['stock'];
                //         $newVariation->price = $variation['price'];
                //         if (!$newVariation->save()) throw new Error("Product Variations not added!");
                //     }
                // }

                //ensure configurations for ring products
                if ($request->sub_category_id == 1 && $new_product->id) {
                    $product_enum = new ProductEnum();
                    // $product_enum->metal_types = json_encode([$product_image->id]);
                    $product_enum->gem_shape_id = 1;
                    // $product_enum->default_metal_id = $product_image->id;
                    $product_enum->band_width_ids = json_encode(BandWidth::pluck('id')->toArray());
                    $product_enum->accent_stone_type_ids = json_encode(AccentStoneTypes::pluck('id')->toArray());
                    $product_enum->setting_height_ids = json_encode(SettingHeight::pluck('id')->toArray());
                    $product_enum->prong_style_ids = json_encode(ProngStyle::pluck('id')->toArray());
                    $product_enum->ring_size_ids = json_encode(RingSize::pluck('id')->toArray());
                    $product_enum->bespoke_customization_ids = json_encode(BespokeCustomization::pluck('id')->toArray());
                    $product_enum->birth_stone_ids = json_encode(BirthStone::pluck('id')->toArray());
                    $product_enum->product_id = $new_product->id;


                    // if ($product_enum->save()) {
                    if (!$product_enum->save()) throw new Error("Customizations not addded");

                    // if (!$new_product->save()) throw new Error("Product not added even with customizations!");
                    // } else {
                    //     throw new Error("Customizations are not enabled for this product!");
                    // }
                }
                $products = Product::has('user')->with(['product_enum', 'user', 'images', 'variation', 'subCategories.categories'])
                    ->where('id', $new_product->id)->first();
                // $user = User::whereRelation('role', 'name', 'admin')->first();
                // $title = 'NEW PRODUCT';
                // $message = 'New Product has been added';
                // $appnot = new AppNotification();
                // $appnot->user_id = $user->id;
                // $appnot->notification = $message;
                // $appnot->navigation = $title;
                // $appnot->save();
                // NotiSend::sendNotif($user->device_token, '', $title, $message);
                DB::commit();
                return response()->json(['status' => true, 'Message' => 'Product Added Successfully!', 'Products' => new ProductsResource($products) ?? []], 200);
            } else throw new Error("Authenticated User Required!");
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }


    protected function check_product_exists($productId)
    {
        $product = Product::find($productId);
        if (!$product)
            return response()->json(['status' => false, 'Message' => 'product not found!'], 404);
    }

    public function update(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'title' => 'required',
            'price' => 'nullable',
            'discount' => 'nullable',
            'product_desc' => 'required',
            // 'product_single_image' => 'nullable',
            // 'product_multiple_images' => 'nullable|array',
            'variations' => 'nullable',
            'tags' => 'required',
            'sub_category_id' => 'required',

        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        try {
            DB::beginTransaction();

            $product = Product::find($request->id);
            if (!$product)
                return response()->json(['status' => false, 'Message' => 'product not found!'], 404);


            $product->sub_category_id = $request->sub_category_id;
            $product->title = $request->title;
            $product->price = $request->price ?? 0;
            $product->discount_price = $request->discount ?? 0;
            $product->tags = json_encode($request->tags);
            $product->desc = $request->product_desc;
            $product->is_featured = $request->featured ?? false;

            if (!$product->save()) throw new Error("Product not updated!");

            //product image should be updated by product image crud

            // Single image update for sub_category_id =1 only non rings are updatable

            // if ($product->sub_category_id != 1) {
            //     if ($request->hasFile('product_single_image')) {
            //         $product_image = ProductImage::where('product_id', $product->id)->first() ?? new ProductImage();
            //         $product_image->product_id = $product->id;

            //         $filename = "Product-" . time() . "-" . rand() . "." . $request->product_single_image->getClientOriginalExtension();
            //         $request->product_single_image->storeAs('product/' . $product->id, $filename, "public");
            //         $product_image->image = "product/" . $product->id . "/" . $filename;

            //         // Multiple images update
            //         $multiple_images = [];
            //         if ($request->has('product_multiple_images') && is_array($request->product_multiple_images)) {
            //             foreach ($request->product_multiple_images as $image) {
            //                 $imageFilename = "Product-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
            //                 $image->storeAs('product/' . $product->id . '/additional', $imageFilename, "public");
            //                 $multiple_images[] = "product/" . $product->id . '/additional' . "/" . $imageFilename;
            //             }
            //             $product_image->image_collection = str_replace('\/', '/', json_encode($multiple_images));
            //         }

            //         if (!$product_image->save()) throw new Error("Product image not saved!");
            //     }
            // }

            if ($product->sub_category_id == 1) {
                $product_enum = new ProductEnum();
                // $product_enum->metal_types = json_encode([$product_image->id]);
                $product_enum->gem_shape_id = 1;
                // $product_enum->default_metal_id = $product_image->id;
                $product_enum->band_width_ids = json_encode(BandWidth::pluck('id')->toArray());
                $product_enum->accent_stone_type_ids = json_encode(AccentStoneTypes::pluck('id')->toArray());
                $product_enum->setting_height_ids = json_encode(SettingHeight::pluck('id')->toArray());
                $product_enum->prong_style_ids = json_encode(ProngStyle::pluck('id')->toArray());
                $product_enum->ring_size_ids = json_encode(RingSize::pluck('id')->toArray());
                $product_enum->bespoke_customization_ids = json_encode(BespokeCustomization::pluck('id')->toArray());
                $product_enum->birth_stone_ids = json_encode(BirthStone::pluck('id')->toArray());
                $product_enum->product_id = $product->id;


                // if ($product_enum->save()) {
                if (!$product_enum->save()) throw new Error("Customizations not addded");

                // } else {
                //     throw new Error("Customizations are not enabled for this product!");
                // }
            }


            // if (!empty($request->variations)) {
            //     ProductVariation::where('product_id', $product->id)->delete();

            //     foreach ($request->variations as $variation) {
            //         if (is_object($variation)) $variation = $variation->toArray();
            //         $newVariation = new ProductVariation();
            //         $newVariation->product_id = $product->id;
            //         $newVariation->size = $variation['size'];
            //         $newVariation->stock = $variation['stock'];
            //         $newVariation->price = $variation['price'];
            //         if (!$newVariation->save()) throw new Error("Product Variations not added!");
            //     }
            // }

            $products = Product::has('user')->with(['images', 'variation', 'subCategories.categories'])
                ->where('id', $product->id)->first();

            DB::commit();
            return response()->json(['status' => true, 'Message' => 'Product Updated Successfully!', 'Products' => $products ?? []], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'Message' => $th->getMessage()]);
        }
    }

    public function image_by_variant(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'variant_id' => 'nullable|exists:product_variations,id',
            'product_id' => 'nullable|exists:products,id',
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'Message' => 'Validation errors',
                'errors' => $valid->errors()
            ]);
        }

        $all_image = collect();

        if (empty($request["variant_id"]) && !empty($request["product_id"])) {
            $all_image = ProductImage::where('product_id', $request["product_id"]);
        } elseif (!empty($request["variant_id"]) && empty($request["product_id"])) {
            $all_image = ProductImage::where('variant_id', $request["variant_id"]);
        } elseif (!empty($request["variant_id"]) && !empty($request["product_id"])) {
            $all_image = ProductImage::where('variant_id', $request["variant_id"])->where('product_id', $request["product_id"]);
        }

        $all_image = $all_image->get();


        $productHelper = new ImageHelper();
        $formattedImages = $productHelper->formatProductImages($all_image);

        if ($formattedImages->isNotEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'Product Image found',
                'images' => $formattedImages
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Product Image not found'
        ], 404);
    }

    public function image($productId)
    {
        $all_image = ProductImage::where('product_id', $productId)->get();


        $productHelper = new ImageHelper();
        $formattedImages = $productHelper->formatProductImages($all_image);

        if ($formattedImages->isNotEmpty()) {
            return response()->json(['status' => true, 'Message' => 'Product Image found', 'Images' => $formattedImages], 200);
        }

        return response()->json(['status' => false, 'Message' => 'Product Image not found']);
    }

    public function product_image_search($productImageId)
    {
        $all_image = ProductImage::where('id', $productImageId)->get();
        $productHelper = new ImageHelper();
        $formattedImages = $productHelper->formatProductImages($all_image);

        if ($formattedImages->isNotEmpty()) {
            return response()->json(['status' => true, 'Message' => 'Product Image found', 'Images' => $formattedImages], 200);
        }

        return response()->json(['status' => false, 'Message' => 'Product Image not found']);
    }

    public function delete(Request $request)
    {
        $product = Product::where('id', $request->id)->first();
        if (!empty($product)) {
            if ($product->is_delete == false) $product->is_delete = true;
            else $product->is_delete = false;
            if ($product->save()) return response()->json(['status' => true, 'Message' => 'Successfully deleted Product'], 200);
        } else {
            return response()->json(["status" => false, 'Message' => 'Product not deleted']);
        }
    }

    public function addImage(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_id' => 'nullable|numeric|exists:products,id',
            'product_single_image' => 'required|image|mimes:jpg,jpeg,png',
            'product_multiple_images' => 'nullable|array',
            'product_multiple_images.*' => 'image|mimes:jpg,jpeg,png',
            'product_small_image' => 'nullable',
            // 'name' => "nullable|string",
            'variant_id' => 'nullable|exists:product_variations,id',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $valid = $valid->validated();

        try {
            $this->check_product_exists($valid['product_id']);

            $product_image = new ProductImage();

            if (!empty($request->variant_id)) {
                $product_image->variant_id = $request->variant_id;
                $product_image->product_id = ProductVariation::where('id', $request->variant_id)->value('product_id');
            } elseif (!empty($request->product_id)) {
                $product_image->variant_id = null;
                $product_image->product_id = $request->product_id;
            } else {
                return response()->json(['status' => false, 'Message' => "Pass a product_id or variant_id"], 404);
            }


            if ($request->hasFile('product_single_image')) {
                // Ensure the product directory exists
                $productDir = 'product/' . ($request->product_id ?? $request->variant_id);
                if (!Storage::disk('public')->exists($productDir)) {
                    Storage::disk('public')->makeDirectory($productDir);
                }

                $filename = "Product-" . time() . "-" . rand() . "." . $request->product_single_image->getClientOriginalExtension();
                $request->product_single_image->storeAs($productDir, $filename, "public");
                $product_image->image = $productDir . "/" . $filename;
            }

            $multiple_images = [];
            if ($request->has('product_multiple_images') && is_array($request->product_multiple_images)) {
                foreach ($request->product_multiple_images as $image) {
                    // Ensure the additional images directory exists
                    $additionalDir = $productDir . '/additional';
                    if (!Storage::disk('public')->exists($additionalDir)) {
                        Storage::disk('public')->makeDirectory($additionalDir);
                    }

                    $imageFilename = "Product-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs($additionalDir, $imageFilename, "public");

                    $multiple_images[] = $additionalDir . '/' . $imageFilename;
                }
                $product_image->image_collection = json_encode($multiple_images, JSON_UNESCAPED_SLASHES);
            }

            if (!$product_image->save()) {
                throw new \Exception("Product image not saved!");
            }


            $image = ProductImage::with('product')->where("id", $product_image->id)->first();

            return response()->json(['status' => true, 'Message' => 'Product Image(s) Added Successfully!', "image" => $image], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'Message' => $e->getMessage()], 404);
        }
    }

    public function updateImage(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_image_id' => 'required|numeric|exists:product_images,id',
            'product_id' => 'required|numeric|exists:products,id',
            'product_single_image' => 'nullable|image',
            'product_multiple_images' => 'nullable|array',
            'product_small_image' => 'nullable|image',
            'name' => "nullable|string"
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $valid = $valid->validated();

        try {
            $this->check_product_exists($valid['product_id']);

            $isRing =  Product::isRing($valid['product_id'])->exists();


            $product_image = ProductImage::findOrFail($valid['product_image_id']);
            $product_image->product_id = $request->product_id;

            if ($request->hasFile('product_single_image')) {
                $filename = "Product-" . time() . "-" . rand() . "." . $request->product_single_image->getClientOriginalExtension();
                $request->product_single_image->storeAs('product/' . $request->product_id, $filename, "public");
                $product_image->image = "product/" . $request->product_id . "/" . $filename;

                if ($isRing) {
                    $product_image->name = $valid["name"];
                    $smallImageFilename = "Product-" . time() . "-" . rand() . "." . $request->product_single_image->getClientOriginalExtension();
                    $request->product_single_image->storeAs('product/' . $request->product_id . "/small-icon", $smallImageFilename, "public");
                    $product_image->small_image = "product/" . $request->product_id . "/small-icon/" . $smallImageFilename;
                }
            }

            $multiple_images = [];
            if ($request->has('product_multiple_images') && is_array($request->product_multiple_images)) {
                foreach ($request->product_multiple_images as $image) {
                    $imageFilename = "Product-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs('product/' . $request->product_id . '/additional', $imageFilename, "public");

                    $multiple_images[] = "product/" . $request->product_id . '/additional/' . $imageFilename;
                }
                $product_image->image_collection = json_encode($multiple_images, JSON_UNESCAPED_SLASHES);
            }

            if (!$product_image->save()) {
                throw new \Exception("Product image not updated!");
            }

            $image = ProductImage::with('product')->where("id", $product_image->id)->first();

            return response()->json(['status' => true, 'Message' => 'Product Image(s) Updated Successfully!', "image" => $image], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'Message' => $e->getMessage()], 404);
        }
    }



    public function deleteImage(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|integer|exists:product_images,id',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $valid = $valid->validated();
        $product = ProductImage::where('id', $request->id)->first();

        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Product image not found'], 404);
        }

        DB::beginTransaction();

        try {
            $isRing = Product::isRing($product->product_id)->exists();

            if ($isRing) {
                $productEnum = ProductEnum::where('product_id', $product->product_id)->first();

                if ($productEnum) {
                    $metalTypes = json_decode($productEnum->metal_types, true);
                    if (!is_array($metalTypes)) {
                        $metalTypes = [];
                    }

                    $updatedMetalTypes = array_filter($metalTypes, function ($typeId) use ($product) {
                        return $typeId != $product->id;
                    });

                    if (empty($updatedMetalTypes)) {
                        $productEnum->delete();
                    } else {
                        $productEnum->metal_types = json_encode(array_values($updatedMetalTypes));
                        $productEnum->save();
                    }
                }
            }

            // Delete the product image
            $product->delete();

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Image and associated records deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => false, 'message' => 'An error occurred while deleting the image', 'error' => $e->getMessage()]);
        }
    }



    public function showDeleteProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'skip' => 'required',
            'take' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $skip = $request->skip;
        $take = $request->take;
        $role = $request->role;
        $search = $request->search;
        $product = Product::has('user')->with(['user.role', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->where('is_delete', true);
        $product_count = Product::has('user')->with(['user.role', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->where('is_delete', true);
        if (!empty($role)) {
            $product->whereHas('user', function ($q) use ($role) {
                $q->whereRelation('role', 'name', $role);
            });
            $product_count->whereHas('user', function ($q) use ($role) {
                $q->whereRelation('role', 'name', $role);
            });
        }
        if (!empty($search)) {
            $product->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('price', 'like', '%' . $search . '%')
                    ->orWhere('discount_price', 'like', '%' . $search . '%')
                    ->orWhere('desc', 'like', '%' . $search . '%')
                    ->orWhere('tags', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
            $product_count->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('price', 'like', '%' . $search . '%')
                    ->orWhere('discount_price', 'like', '%' . $search . '%')
                    ->orWhere('desc', 'like', '%' . $search . '%')
                    ->orWhere('tags', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }
        $products = $product->skip($skip)->take($take)->get();
        $products_counts = $product_count->count();
        if (count($products)) return response()->json(['status' => true, 'Message' => 'Successfully Show Deleted Products', 'Products' => ProductsResource::collection($products), 'ProductsCount' => $products_counts ?? []], 200);
        else return response()->json(["status" => false, 'Message' => 'Products not found', 'Products' => $products ?? [], 'ProductsCount' => $products_counts ?? []]);
    }

    public function hardDelete($id)
    {
        if (empty($id)) return response()->json(["status" => false, 'Message' => 'Id not found']);
        $product = Product::where('id', $id)->where('is_delete', true)->first();
        if (!empty($product)) {
            if ($product->delete()) return response()->json(['status' => true, 'Message' => 'Successfully deleted Product'], 200);
        } else {
            return response()->json(["status" => false, 'Message' => 'Product not deleted']);
        }
    }

    public function allHardDelete()
    {
        $product = Product::where('is_delete', true)->get();
        if (count($product)) {
            foreach ($product as $key => $value) {
                if ($value->delete());
            }
            return response()->json(['status' => true, 'Message' => 'Successfully hard deleted Product'], 200);
        } else {
            return response()->json(["status" => false, 'Message' => 'Product not found']);
        }
    }

    public function statusChangeProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required',
            'message' => 'nullable',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        $product = Product::where('id', $request->id)->first();
        if (empty($product)) return response()->json(["status" => false, 'Message' => 'Product not Found']);
        $product->status = $request->status;
        if ($product->save()) {
            $user = $product->user;
            if ($product->status == 'approved') {
                $title = 'YOUR PRODUCT HAS BEEN APPROVED';
                $message = 'Dear ' . $user->username . ' your product has been approved from admin-The Real Bazaar';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, '', $title, $message);
                return response()->json(["status" => true, 'Message' => 'Product Status Change to Approved Successfully'], 200);
            } elseif ($product->status == 'rejected') {
                $title = 'YOUR PRODUCT HAS BEEN REJECTED';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $request->message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, '', $title, $request->message);
                return response()->json(["status" => true, 'Message' => 'Product Status Change to Rejected Successfully'], 200);
            } else {
                $title = 'YOUR PRODUCT HAS BEEN PENDING';
                $message = 'Dear ' . $user->username . ' your product has been pending from admin-The Real Bazaar';
                $appnot = new AppNotification();
                $appnot->user_id = $user->id;
                $appnot->notification = $message;
                $appnot->navigation = $title;
                $appnot->save();
                NotiSend::sendNotif($user->device_token, '', $title, $message);
                return response()->json(["status" => true, 'Message' => 'Product Status Change to Pending Successfully'], 200);
            }
        } else return response()->json(["status" => false, 'Message' => 'Product Status Change not Successfully']);
    }

    public function productStatusChange(Request $request)
    {
        $product = Product::where('id', $request->id)->first();
        if (!empty($product)) {
            if ($product->is_active == false) $product->is_active = true;
            else $product->is_active = false;
            if ($product->save()) return response()->json(['status' => true, 'Message' => 'Successfully status change Product'], 200);
        } else return response()->json(["status" => false, 'Message' => 'Product Status not change']);
    }

    public function productStatusTrending($id)
    {
        if (empty($id)) return response()->json(['status' => false, 'Message' => 'Id not found']);
        $trending = Product::where('id', $id)->first();
        if (empty($trending)) return response()->json(['status' => false, 'Message' => 'Trending not found']);
        if ($trending->is_trending == false) $trending->is_trending = true;
        else $trending->is_trending = false;
        if ($trending->save()) return response()->json(['status' => true, 'Message' => 'Trending save', 'Product' => new ProductsResource($trending)], 200);
        else return response()->json(['status' => false, 'Message' => 'Trending not save']);
    }

    public function likeProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $likeExist = LikeProduct::where('user_id', auth()->user()->id)->where('product_id', $request->product_id)->first();
        if (is_object($likeExist)) {
            if ($likeExist->delete()) return response()->json(['status' => true, 'Message' => "UnLike Successfully"], 200);
            return response()->json(['status' => false, 'Message' => "UnLike not Successfull"]);
        }
        $like = new LikeProduct();
        $like->user_id = auth()->user()->id;
        $like->product_id = $request->product_id;
        if ($like->save()) return response()->json(['status' => true, 'Message' => "Like Successfully"], 200);
        return response()->json(['status' => false, 'Message' => "Like not Successfull"]);
    }

    public function reviewProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_id' => 'required',
            'stars' => 'required|lt:6',
            'comments' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $review = new ProductReview();
        $review->user_id = auth()->user()->id;
        $review->product_id = $request->product_id;
        $review->stars = $request->stars;
        $review->comments = $request->comments;
        if ($review->save()) {
            $products = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->where('id', $request->product_id)->first();

            return response()->json(['status' => true, 'Message' => "Review Successfully", 'Products' => new ProductsResource($products) ?? []], 200);
        }
        return response()->json(['status' => false, 'Message' => "Review not Successfull"]);
    }

    public function historyProduct()
    {
        $historyProduct = Product::has('user')->with(['user', 'images', 'variation', 'subCategories.categories', 'reviews.users'])->whereHas('history', function ($query) {
            $query->where('user_id', auth()->user()->id);
        })->get();

        if (count($historyProduct)) return response()->json(['status' => true, 'Message' => 'Product found', 'Products' => ProductsResource::collection($historyProduct)], 200);
        return response()->json(['status' => false, 'Message' => 'Product not found']);
    }

    public function addHistoryProduct(Request $request)
    {
        $valid = Validator::make($request->all(), [
            // 'user_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $history = UserProductHistory::where('user_id', auth()->user()->id)->where('product_id', $request->product_id)->first();
        if (!empty($history)) return response()->json(['status' => true, 'Message' => 'Users product exist in history'], 200);
        $product = new UserProductHistory();
        $product->user_id = auth()->user()->id;
        $product->product_id = $request->product_id;
        if ($product->save()) return response()->json(['status' => true, 'Message' => 'Users product added in history'], 200);
        return response()->json(['status' => false, 'Message' => 'Users product not added in history']);
    }

    public function seller_totalsales_count()
    {
        $seller_totalsales_count = Order::where('seller_id', auth()->user()->id)->groupBy('seller_id')
            ->selectRaw('seller_id,sum(net_amount) AS net_amount')->get();

        $seller_todaysales_count = Order::where('seller_id', auth()->user()->id)
            ->where('order_date', Carbon::today())
            ->selectRaw('seller_id, sum(net_amount) AS net_amount')->groupBy('seller_id')->get();

        $submonth = Carbon::now();
        $subweek = Carbon::now();

        $seller_lastmonthsales_count = Order::where('seller_id', auth()->user()->id)
            ->where('order_date', '>=', $submonth->submonth())
            ->where('order_date', '<=', Carbon::today())
            ->selectRaw('seller_id, sum(net_amount) AS net_amount')->groupBy('seller_id')->get();
        $seller_lastweeksales_count = Order::where('seller_id', auth()->user()->id)
            ->where('order_date', '>=', $subweek->subweek())
            ->where('order_date', '<=', Carbon::today())
            ->selectRaw('seller_id, sum(net_amount) AS net_amount')->groupBy('seller_id')->get();
        return response()->json(["status" => true, 'totalsales_count' => $seller_totalsales_count, 'lastmonthsales_count' => $seller_lastmonthsales_count, 'todaysales_count' => $seller_todaysales_count, 'lastweeksales_count' => $seller_lastweeksales_count], 200);
    }

    public function seller_products_count()
    {
        $seller_products_count = Product::where('user_id', auth()->user()->id)->count();
        $seller_category_count = SubCategory::with('categories:id,name')->withCount('products')->get();
        return response()->json([
            "status" => true,
            'products_count' => $seller_products_count,
            'category_count' => $seller_category_count
        ], 200);
    }

    public function seller_top_products()
    {
        $seller_top_products = Product::where('user_id', auth()->user()->id)->withCount('orders')->get();
        $seller_top_products = $seller_top_products->sortByDesc('orders_count')->values();
        return response()->json(["status" => true, 'seller_top_products' => $seller_top_products], 200);
    }

    public function seller_top_customers()
    {
        $seller_top_customers = Order::selectRaw('user_id, SUM(net_amount) as total_amount')->with('users')->where('seller_id', auth()->user()->id)->groupBy('user_id')->get();
        $seller_top_customers = $seller_top_customers->sortByDesc('total_amount')->values();
        return response()->json(["status" => true, 'seller_top_customers' => $seller_top_customers], 200);
    }

    public function admin_totalsales_count()
    {
        $seller_totalsales_count = Payment::selectRaw('sum(total) AS total')->get();

        $seller_todaysales_count = Payment::whereDate('created_at', Carbon::today())
            ->selectRaw('sum(total) AS total')->get();

        $submonth = Carbon::now();
        $subweek = Carbon::now();

        $seller_lastmonthsales_count = Payment::where('created_at', '>=', $submonth->submonth())
            ->where('created_at', '<=', Carbon::today())
            ->selectRaw('sum(total) AS total')->get();

        $seller_lastweeksales_count = Payment::where('created_at', '>=', $subweek->subweek())
            ->where('created_at', '<=', Carbon::today())
            ->selectRaw('sum(total) AS total')->get();

        return response()->json(["status" => true, 'totalsales_count' => $seller_totalsales_count, 'lastmonthsales_count' => $seller_lastmonthsales_count, 'todaysales_count' => $seller_todaysales_count, 'lastweeksales_count' => $seller_lastweeksales_count], 200);
    }

    public function admin_vendor_count()
    {
        $vendor_count = User::whereHas('role', function ($query) {
            $query->where('name', 'seller');
        })->count();
        $vendor_product_count = User::withCount('products')->get();
        $vendor_product_count = $vendor_product_count->sortByDesc('products_count')->values();
        return response()->json([
            "status" => true,
            'vendors_count' => $vendor_count,
            'vendor_products_count' => $vendor_product_count
        ], 200);
    }

    public function seller_top_sales($role = null)
    {
        if ($role == 'wholesaler') {
            $seller_top_sales = User::withCount('sellers_orders_products')
                ->whereHas('role', function ($query) {
                    $query->where('name', 'wholesaler');
                })->get();
        } elseif ($role == 'retailer') {
            $seller_top_sales = User::withCount('sellers_orders_products')
                ->whereHas('role', function ($query) {
                    $query->where('name', 'retailer');
                })->get();
        } else {
            $seller_top_sales = User::withCount('sellers_orders_products')
                ->whereHas('role', function ($query) {
                    $query->where('name', 'retailer')->orWhere('name', 'wholesaler');
                })->get();
        }
        $seller_top_sales = $seller_top_sales->sortByDesc('sellers_orders_products_count')->take(10)->values();
        if (count($seller_top_sales)) return response()->json(["status" => true, 'seller_top_sales' => $seller_top_sales ?? []], 200);
        else return response()->json(["status" => false, 'seller_top_sales' => $seller_top_sales ?? []]);
    }

    public function admin_customer_count()
    {
        $customer_count = User::whereHas('role', function ($query) {
            $query->where('name', 'user');
        })->count();
        $top_customers = Order::selectRaw('user_id, SUM(net_amount) as total_amount')
            ->with('users')->groupBy('user_id')->get();
        $top_customers = $top_customers->sortByDesc('total_amount')->values();
        return response()->json(["status" => true, 'customers_count' => $customer_count, 'top_customers' => $top_customers], 200);
    }

    public function seller_line_chart()
    {
        $lineChart = Order::where('seller_id', auth()->user()->id)
            ->selectRaw("COUNT(*) as orders")
            ->selectRaw("sum(net_amount) as total_amount")
            ->selectRaw("MONTHNAME(created_at) as month_name")
            ->selectRaw("DATE(created_at) as date")
            ->selectRaw('max(created_at) as createdAt')
            ->whereMonth('created_at', date('m'))
            ->groupBy('month_name')
            ->groupBy('date')
            ->orderBy('createdAt')
            ->get();
        return response()->json(["status" => true, 'lineChart' => $lineChart], 200);
    }
    public function updateProductEnums(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',

            'band_width_ids' => 'nullable|array',
            'accent_stone_type_ids' => 'nullable|array',
            'setting_height_ids' => 'nullable|array',
            'prong_style_ids' => 'nullable|array',
            'ring_size_ids' => 'nullable|array',
            'bespoke_customization_ids' => 'nullable|array',
            'birth_stone_ids' => 'nullable|array',
        ]);

        // Fetch the existing ProductEnum record or create a new one
        $productEnum = ProductEnum::firstOrNew(['product_id' => $validatedData['product_id']]);

        if (!$productEnum->exists) {
            // Insert new record logic
            $productEnum->product_id = $validatedData['product_id'];

            $productEnum->band_width_ids = isset($validatedData['band_width_ids']) ? json_encode($validatedData['band_width_ids']) : null;
            $productEnum->accent_stone_type_ids = isset($validatedData['accent_stone_type_ids']) ? json_encode($validatedData['accent_stone_type_ids']) : null;
            $productEnum->setting_height_ids = isset($validatedData['setting_height_ids']) ? json_encode($validatedData['setting_height_ids']) : null;
            $productEnum->prong_style_ids = isset($validatedData['prong_style_ids']) ? json_encode($validatedData['prong_style_ids']) : null;
            $productEnum->ring_size_ids = isset($validatedData['ring_size_ids']) ? json_encode($validatedData['ring_size_ids']) : null;
            $productEnum->bespoke_customization_ids = isset($validatedData['bespoke_customization_ids']) ? json_encode($validatedData['bespoke_customization_ids']) : null;
            $productEnum->birth_stone_ids = isset($validatedData['birth_stone_ids']) ? json_encode($validatedData['birth_stone_ids']) : null;

            $productEnum->save();

            return response()->json(['message' => 'New product enum created successfully', 'data' => $productEnum], 201);
        }

        // Update existing record logic
        $updatedFields = [];


        if (isset($validatedData['band_width_ids'])) {
            $updatedFields['band_width_ids'] = json_encode($validatedData['band_width_ids']);
        }
        if (isset($validatedData['accent_stone_type_ids'])) {
            $updatedFields['accent_stone_type_ids'] = json_encode($validatedData['accent_stone_type_ids']);
        }
        if (isset($validatedData['setting_height_ids'])) {
            $updatedFields['setting_height_ids'] = json_encode($validatedData['setting_height_ids']);
        }
        if (isset($validatedData['prong_style_ids'])) {
            $updatedFields['prong_style_ids'] = json_encode($validatedData['prong_style_ids']);
        }
        if (isset($validatedData['ring_size_ids'])) {
            $updatedFields['ring_size_ids'] = json_encode($validatedData['ring_size_ids']);
        }
        if (isset($validatedData['bespoke_customization_ids'])) {
            $updatedFields['bespoke_customization_ids'] = json_encode($validatedData['bespoke_customization_ids']);
        }
        if (isset($validatedData['birth_stone_ids'])) {
            $updatedFields['birth_stone_ids'] = json_encode($validatedData['birth_stone_ids']);
        }

        if (!empty($updatedFields)) {
            $productEnum->update($updatedFields);
        }

        return response()->json(['message' => 'Product enum updated successfully', 'data' => $productEnum], 200);
    }

    public function getAllBracProducts(Request $request)
    {

        $query = ProductVariation::with(['product', 'product_images', 'metal_karate', 'clarity'])
            ->whereHas('product', function ($q) {
                $q->where('sub_category_id', 2);
            });

        $productVariations = $query->get();

        //change this  as only one image record is needed for the variation
        $data = $productVariations->map(function ($variation) {
            return [
                'product' => new ProductNonRingResource($variation->product),
                'images' => $variation->product_images->count() > 0
                    ? ProductImageResource::collection($variation->product_images)
                    : [],
                'variation' => new ProductVariationResource($variation),
                'karate'  => MetalKarateResource::collection($variation->metal_karate),
                'clarity' => ClarityResource::collection($variation->clarity),
            ];
        });

        return response()->json([
            'message' => 'Ring2 products',
            'data' => $data,
        ], 200);
    }

    public function showSpecificBracVarition(Request $request)
    {
        // Validate the input request
        $valid = Validator::make($request->all(), [
            'variant_id' => 'required|exists:product_variations,id',
            // 'metal_type_id ' => 'required|exists:metal_type_categories,id',
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'Message' => 'Validation errors',
                'errors' => $valid->errors(),
            ]);
        }

        $variation = ProductVariation::with(['product', 'product_images', 'metal_karate', 'clarity'])
            ->where('id', $request["variant_id"])
            ->whereHas('product', function ($q) {
                $q->where('sub_category_id', 2);
            })
            ->first();



        if (!$variation) {
            return response()->json([
                'status' => false,
                'message' => 'Variation not found',
            ], 404);
        }


        $other_variants = ProductVariation::where('product_id', $variation->product_id)
            ->with('product_images')
            ->where('id', '!=', $variation->id)
            ->get();
        $variation->other_variants = $other_variants;

        // Return the response
        return response()->json([
            'message' => 'Product with variations',
            'data' => [
                'product' => new ProductNonRingResource($variation->product),
                'image' => $variation["product_images"]->count() > 0 ?  ProductImageResource::collection($variation["product_images"]) : [],

                'varation' => ProductVariationResource::collection(collect([$variation])),
                'karate'  => MetalKarateResource::collection($variation->metal_karate),
                'clarity' => ClarityResource::collection($variation->clarity),
                'other_vairants' => $other_variants,
            ],
            'is_variation' => true
        ], 200);
    }

    //step1
    public function getAllRingProducts(Request $request)
    {

        $valid = Validator::make($request->all(), [
            'metal_type' => 'nullable|exists:metal_type_categories,id',
            'gem_shapes' => 'nullable|exists:gemshapes,id',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $query = ProductVariation::with(['product', 'product_images'])
            ->whereHas('product', function ($q) {
                $q->where('sub_category_id', 1);
            });

        if ($request->metal_type) {
            $query = $query->where('metal_type_id', $request->metal_type);
        }
        if ($request->gem_shapes) {
            $query = $query->where('gem_shape_id', $request->gem_shapes);
        }

        $productVariations = $query->get();

        $data = $productVariations->map(function ($variation) {
            return [
                'product' => new ProductNonRingResource($variation->product),
                'images' => $variation->product_images->count() > 0
                    ? ProductImageResource::collection($variation->product_images)
                    : [],
                'variation' => new ProductVariationResource($variation),
            ];
        });

        return response()->json([
            'message' => 'Ring products',
            'data' => $data,
        ], 200);
        // // modify images
        // $format = new ImageHelper();
        // foreach ($query as $product) {
        //     if (!empty($product["images"])) {
        //         $product["images"] = $format->formatProductImages($product["images"]);
        //     }
        // }
        // modify prices


        // foreach ($query as $key => $title) {
        //     if (isset($title['title']) && isset($title['product']['title'])) {
        //         $product_title = $query[$key]['product_title'] = $title['product']['title'] . " - " . $title['title'];
        //     }
        // }

        // foreach ($query as $key => $title) {
        //     if (isset($title['price']) && isset($title['product']['price'])) {
        //         $total_price =  $query[$key]['total_price'] = $title['product']['price'] + $title['price'] - $title['product']['discount_price'];
        //     }
        //     if (isset($title['product']['discount_price'])) {
        //         $total_discount = $query[$key]['total_discount'] = $title['product']['discount_price'];
        //     }
        // }



        // $format = new ImageHelper();
        // foreach ($format as $product) {
        //     $product["images"] = $format->formatProductImages($product["images"]);
        // }
        // $format = new ImageHelper();
        // foreach ($format as $product) {
        //     $product["images"] = $format->formatProductImages($product["images"]);
        // }

        // // modify title
        // $format = new ImageHelper();
        // foreach ($rings as $product) {
        //     $product["images"] = $format->formatProductImages($product["images"]);
        // }


    }


    // step2

    public function showSpecificRingVarition(Request $request)
    {
        // Validate the input request
        $valid = Validator::make($request->all(), [
            'variant_id' => 'required|exists:product_variations,id',
            // 'metal_type_id ' => 'required|exists:metal_type_categories,id',
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'Message' => 'Validation errors',
                'errors' => $valid->errors(),
            ]);
        }

        $variation = ProductVariation::with(['product', 'product_images', 'metal_type'])
            ->where('id', $request["variant_id"])
            ->where('metal_type_id', $request['metal_type_id'])
            ->first();



        if (!$variation) {
            return response()->json([
                'status' => false,
                'message' => 'Variation not found',
            ], 404);
        }


        //product enumerations

        $product_id = $variation->product_id;


        // if ($enum) {
        $productEnum = ProductEnum::where('product_id', $product_id)->first();

        // if (!$productEnum) {
        //     return;
        // }

        // Map the related data models based on the IDs stored in the ProductEnum
        if ($productEnum) {
            $productEnum = [
                'band_width' => $this->getRelatedModels($productEnum->band_width_ids, BandWidth::class),
                'accent_stone_types' => $this->getRelatedModels($productEnum->accent_stone_type_ids, AccentStoneTypes::class),
                'setting_heights' => $this->getRelatedModels($productEnum->setting_height_ids, SettingHeight::class),
                'prong_styles' => $this->getRelatedModels($productEnum->prong_style_ids, ProngStyle::class),
                'ring_sizes' => $this->getRelatedModels($productEnum->ring_size_ids, RingSize::class),
                'bespoke_customizations' => $this->getRelatedModels($productEnum->bespoke_customization_ids, BespokeCustomization::class),
                'birth_stones' => $this->getRelatedModels($productEnum->birth_stone_ids, BirthStone::class),
            ];
        }

        $variation["enumeration"] = $productEnum ?? null;
        // }

        $other_variants = ProductVariation::where('product_id', $product_id)
            ->with('product_images')
            ->where('id', '!=', $variation->id)
            ->get(['id', 'metal_type_id']);
        $variation->other_variants = $other_variants;

        // Return the response
        return response()->json([
            'message' => 'Product with variations',
            'data' => [
                'product' => new ProductNonRingResource($variation->product),
                'image' => $variation["product_images"]->count() > 0 ?  ProductImageResource::collection($variation["product_images"]) : [],

                'varation' => ProductVariationResource::collection(collect([$variation])),
                'enumerations' => $variation["enumeration"],
                'other_vairants' => $other_variants,
            ],
            'is_variation' => true
        ], 200);
    }


    public function getSpecificNonRing(Request $request)
    {

        $valid = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variations,id',
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'Message' => 'Validation errors',
                'errors' => $valid->errors(),
            ]);
        }

        $product = Product::isRing($request["product_id"])->exists();
        if ($product) {
            return response()->json([
                'status' => false,
                'Message' => 'Method is only for non ring based',
            ], 404);
        }
        $product  = Product::with(['variation', 'images'])->where('id', $request["product_id"])->first();

        $format = new ImageHelper();


        foreach ($product as $producta) {
            if (!empty($producta["images"])) {
                $producta["images"] = $format->formatProductImages($producta["images"]);
            }
        }


        if (count($product["variation"]) > 1) {

            $variantId = $request->has("variant_id")
                ? $request["variant_id"]
                : ProductVariation::where('product_id', $product["id"])->inRandomOrder()->value('id');

            $variation = ProductVariation::with(['product_images', 'product'])
                ->where('id', $variantId)
                ->first();

            // print_r($variation->product_images);
            $product_id = $variation->product_id;

            $other_variants = ProductVariation::where('product_id', $product_id)
                ->with('product_images')

                ->where('id', '!=', $variantId)
                ->get(['id']);

            // $variation->other_variants = $other_variants;


            return response()->json([
                'message' => 'Product with variations',
                'data' => [
                    'product' => new ProductNonRingResource($variation->product),
                    'image' => $variation["product_images"]->count() > 0 ?  ProductImageResource::collection($variation["product_images"]) : [],

                    'varation' => ProductVariationResource::collection(collect([$variation])),
                    'other_vairants' => $other_variants,
                ],
                'is_variation' => true
            ], 200);
        }
        return response()->json([
            'message' => 'Product witout variations',
            'data' => $product,
            'is_variation' => false

        ], 200);
    }
    private  function getRelatedModels($ids, $modelClass)
    {
        if (empty($ids)) {
            return [];
        }

        $idArray = explode(',', $ids);

        return $modelClass::whereIn('id', $idArray)->get()->toArray();
    }
    public function getAllNonRingProducts(Request $request)
    {

        $valid = Validator::make($request->all(), [
            'sub_category_id' => 'required|exists:sub_categories,id',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        if ($request["sub_category_id"] == 1) {
            return response()->json(['status' => false, 'Message' => 'Method is only for non customizable products']);
        }

        $query = Product::with('images')->where("sub_category_id", $request["sub_category_id"])->get();

        // $data = $query->map(function ($variation) {
        //     return [
        //         'product' => new ProductNonRingResource($variation),
        //     ];
        // });

        return response()->json([
            'message' => 'Ring products',
            'data' => $query,
        ], 200);
    }

    public function deleteOneImageRecord(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'id' => 'required|exists:product_images,id',
            'image_url' => 'required|string',
            'type' => 'required|in:main,additional',
        ]);
        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'Message' => 'Validation errors',
                'errors' => $valid->errors(),
            ]);
        }

        $recordId = $request['id'];
        $imageUrl =  $request['image_url'];
        $type = $request['type'];

        // Find the record in the database
        $product = ProductImage::find($recordId);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found.',
            ], 404);
        }


        if ($type === 'main') {
            if ($product->image && str_contains($product->image, $imageUrl)) {
                Storage::delete($product->image);
                $product->image = null;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Main image not found.',
                ], 404);
            }
        } elseif ($type === 'additional') {
            $imageCollection = json_decode($product->image_collection, true) ?? [];
            $matchedKey = array_search($imageUrl, $imageCollection);
            if ($matchedKey !== false) {
                Storage::delete($imageCollection[$matchedKey]);
                unset($imageCollection[$matchedKey]);
                $product->image_collection = json_encode(array_values($imageCollection), JSON_UNESCAPED_SLASHES); // Use JSON_UNESCAPED_SLASHES to avoid escaping slashes
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found in collection.',
                ], 404);
            }
        }
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully. in ' . $request["type"],
        ], 202);
    }

    public function search_products_bar(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'search' => 'required|string',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $search = $request->search;
        $products = ProductVariation::where('title', 'like', '%' . $search . '%')
            ->orWhereHas('product', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->with(['product_images', 'product'])
            ->limit(6)
            ->get();



        $sub_categories = SubCategory::where('name', 'like', '%' . $search . '%')->limit(3)->get();

        return response()->json([
            'status' => true,
            'message' => 'Product found',
            'products' => ProductVariationResource::collection($products),
            'sub_categories' => SubCategoryResource::collection($sub_categories)
        ], 200);
    }


    public function addImageDynamic(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'image_id' => 'required|numeric|exists:product_images,id',
            'type' => 'required|in:main,additional',
            'image' => 'required|image|mimes:jpg,jpeg,png',
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'Message' => 'Validation errors',
                'errors' => $valid->errors(),
            ]);
        }

        $valid = $valid->validated();

        try {
            // Fetch the product image record by image_id
            $productImage = ProductImage::find($valid['image_id']);

            $productDir = 'product/' . $productImage->product_id;

            if (!Storage::disk('public')->exists($productDir)) {
                Storage::disk('public')->makeDirectory($productDir);
            }

            if ($request->type === 'main') {
                // Handle main image
                if ($productImage->image) {
                    // Delete existing main image if present
                    Storage::disk('public')->delete($productImage->image);
                }

                $mainFilename = "Product-" . time() . "-" . rand() . "." . $request->image->getClientOriginalExtension();
                $request->image->storeAs($productDir, $mainFilename, "public");
                $productImage->image = $productDir . '/' . $mainFilename;
            } elseif ($request->type === 'additional') {
                // Handle additional images
                $additionalDir = $productDir . '/additional';
                if (!Storage::disk('public')->exists($additionalDir)) {
                    Storage::disk('public')->makeDirectory($additionalDir);
                }

                $additionalFilename = "Product-" . time() . "-" . rand() . "." . $request->image->getClientOriginalExtension();
                $request->image->storeAs($additionalDir, $additionalFilename, "public");

                $imageCollection = json_decode($productImage->image_collection, true) ?? [];
                $imageCollection[] = $additionalDir . '/' . $additionalFilename;
                $productImage->image_collection = json_encode($imageCollection, JSON_UNESCAPED_SLASHES);
            }

            if (!$productImage->save()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Could not add image !',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Image added successfully!',
                'product_image' => $productImage,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
