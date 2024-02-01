<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sourcing\StoreSourcingRequest;
use App\Repositories\Interfaces\SourcingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SourcingController extends Controller
{

    public $repository;

    public function __construct(SourcingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $options = [];

        if(!auth()->user()->hasRole('admin')){
            $options['where'][] = ['user_id', '=', auth()->id()];
        }

        return response()->json([
            'code' => 'SUCCESS',
            'sourcings' => $this->repository->paginate(10, 'created_at', 'desc', $options)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSourcingRequest $request)
    {
        try {
            $sourcing = $this->repository->create($request->all());
            return response()->json([
                'code' => 'SUCCESS',
                'sourcing' => $sourcing
            ]);
        } catch (\Throwable $th) {
            return $th->getMessage();
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
        $sourcing = $this->repository->get($id);

        if(!$sourcing) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'sourcing' => $sourcing
            ], 200);
        }
        $sourcing->load('seller');

        return response()->json([
            'code' => !$sourcing ? 'NOT_FOUND' : 'SUCCESS',
            'sourcing' => $sourcing
        ], 200);
    }


    public function history($id)
    {
        $sourcing = $this->repository->get($id);

        if(!$sourcing) {
            return response()->json([
                'code' => 'NOT_FOUND',
            ], 200);
        }

        return response()->json([
            'code' => 'SUCCESS',
            'history' => $sourcing?->history
        ], 200);
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
    public function update(StoreSourcingRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $sourcing = $this->repository->update($id, $request->all());
            DB::commit();

            return response()->json([
                'code' => 'SUCCESS',
                'sourcing' => $sourcing
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ]);
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
        //
    }
}
