<?php

namespace App\Http\Controllers\Api\Admin;


use App\Http\Controllers\Controller;
use App\Models\Credit;
use App\Repositories\Interfaces\CreditRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class CreditSellerController extends Controller
{
    private $creditRepository;

    public function __construct(CreditRepositoryInterface $creditRepository)
    {
        $this->creditRepository = $creditRepository;
    }

    public function index(Request $request)
    {

        $sortBy = $request->input('sort_by');
        $sortCredit = $request->input('sort_order');
        $perPage = $request->input('per_page');

        $options = $this->get_options($request);


        $Credit = $this->creditRepository->paginate($perPage, $sortBy, $sortCredit, $options);
 

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'Credit' => $Credit,
             
            ]
        ]);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $Credit = $this->creditRepository->create($request->all());

            DB::commit();
            return [
                'code' => 'SUCCESS',
                'data' => [
                    'Credit' => $Credit
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            if (!$request->user()->can('view_Credit')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Credit',
                    ],
                    405
                );
            }

            $Credit = Credit::find($id);

            if (isset($Credit)) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'Credit' => $Credit,
                        ],
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Credit Not Exist',
                    ],
                    404
                );
            }
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage()
                ],
                500
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $order = $this->creditRepository->update($id, $request->all());

            return [
                'code' => 'SUCCESS',
                'data' => [
                    'order' => $order
                ]
            ];

        } catch (\Throwable $th) {


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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {

            $Credit = Credit::find($id);
            if ($Credit) {

                $Credit->delete();
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'Credit_DELETED',
                        'message' => 'Credit Deleted Successfully!',
                    ],
                    200
                );
            }
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_FOUND',
                    'message' => 'Credit Not Exist',
                ],
                404
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                    'code' => 'SERVER_ERROR'
                ],
                500
            );
        }
    }




    public function get_options($request) {
        $filters = $request->input('filters', []);        

        $filtersDate = Arr::only($filters, ['created_from', 'created_to','credit_from','credit_to']);
        $validatedFilters = Arr::only($filters, ['seller_id']);

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
            ['credit_at', '>=', data_get($filtersDate, 'credit_from', null)],
            ['credit_at', '<=', data_get($filtersDate, 'credit_to', null)],
        ];


        $options = [
            'whereDate' => $whereDate,
            'where' => $toFilter,
        ];

        return $options;
    }
}
