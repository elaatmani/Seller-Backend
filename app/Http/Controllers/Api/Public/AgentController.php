<?php

namespace App\Http\Controllers\Api\Public;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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


    public function index(Request $request) {

        $sortBy = $request->input('sort_by');
        $sortOrder = $request->input('sort_order');
        $perPage = $request->input('per_page');
        $confirmation = $request->input('confirmation');

        $where = [
            ['confirmation', $confirmation == 'confirmer' ? '=' : '!=', 'confirmer'],
            ['agente_id', '=', auth()->id(), ]
        ];

        $orders = $this->orderRepository->agentOrdersPaginate($where, $perPage, $sortBy, $sortOrder);

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'orders' => $orders
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
