<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\UserProduct;

class AffiliateController extends Controller
{
    public function import(Request $request, UserProduct $userProduct)
    {
        $product = $userProduct->firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'type' => 'import'
        ]);

        if ($product->wasRecentlyCreated) {
            $message = 'Product imported successfully';
            $code = "SUCCESS";
        } else {
            $message = 'Product already exists in import list';
            $code = "ALREADY_IMPORTED";
        }

        return response()->json([
            'code' => $code,
            'message' => $message
        ]);
    }

    public function unimport(Request $request, UserProduct $userProduct)
    {
        $id = $request->product_id;

        Order::where('user_id', auth()->id())->whereRelation('items', 'product_id', $id);

        return response()->json([
            'code' => 'ORDERS_EXISTS',
            'message' => 'Product cannot be removed from import list because it has orders'
        ]);

        $userProduct->where('user_id', auth()->id())
            ->where('product_id', $id)
            ->where('type', 'import')
            ->delete();

        return response()->json([
            'code' => 'SUCCESS',
            'message' => 'Product removed from import list'
        ]);
    }

    public function wishlist(Request $request, UserProduct $userProduct)
    {
        $product = $userProduct->firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'type' => 'wishlist'
        ]);

        if ($product->wasRecentlyCreated) {
            $message = 'Product added to wish list';
            $code = "SUCCESS";
        } else {
            $message = 'Product already exists in wish list';
            $code = "ALREADY_WISHLISTED";
        }

        return response()->json([
            'code' => $code,
            'message' => $message
        ]);
    }

    public function unwishlist(Request $request, UserProduct $userProduct)
    {
        $id = $request->product_id;

        $userProduct->where('user_id', auth()->id())
            ->where('product_id', $id)
            ->where('type', 'wishlist')
            ->delete();

        return response()->json([
            'code' => 'SUCCESS',
            'message' => 'Product removed from wish list'
        ]);
    }
}
