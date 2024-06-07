<?php

namespace App\Http\Controllers\Api\Admin;

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
        $data = $request->validated();
        
        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'product' => 'true'
            ]
        ]);
    }
}
