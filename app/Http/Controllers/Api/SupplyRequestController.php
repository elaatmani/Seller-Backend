<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplyRequest\CreateRequest;
use App\Http\Requests\SupplyRequest\UpdateRequest;
use App\Repositories\Interfaces\SupplyRequestRepositoryInterface;
use Illuminate\Http\Request;

class SupplyRequestController extends Controller
{

    protected $supplyRequestRepository;

    public function __construct(SupplyRequestRepositoryInterface $supplyRequestRepository)
    {
        $this->supplyRequestRepository = $supplyRequestRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $options = [
            'with' => ['product_variation', 'product'],
            'where' => [
                ['seller_id', '=', auth()->id()]
            ]
        ];

        if(auth()->user()->hasRole('admin')) {
            $options['where'] = [];
            $options['with'][] = 'seller';
        }

        $supplyRequest = $this->supplyRequestRepository->paginate(10, 'created_at', 'desc', $options);

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'supply_requests' => $supplyRequest
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateRequest $request)
    {
        try {
            $supply = $this->supplyRequestRepository->create($request->all());

            $supply->load(['product', 'product_variation']);

            return response()->json([
                'code' => 'SUCCESS',
                'supply_request' => $supply
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage(),
                'trace' => $th->getLine(),
                'file'=> $th->getFile()
            ], 500);
            //throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        try {
            $supply = $this->supplyRequestRepository->update($id, $request->all());

            $supply->load(['product', 'product_variation']);

            if(auth()->user()->hasRole('admin')) {
                $supply->load('seller');
            }

            return response()->json([
                'code' => 'SUCCESS',
                'supply_request' => $supply
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage(),
                'trace' => $th->getLine(),
                'file'=> $th->getFile()
            ], 500);
            //throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $this->supplyRequestRepository->delete($id);

            return response()->json([
                'code' => 'SUCCESS'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
