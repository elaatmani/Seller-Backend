<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StatisticsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class AdminController extends Controller
{

    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function statistics(Request $request) {


        $statistics = StatisticsService::admin($request);

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'statistics' => $statistics
            ]
        ]);
    }

    public function index(Request $request) {

        $sortBy = $request->input('sort_by');
        $sortOrder = $request->input('sort_order');
        $perPage = $request->input('per_page');
        $filters = $request->input('filters');
        $search = $request->input('search');
        // $confirmation = $request->input('confirmation');

        $orWhere = !$search ? [] : [
            ['id', 'LIKE', "%$search%"],
            ['fullname', 'LIKE', "%$search%"],
            ['phone', 'LIKE', "%$search%"],
            ['adresse', 'LIKE', "%$search%"],
            ['city', 'LIKE', "%$search%"],
            ['note', 'LIKE', "%$search%"],
        ];

        $toFilter = [];

        $filtersDate = Arr::only($filters, ['created_from', 'created_to', 'dropped_from', 'dropped_to']);
        $filters = Arr::only($filters, ['confirmation', 'delivery', 'affectation', 'agente_id', 'upsell']);
        if(is_array($filters)){
            foreach($filters as $f => $v) {
                if($v == 'all') continue;
                $toFilter[] = [$f, $v];
            }
        }

        $where = [
            ...$toFilter
        ];

        $whereDate = [
        ];

        if(!!$filtersDate['created_from']) $whereDate[] = ['created_at', '>=', Carbon::make($filtersDate['created_from'])->toDate()];
        if(!!$filtersDate['created_to']) $whereDate[] = ['created_at', '<=', Carbon::make($filtersDate['created_to'])->toDate()];
        if(!!$filtersDate['dropped_from']) $whereDate[] = ['dropped_at', '>=', Carbon::make($filtersDate['dropped_from'])->toDate()];
        if(!!$filtersDate['dropped_to']) $whereDate[] = ['dropped_at', '<=', Carbon::make($filtersDate['dropped_to'])->toDate()];



        $orders = $this->orderRepository->paginate($where, $orWhere, $perPage, $sortBy, $sortOrder, $whereDate);
        $statistics = $this->orderRepository->adminStatistics();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'orders' => $orders,
                'statistics' => $statistics
            ]
        ]);
    }


    public function update(UpdateOrderRequest $request, $id) {

        try {
            DB::beginTransaction();

            $order = $this->orderRepository->update($id, $request->all());

            DB::commit();
            return [
                'code' => 'SUCCESS',
                'data' => [
                    'order' => $order
                ]
            ];

        } catch (\Throwable $th) {

            // rollback transaction on error
            DB::rollBack();

            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage(),
                ],
                500
            );
        }
    }
}
