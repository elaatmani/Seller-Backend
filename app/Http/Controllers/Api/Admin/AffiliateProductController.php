<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Affiliate\ProductResource;
use App\Http\Resources\Affiliate\ProductEditResource;
use App\Http\Resources\Affiliate\ProductCollectionResource;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Http\Requests\Product\Affiliate\StoreProductRequest;
use App\Http\Requests\Product\Affiliate\UpdateProductRequest;
use App\Repositories\Interfaces\AffiliateRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AffiliateProductController extends Controller
{

    protected $repository;
    protected $productRepository;

    public function __construct(AffiliateRepositoryInterface $repository, ProductRepositoryInterface $productRepository) {
        $this->repository = $repository;
        $this->productRepository = $productRepository;
    }

    public function index(Request $request) {
        $products = $this->repository->paginate(100, 'created_at', 'desc');

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

    public function update(UpdateProductRequest $request, $id)
    {

        $data = $request->validated();

        $product = $this->repository->update($id, $request->all());
        
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

    public function edit(Request $request, $id)
    {
        $product = $this->repository->get($id);

        if(!$product) {
            return response()->json([
                'code' => 'ERROR',
                'message' => 'Product not found'
            ], 404);
        }
        
        $product = new ProductEditResource($product);

        return response()->json([
            'code' => 'SUCCESS',
            'product' => $product
        ]);
    }

    public function details(Request $request, $id)
    {
        $product = $this->repository->details($id);

        if(!$product) {
            return response()->json([
                'code' => 'ERROR',
                'message' => 'Product not found'
            ], 404);
        }
        
        // $product = new ProductResource($product);
        // $count_orders = Order::whereRelation('items', 'product_id', $product->id)->count();
        // $sellers = $product->sellers;

        return response()->json([
            'code' => 'SUCCESS',
            'product' => $product
        ]);
    }

    public function getOffers(Request $request, $id)
    {
        $product = $this->repository->get($id);

        if(!$product) {
            return response()->json([
                'code' => 'ERROR',
                'message' => 'Product not found'
            ], 404);
        }
        
        $offers = $product->offers()->where('user_id', auth()->id())->orWhereNull('user_id')->get();
        

        return response()->json([
            'code' => 'SUCCESS',
            'offers' => $offers
        ]);
    }

    public function setOffers(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $product = $this->repository->get($id);

            if(!$product) {
                return response()->json([
                    'code' => 'ERROR',
                    'message' => 'Product not found'
                ], 404);
            }
            
            $offers = $request->input('offers', []);

            $offers = $this->productRepository->updateProductOffers($id, $offers);

            DB::commit();
            return response()->json([
                'code' => 'SUCCESS',
                'offers' => $offers
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
