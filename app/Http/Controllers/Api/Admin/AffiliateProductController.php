<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\Affiliate\StoreProductRequest;
use App\Http\Resources\Affiliate\ProductCollectionResource;
use App\Http\Resources\Affiliate\ProductResource;
use App\Repositories\Interfaces\AffiliateRepositoryInterface;

class AffiliateProductController extends Controller
{

    protected $repository;

    public function __construct(AffiliateRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function index(Request $request) {
        $products = $this->repository->paginate(10, 'created_at', 'desc');

        $products->getCollection()->transform(fn($product) => new ProductCollectionResource($product));

        return response()->json([
            'code' => 'SUCCESS',
            'products' => $products
        ]);
    }

    public function imported(Request $request) {
        $products = auth()->user()->imported_products()->orderBy('created_at', 'desc')->paginate(10);

        $products->getCollection()->transform(fn($product) => new ProductCollectionResource($product));

        return response()->json([
            'code' => 'SUCCESS',
            'products' => $products
        ]);
    }

    public function wishlisted(Request $request) {
        $products = auth()->user()->wishlisted_products()->orderBy('created_at', 'desc')->paginate(10);

        $products->getCollection()->transform(fn($product) => new ProductCollectionResource($product));

        return response()->json([
            'code' => 'SUCCESS',
            'products' => $products
        ]);
    }

    public function store(StoreProductRequest $request)
    {

        $data = $request->validated();

        $product = $this->repository->store($request->all());
        
        return response()->json([
            'code' => 'SUCCESS',
            'product' => $product
        ]);
    }


    public function show(Request $request, $id)
    {
        $product = $this->repository->get($id);

        if(!$product) {
            return response()->json([
                'code' => 'ERROR',
                'message' => 'Product not found'
            ], 404);
        }
        
        $product = new ProductResource($product);

        return response()->json([
            'code' => 'SUCCESS',
            'product' => $product
        ]);
    }
}
