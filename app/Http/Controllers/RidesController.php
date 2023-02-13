<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RidesController extends Controller
{
    public function index(Request $request)
    {
        $tabname = "data_".$request->user()->name;
        $query = DB::table($tabname)
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();
        return response()->json($query);
    }

    public function store(Request $request)
    {
        $tabname = "data_".$request->user()->name;
        $query = DB::table($tabname)->insert([
            'tabor' => $request->input('tabor'),
            'line' => $request->input('line'),
            'direction' => $request->input('direction'),
            'first' => $request->input('first'),
            'last' => $request->input('last'),
        ]);

        return response()->json($query, 201);
    }

    public function update(Request $request, $id)
    {
        $tabname = "data_".$request->user()->name;
        $query = DB::table($tabname)
            ->where('id', $id)
            ->update([
                'tabor' => $request->input('tabor'),
                'line' => $request->input('line'),
                'direction' => $request->input('direction'),
                'first' => $request->input('first'),
                'last' => $request->input('last'),
            ]);

        return response()->json($query);
    }

    public function delete(Request $request, $id)
    {
        $tabname = "data_".$request->user()->name;
        DB::table($tabname)->where('id', $id)->delete();

        return response(null, 204);
    }

    public function search(Request $request)
    {
        $date = date('Y-m-d');
        $tabname = "data_".$request->user()->name;
        $search = $request->query('phrase', '');
        $column = $request->query('column', 'tabor');
        $sortColumn = $request->query('sort', 'created_at');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $start = $request->query('start', 0);
        $length = $request->query('length', 10);
        $order = $request->query('order', 'desc');

        $startCreatedAt = $startDate ? $startDate." 00:00:00" : $date." 00:00:00";
        $endCreatedAt = $endDate ? $endDate." 23:59:59" : $date." 23:59:59";

        $query = DB::table($tabname);

        if (!empty($search)) {
            $query->where($column, 'like', $search."%")
                    ->where('created_at','>=', $startCreatedAt)
                    ->where('created_at','<=', $endCreatedAt);
        } else {
            $query->where('created_at','>=', $startCreatedAt)
                ->where('created_at','<=', $endCreatedAt);
        }

        $recordsTotal = $query->count();

        $query->orderBy($sortColumn, $order)
                ->take($length)
                ->skip($start);

        $json = array(
            'startCreatedAt' => $startCreatedAt,
            'endCreatedAt' => $endCreatedAt,
            'recordsTotal' => $recordsTotal,
            'data' => [],
        );

        $results = $query->get();

        foreach ($results as $result) {
            $json['data'][] = $result;
        }

//        $results = DB::table($tabname)->where('tabor', '=', $phrase)->take(10)->skip();



        return response()->json($json);
    }

    public function ranking()
    {
        $users_names = DB::table('users')->select('name')->get();
        $sum = [];
        $year = date('Y');

        for ($i = 0; $i < sizeof($users_names); $i++) {
            $name = $users_names[$i]->name;
            $tabname = 'data_'.$name;

            $result = DB::table($tabname)
                ->whereYear('created_at', '=', $year)
                ->count('tabor');

            $resultOtherYears = DB::table($tabname)
                ->whereYear('created_at', '<', $year)
                ->count('tabor');

            $resultAllYears = DB::table($tabname)
                ->count('tabor');

            $sum[$i] = [
                'name' => $name,
                'quantity' => $result,
                'quantity_in_other_years' => $resultOtherYears,
                'quantity_in_all_years' => $resultAllYears,
            ];
        }

        return response()->json($sum);
    }

    public function statement(Request $request)
    {
        $tabname = "data_".$request->user()->name;
        $results = [];
        $year = date('Y');

        for ($i = 0; $i < 12; $i++) {
            $month = '';
            $query =  DB::table($tabname)
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $i+1)
                ->count('tabor');

            switch ($i+1) {
                case 1:
                    $month = 'styczeń';
                    break;
                case 2:
                    $month = 'luty';
                    break;
                case 3:
                    $month = 'marzec';
                    break;
                case 4:
                    $month = 'kwiecień';
                    break;
                case 5:
                    $month = 'maj';
                    break;
                case 6:
                    $month = 'czerwiec';
                    break;
                case 7:
                    $month = 'lipiec';
                    break;
                case 8:
                    $month = 'sierpień';
                    break;
                case 9:
                    $month = 'wrzesień';
                    break;
                case 10:
                    $month = 'październik';
                    break;
                case 11:
                    $month = 'listopad';
                    break;
                case 12:
                    $month = 'grudzień';
                    break;
            }

            $results[$i] = [
                'month' => $month,
                'quantity' => $query,
                ];
        }

        return response()->json($results);
    }

    public function autocomplete() {
        $lines = DB::table('linie')->pluck('nazwa');
        $directions = DB::table('kierunki')->pluck('nazwa');
        $stops = DB::table('przystanki')->pluck('nazwa');
        $json = array(
            'lines' => $lines,
            'directions' => $directions,
            'stops' => $stops,
        );

        return response()->json($json);
    }
}
