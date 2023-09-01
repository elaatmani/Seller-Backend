<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Repositories\Interfaces\AdsRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class AdsController extends Controller
{


    private $adsRepository;

    public function __construct(AdsRepositoryInterface $adsRepository)
    {
        $this->adsRepository = $adsRepository;
    }

    public function index(Request $request)
    {

        $sortBy = $request->input('sort_by');
        $sortAds = $request->input('sort_order');
        $perPage = $request->input('per_page');

        $options = $this->get_options($request);


        $ads = $this->adsRepository->paginate($perPage, $sortBy, $sortAds, $options);
 

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'ads' => $ads,
             
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

            $ads = $this->adsRepository->create($request->all());

            DB::commit();
            return [
                'code' => 'SUCCESS',
                'data' => [
                    'ads' => $ads
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
                        'message' => 'You Dont Have Access To See Ads',
                    ],
                    405
                );
            }

            $ads = Ads::find($id);

            if (isset($ads)) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'ads' => $ads,
                        ],
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Ads Not Exist',
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
            $order = $this->adsRepository->update($id, $request->all());

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
                        'message' => 'You Dont Have Access To Delete Ads',
                    ],
                    405
                );
            }

            $ads = Ads::find($id);
            if ($ads) {

                $ads->delete();
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'ADS_DELETED',
                        'message' => 'Ads Deleted Successfully!',
                    ],
                    200
                );
            }
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_FOUND',
                    'message' => 'Ads Not Exist',
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
        $search = $request->input('search', '');
        

        $orWhere = !$search ? [] : [
            ['id', 'LIKE', "%$search%"],
            ['source', 'LIKE', "%$search%"],
            ['amount', 'LIKE', "%$search%"],
        ];


        $filtersDate = Arr::only($filters, ['created_from', 'created_to']);
        $validatedFilters = Arr::only($filters, []);

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
            ['ads_at', '>=', data_get($filtersDate, 'dropped_from', null)],
            ['ads_at', '<=', data_get($filtersDate, 'dropped_to', null)],

        ];

        $whereHas = [
            [ 'product_id', '=', data_get($filters, 'product_id', 'all'), 'items' ]
        ];

        $options = [
            'whereDate' => $whereDate,
            'where' => $toFilter,
            'orWhere' => $orWhere,
            'whereHas' => $whereHas
        ];

        return $options;
    }
}
