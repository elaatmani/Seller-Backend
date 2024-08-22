<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sourcing\StoreSourcingRequest;
use App\Http\Requests\Sourcing\UpdateSourcingRequest;
use App\Repositories\Interfaces\SourcingRepositoryInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\SendEmail;
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


        $options = $this->parseOptions($request);

        if(!auth()->user()->hasRole('admin')){
            $options['where'][] = ['user_id', '=', auth()->id()];
        }

        $sourcings = $this->repository->paginate($options['per_page'], $options['sort_by'], $options['sort_order'], $options);
        return response()->json([
            'code' => 'SUCCESS',
            'sourcings' => $sourcings
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
    public function update(UpdateSourcingRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $oldSourcing = $this->repository->get($id);
            $sourcing = $this->repository->update($id, $request->all());
            $statusChanged = '';
            if ($oldSourcing->quotation_status != $sourcing->quotation_status) {
                $statusChanged = 'Quotation Status: ' . $sourcing->quotation_status;
            } elseif ($oldSourcing->sourcing_status != $sourcing->sourcing_status) {
                $statusChanged = 'Sourcing Status: ' . $sourcing->sourcing_status;
            }
    
            try {
                $email = $sourcing->seller->email;
                Mail::to($email)->send(new SendEmail($sourcing, $statusChanged));
                Log::info('Email sent successfully to ' . $email);
            } catch (\Exception $e) {
                // An error occurred while sending the email
                Log::error('Failed to send email to ' . $email . '. Error: ' . $e->getMessage());
            }
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

    public function parseOptions(Request $request) {
        $filters = $request->input('filters', []);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');
        $searchByField = $request->input('searchByField', 'all');

        switch ($searchByField) {
            case 'id':
                $orWhere = [
                    ['id', '=', $search]
                ];
            break;

            default:
                $orWhere = !$search ? [] : [
                    ['id', 'LIKE', "%$search%"],
                    ['fullname', 'LIKE', "%$search%"],
                    ['phone', 'LIKE', "%$search%"],
                    ['adresse', 'LIKE', "%$search%"],
                    ['city', 'LIKE', "%$search%"],
                    ['note', 'LIKE', "%$search%"],
                ];
            break;
        }

        $filtersDate = Arr::only($filters, ['created_from', 'created_to']);
        $validatedFilters = Arr::only($filters, ['user_id', 'quotation_status', 'sourcing_status']);

        $toFilter = [];
        if(is_array($validatedFilters)){
            foreach($validatedFilters as $f => $v) {
                if($v == 'all') continue;
                $toFilter[] = [$f, '=', $v];
            }
        }

        $whereDate = [
            ['created_at', '>=', data_get($filtersDate, 'created_from', null)],
            ['created_at', '<=', data_get($filtersDate, 'created_to', null)],
        ];

        $options = [
            'whereDate' => $whereDate,
            'where' => $toFilter,
            'orWhere' => $orWhere,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'per_page' => $perPage
        ];

        return $options;
    }
}
