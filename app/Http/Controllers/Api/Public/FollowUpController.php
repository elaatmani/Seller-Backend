<?php

namespace App\Http\Controllers\Api\Public;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\StatisticsService;
use Carbon\Carbon;

class FollowUpController extends Controller
{

    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }


    public function statistics() {
        $orders = $this->orderRepository->all();

        $statistics = StatisticsService::followup($orders);

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

        $followUpCondition = [['delivery', '=', 'annuler'],['confirmation', '=', 'confirmer']];

        $orWhere = !$search ? [] : [
            ['id', 'LIKE', "%$search%"],
            ['fullname', 'LIKE', "%$search%"],
            ['phone', 'LIKE', "%$search%"],
            ['adresse', 'LIKE', "%$search%"],
            ['city', 'LIKE', "%$search%"],
            ['note', 'LIKE', "%$search%"],
        ];

        $toFilter = [];

        if(is_array($filters)){
            foreach($filters as $f => $v) {
                if($v == 'all') continue;
                if($f == 'created_at') {

                    $toFilter[] = [$f, 'like', "%$v%"];
                } else {
                    $toFilter[] = [$f, '=', $v];
                }
            }
        }

        $where = [
            ...$followUpCondition,
            ...$toFilter
        ];

        $options = [
            'where' => $where,
            'orWhere' => $orWhere
        ];


        $orders = $this->orderRepository->paginate($perPage, $sortBy, $sortOrder, $options);
        $statistics = $this->orderRepository->followUpStatistics(1);

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'filters' => $toFilter,
                'statistics' => $statistics,
                'orders' => $orders,
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
