<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\Affiliate\StoreProductRequest;
use App\Repositories\Interfaces\AffiliateRepositoryInterface;

class AffiliateProductController extends Controller
{

    protected $repository;

    public function __construct(AffiliateRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    public function store(StoreProductRequest $request)
    {
        // $product = Product::find(27)->makeHidden(['seller_user', 'deliveries', 'offers']);

        // return response()->json([
        //     'code' => 'SUCCESS',
        //     'data' => [
        //         'product' => $product,
        //         'media' => $product->getMedia()
        //     ]
        // ]);

        $data = $request->validated();

        $product = $this->repository->store($request->all());
        
        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'product' => $product
            ]
        ]);
    }
}
