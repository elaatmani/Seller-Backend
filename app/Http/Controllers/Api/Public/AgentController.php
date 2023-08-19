<?php

namespace App\Http\Controllers\Api\Public;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StatisticsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class AgentController extends Controller
{
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function statistics() {
        $orders = $this->orderRepository->all()->where('agente_id', auth()->id());

        $statistics = StatisticsService::agent($orders);

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
        $confirmation = $request->input('confirmation');

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
            // check if only confirmed orders or else
            ['confirmation', $confirmation == 'confirmer' ? '=' : '!=', 'confirmer'],
            ['agente_id', '=', auth()->id(), ],
            ...$toFilter
        ];

        $options = [
            'where' => $where,
            'orWhere' => $orWhere
        ];


        $orders = $this->orderRepository->paginate($perPage, $sortBy, $sortOrder, $options);
        $statistics = $this->orderRepository->agentStatistics(auth()->id());

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'orders' => $orders,
                'statistics' => $statistics
            ]
        ]);
    }

    public function counts() {

        $countConfirmed = $this->orderRepository->whereCount([['confirmation', '=', 'confirmer'], ['agente_id', '=', auth()->id()]]);
        $countNotConfirmed = $this->orderRepository->whereCount([['confirmation', '!=', 'confirmer'], ['agente_id', '=', auth()->id()]]);
        $countAvailable = $this->orderRepository->whereCount([['confirmation', '=', null], ['agente_id', '=', null]]);

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'confirmed' => $countConfirmed,
                'not_confirmed' => $countNotConfirmed,
                'available' => $countAvailable,
            ]
        ]);
    }


    public function update(UpdateOrderRequest $request, $id) {

        try {
            $order = $this->orderRepository->update($id, $request->all());

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
