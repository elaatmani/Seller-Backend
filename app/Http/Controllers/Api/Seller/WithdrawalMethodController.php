<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalMethod;
use Illuminate\Http\Request;

class WithdrawalMethodController extends Controller
{
    public function index(Request $request) {

        $wms = WithdrawalMethod::where('seller_id', auth()->id())->get();

        return response()->json([
            'code' => 'SUCCESS',
            'withdrawal_methods' => $wms
        ]);
    }

    public function update(Request $request, $id) {
        $wm = WithdrawalMethod::where([
            ['id', '=', $id],
            ['seller_id', '=', auth()->id()]
        ])->first();

        if(!$wm) return response()->json([
            'code' => 'NOT_FOUND'
        ], 404);

        if($request->is_preferred) {
            WithdrawalMethod::where('seller_id', auth()->id())->update(['is_preferred' => false]);
        }

        $methods = WithdrawalMethod::where('seller_id', auth()->id())->count();

        $isPreferred = $methods == 1 ? true : $request->is_preferred;

        switch ($request->type) {
            case 'bank_transfer':
                $metadata = [
                    'rib' => $request->metadata['rib'],
                    'account_holder_name' => $request->metadata['account_holder_name'],
                    'bank_name' => $request->metadata['bank_name'],
                ];

                $wm->metadata = $metadata;
                $wm->is_preferred = $isPreferred;
            break;

            case 'binance':
                $metadata = [
                    'binance_id' => $request->metadata['binance_id'],
                ];

                $wm->metadata = $metadata;
                $wm->is_preferred = $isPreferred;
            break;
        }

        $wm->save();

        $wms = WithdrawalMethod::where('seller_id', auth()->id())->get();

        return response()->json([
            'code' => 'SUCCESS',
            'withdrawal_methods' => $wms
        ]);
    }

    public function setup(Request $request) {
        $methods = WithdrawalMethod::where('seller_id', auth()->id())->count();

        if($request->is_preferred && $methods > 0) {
            WithdrawalMethod::where('seller_id', auth()->id())->update(['is_preferred' => false]);
        }

        $isPreferred = $methods == 0 ? true : $request->is_preferred;

        $data = [
            'type' => $request->type,
            'metadata' => $request->metadata,
            'is_preferred' => $isPreferred,
            'seller_id' => auth()->id()
        ];

        WithdrawalMethod::create($data);

        $wms = WithdrawalMethod::where('seller_id', auth()->id())->get();

        return response()->json([
            'code' => 'SUCCESS',
            'withdrawal_methods' => $wms
        ]);
    }

    public function destroy(Request $request, $id) {

    }


}
