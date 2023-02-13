<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehiclesController extends Controller
{
    public function indexBuses()
    {
        $query = DB::table('pojazdy')
            ->select('ID', 'tabor', 'producer', 'model')
            ->where('type', '=','A')
            ->orderBy('tabor')
            ->get();
        return response()->json($query);
    }

    public function indexTrams()
    {
        $query = DB::table('pojazdy')
            ->select('ID', 'tabor', 'producer', 'model')
            ->where('type', '=','T')
            ->orderBy('tabor')
            ->get();
        return response()->json($query);
    }

    public function indexOthers()
    {
        $query = DB::table('pojazdy')
            ->select('ID', 'tabor', 'producer', 'model')
            ->where('type', '=', 'Z')
            ->orderBy('tabor')
            ->get();
        return response()->json($query);
    }

    public function indexAllVehicles() {
        $query = DB::table('pojazdy')
            ->select('ID', 'tabor', 'producer', 'model')
            ->get();

        return response($query);
    }

    public function store(Request $request)
    {
        $query = DB::table('pojazdy')->insert([
            'tabor' => $request->input('tabor'),
            'producer' => $request->input('producer'),
            'model' => $request->input('model'),
            'info' => '',
            'type' => $request->input('type'),
        ]);

        return response()->json($query, 201);
    }

    public function delete($id)
    {
        DB::table('pojazdy')->where('ID', $id)->delete();
    }

    public function indexDepots() {
        /*
        B - bieńczyce
        D - wola duchacka
        P - płaszów
        M - mobilis
        ? - MAN Lion's Intercity

        H - nowa huta
        R - podgórze
        */
        $json = array(
                'bienczyce' => [],
                'wolaDuchacka' => [],
                'plaszow' => [],
                'mobilis' => [],
                'nowaHuta' => [],
                'podgorze' => [],
                'sums' => [
                    'bienczyce' => 0,
                    'wolaDuchacka' => 0,
                    'plaszow' => 0,
                    'mobilis' => 0,
                    'nowaHuta' => 0,
                    'podgorze' => 0,
                ],
        );

        $queryBModels = DB::table('pojazdy')
            ->select('model')
            ->where([
                ['type', '=', 'A'],
                ['tabor', 'like', 'B%']])
            ->groupBy('model')
            ->get();

        $queryDModels = DB::table('pojazdy')
            ->select('model')
            ->where([
                ['type', '=', 'A'],
                ['tabor', 'like', 'D%']])
            ->groupBy('model')
            ->get();

        $queryPModels = DB::table('pojazdy')
            ->select('model')
            ->where([
                ['type', '=', 'A'],
                ['tabor', 'like', 'P%']])
            ->groupBy('model')
            ->get();

        $queryMModels = DB::table('pojazdy')
            ->select('model')
            ->where([
                ['type', '=', 'A'],
                ['tabor', 'like', 'M%']])
            ->groupBy('model')
            ->get();

        $queryHModels = DB::table('pojazdy')
            ->select('model')
            ->where([
                ['type', '=', 'T'],
                ['tabor', 'like', 'H%']])
            ->groupBy('model')
            ->get();

        $queryRModels = DB::table('pojazdy')
            ->select('model')
            ->where([
                ['type', '=', 'T'],
                ['tabor', 'like', 'R%']])
            ->groupBy('model')
            ->get();

        foreach ($queryBModels as $queryBModel) {
            $query = DB::table('pojazdy')
                ->select('model')
                ->where([
                    ['model', '=', $queryBModel->model],
                    ['type', '=', 'A'],
                    ['tabor', 'like', 'B%'],
                ])
                ->count();
            $json['bienczyce'][$queryBModel->model] = $query;
            $json['sums']['bienczyce'] += $query;
        }

        foreach ($queryDModels as $queryDModel) {
            $query = DB::table('pojazdy')
                ->select('model')
                ->where([
                    ['model', '=', $queryDModel->model],
                    ['type', '=', 'A'],
                    ['tabor', 'like', 'D%'],
                ])
                ->count();
            $json['wolaDuchacka'][$queryDModel->model] = $query;
            $json['sums']['wolaDuchacka'] += $query;
        }

        foreach ($queryPModels as $queryPModel) {
            $query = DB::table('pojazdy')
                ->select('model')
                ->where([
                    ['model', '=', $queryPModel->model],
                    ['type', '=', 'A'],
                    ['tabor', 'like', 'P%'],
                ])
                ->count();
            $json['plaszow'][$queryPModel->model] = $query;
            $json['sums']['plaszow'] += $query;
        }

        foreach ($queryMModels as $queryMModel) {
            $query = DB::table('pojazdy')
                ->select('model')
                ->where([
                    ['model', '=', $queryMModel->model],
                    ['type', '=', 'A'],
                    ['tabor', 'like', 'M%'],
                ])
                ->count();
            $json['mobilis'][$queryMModel->model] = $query;
            $json['sums']['mobilis'] += $query;
        }

        foreach ($queryHModels as $queryHModel) {
            $query = DB::table('pojazdy')
                ->select('model')
                ->where([
                    ['model', '=', $queryHModel->model],
                    ['type', '=', 'T'],
                    ['tabor', 'like', 'H%'],
                ])
                ->count();;
            $json['nowaHuta'][$queryHModel->model] = $query;
            $json['sums']['nowaHuta'] += $query;
        }

        foreach ($queryRModels as $queryRModel) {
            $query = DB::table('pojazdy')
                ->select('model')
                ->where([
                    ['model', '=', $queryRModel->model],
                    ['type', '=', 'T'],
                    ['tabor', 'like', 'R%'],
                ])
                ->count();
            $json['podgorze'][$queryRModel->model] = $query;
            $json['sums']['podgorze'] += $query;
        }

        return response()->json($json);
    }
}
