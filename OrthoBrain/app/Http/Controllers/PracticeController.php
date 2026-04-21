<?php

namespace App\Http\Controllers;

use App\Models\Practice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    /**
     * GET /practice-search?q=...
     *
     * Returns up to 20 active practices whose name matches the query,
     * with the full address chain so the registration form can auto-fill
     * practice info + address info when the doctor picks a suggestion.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        // Require minimum query length to avoid shipping the entire table
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $practices = Practice::with(['zipcode', 'city', 'state', 'country'])
            ->where('status', 'ACTIVE')
            ->where('name', 'LIKE', '%' . $q . '%')
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json(
            $practices->map(function (Practice $p) {
                $cityName = $p->city?->name;
                return [
                    'id'                 => $p->id,
                    'name'               => $p->name,
                    'label'              => $cityName ? "{$p->name} ({$cityName})" : $p->name,
                    'website'            => $p->website,
                    'phone_country_code' => $p->phone_country_code,
                    'phone_number'       => $p->phone_number,
                    'street_address_1'   => $p->street_address_1,
                    'street_address_2'   => $p->street_address_2,
                    'zip_id'             => $p->zip_id,
                    'zip_code'           => $p->zipcode?->code,
                    'city_id'            => $p->city_id,
                    'city'               => $cityName,
                    'state_id'           => $p->state_id,
                    'state'              => $p->state?->name,
                    'state_code'         => $p->state?->state_code,
                    'country_id'         => $p->country_id,
                    'country'            => $p->country?->name,
                ];
            })->values()
        );
    }
}
