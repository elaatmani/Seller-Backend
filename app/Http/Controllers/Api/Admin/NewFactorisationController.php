<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\Factorisation;
use App\Models\FactorisationFee;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Interfaces\FactorisationRepositoryInterface;

class NewFactorisationController extends Controller
{


    private $factorisationRepository;

    public function __construct(FactorisationRepositoryInterface $factorisationRepository)
    {
        $this->factorisationRepository = $factorisationRepository;
    }

    public function index(Request $request)
    {

        $sortBy = $request->input('sort_by');
        $sortAds = $request->input('sort_order');
        $perPage = $request->input('per_page');

        $options = $this->get_options($request);

       $options['with'] = 'fees';
       if(!auth()->user()->hasRole('admin')) {
           $options['where'][] = ['user_id', '=', auth()->id()];
           $options['where'][] = ['close','=',true];
       }

        $factorisation = $this->factorisationRepository->paginate($perPage, $sortBy, $sortAds, $options);


        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'factorisation' => $factorisation,


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

            $factorisation = $this->factorisationRepository->create($request->all());

            DB::commit();
            return [
                'code' => 'SUCCESS',
                'data' => [
                    'factorisation' => $factorisation
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
            if (!$request->user()->can('view_ads')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Factorisation',
                    ],
                    405
                );
            }

            $factorisation = Factorisation::find($id);

            if (isset($factorisation)) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'factorisation' => $factorisation,
                        ],
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Factorisation Not Exist',
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
            $order = $this->factorisationRepository->update($id, $request->all());

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
            if (!$request->user()->can('delete_ads')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Delete Factorisation',
                    ],
                    405
                );
            }

            $factorisation = Factorisation::find($id);
            if ($factorisation) {

                $factorisation->delete();
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'ADS_DELETED',
                        'message' => 'Factorisation Deleted Successfully!',
                    ],
                    200
                );
            }
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_FOUND',
                    'message' => 'Factorisation Not Exist',
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



    public function get_sum(){
        $totalPrice = Factorisation::where([
            ['user_id', auth()->id()],
            ['close',1],
            ['paid',0],
            ['type', 'seller']
        ])->sum('price');
        $ids = Factorisation::where([
            ['user_id', auth()->id()],
            ['close',1],
            ['paid',0],
        ])->get()->pluck('id')->toArray();

        $totalFees = FactorisationFee::whereIn('factorisation_id', $ids)->sum('feeprice');
        return response()->json([
            "code"=>"SUCCESS",
            "totalPrice"=>$totalPrice - $totalFees
        ]);
    }
    public function get_options($request) {
        $filters = $request->input('filters', []);
        $search = $request->input('search', '');


        $orWhere = !$search ? [] : [
            ['id', 'LIKE', "%$search%"],
            ['factorisation_id', 'LIKE', "%$search%"],
            // ['source', 'LIKE', "%$search%"],
            // ['amount', 'LIKE', "%$search%"],
        ];


        // $filtersDate = Arr::only($filters, ['created_from', 'created_to','ads_from','ads_to']);
        $validatedFilters = Arr::only($filters, ['user_id','close','paid']);

        $toFilter = [];
        if(is_array($validatedFilters)){
            foreach($validatedFilters as $f => $v) {
                if($v == 'all') continue;
                $toFilter[] = [$f, '=', $v];
            }
        }

        $whereDate = [
            // ['created_at', '>=', data_get($filtersDate, 'created_from', null)],
            // ['created_at', '<=', data_get($filtersDate, 'created_to', null)],
            // ['ads_at', '>=', data_get($filtersDate, 'ads_from', null)],
            // ['ads_at', '<=', data_get($filtersDate, 'ads_to', null)],
        ];


        $options = [
            'whereDate' => $whereDate,
            'where' => $toFilter,
            'orWhere' => $orWhere,
        ];

        return $options;
    }
}
