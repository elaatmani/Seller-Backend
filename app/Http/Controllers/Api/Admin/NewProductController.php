<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class NewProductController extends Controller
{
    public $productRepository;

    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->productRepository = $repository;
    }

    public function index(Request $request) {
        try {
            $sortBy = $request->input('sort_by');
            $sortOrder = $request->input('sort_order');
            $perPage = $request->input('per_page');
            $isAffiliate = $request->input('is_affiliate');

            $options = $this->get_options($request);
            $options['with'] = [
                    'variations'
            ];

            if(!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('affiliate-manager')){
                $options['where'][] = ['user_id','=',auth()->id()];
            }

            if($isAffiliate){
                $options['where'][] = ['product_type','=','affiliate'];
            } else {
                $options['where'][] = ['product_type','=','normal'];
            }
            $products = $this->productRepository->paginate($perPage, $sortBy, $sortOrder, $options);

            $products->getCollection()
            ->transform(function($product) {
                return $product->formatForShow();
            });

            return response()->json([
                'code' => 'SUCCESS',
                'data' => [
                    'products' => $products
                ]
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage(),
                'trace' => $th->getTrace(),
                'line' => $th->getLine()
            ], 500);
        }
    }


    public function get_options($request) {
        $filters = $request->input('filters', []);
        $search = $request->input('search', '');
        $reportedFirst = data_get($filters, 'reported_first', false);

        $orWhere = !$search ? [] : [
            ['id', 'LIKE', "%$search%"],
            ['name', 'LIKE', "%$search%"],
            ['ref', 'LIKE', "%$search%"],
        ];


        $filtersDate = Arr::only($filters, ['created_from', 'created_to', 'dropped_from', 'dropped_to']);
        $validatedFilters = Arr::only($filters, ['user_id']);

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
            ['dropped_at', '>=', data_get($filtersDate, 'dropped_from', null)],
            ['dropped_at', '<=', data_get($filtersDate, 'dropped_to', null)],

        ];

        $whereHas = [
            // [ 'product_id', '=', data_get($filters, 'product_id', 'all'), 'items' ]
        ];

        $options = [
            'whereDate' => $whereDate,
            'where' => $toFilter,
            'orWhere' => $orWhere,
            'reported_first' => $reportedFirst,
            // 'whereHas' => $whereHas
        ];

        return $options;
    }
}
