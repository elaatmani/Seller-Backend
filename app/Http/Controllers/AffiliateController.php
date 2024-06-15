<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserProduct;

class AffiliateController extends Controller
{
    public function storeImport(Request $request, UserProduct $userProduct){
        $product = $userProduct->firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'type' => 'import'
        ]);

        if ($product->wasRecentlyCreated) {
            $message = 'Product imported successfully';
            $code="SUCCESS";
        } else {
            $message = 'Product already exists in import list';
            $code="ALREADY_IMPORTED";
        }

        return response()->json([
            'code' => $code,
            'message' => $message
        ]);
    }

    public function deleteImport($id, UserProduct $userProduct){
        $userProduct->where('user_id', auth()->id())
                     ->where('id', $id)
                     ->where('type', 'import')
                     ->delete();

        return response()->json([
            'code' => 'SUCCESS',
            'message' => 'Product removed from import list'
        ]);
    }

    public function storeWish(Request $request, UserProduct $userProduct){
        $product = $userProduct->firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'type' => 'wish'
        ]);

        if ($product->wasRecentlyCreated) {
            $message = 'Product added to wish list';
            $code="SUCCESS";

        } else {
            $message = 'Product already exists in wish list';
            $code="ALREADY_IMPORTED";

        }

        return response()->json([
            'code' => $code,
            'message' => $message
        ]);
    }

    public function deleteWish($id, UserProduct $userProduct){
        $userProduct->where('user_id', auth()->id())
                     ->where('id', $id)
                     ->where('type', 'wish')
                     ->delete();

        return response()->json([
            'code' => 'SUCCESS',
            'message' => 'Product removed from wish list'
        ]);
    }
}