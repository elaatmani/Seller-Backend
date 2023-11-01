<?php

namespace App\Http\Controllers\Api\Export;

use App\Exports\FactorisationExport;
use App\Http\Controllers\Controller;
use App\Models\Factorisation;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class FactorisationExportController extends Controller
{
    public function index(Request $request, $id) {
        $factorisation = Factorisation::where('factorisation_id', $id)->first();
        if(!$factorisation) abort(404);

        return Excel::download(new FactorisationExport($factorisation), 'VLDO-' . $factorisation->factorisation_id . '.xlsx');
    }

    public function map($factorisation) {

    }
}
