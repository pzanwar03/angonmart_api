<?php

namespace Marvel\Http\Controllers;

use Illuminate\Http\Request;
use Marvel\Database\Models\BdDistrict;
use Marvel\Database\Models\BdDivision;
use Marvel\Database\Models\BdThana;

class LocationController extends CoreController
{
    public function divisions()
    {
        return BdDivision::query()
            ->orderBy('name')
            ->get(['id', 'name', 'bn_name']);
    }

    public function districts(Request $request)
    {
        $query = BdDistrict::query()->orderBy('name');

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        return $query->get(['id', 'division_id', 'name', 'bn_name']);
    }

    public function thanas(Request $request)
    {
        $query = BdThana::query()->orderBy('name');

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        return $query->get(['id', 'district_id', 'name', 'bn_name']);
    }
}
