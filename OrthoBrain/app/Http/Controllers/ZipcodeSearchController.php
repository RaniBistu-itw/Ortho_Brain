<?php

namespace App\Http\Controllers;

use App\Models\Zipcode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Lightweight ZIP/postal-code lookup endpoint, used by the case wizard's
 * shipping-address combobox. Replaces the previous approach of inlining all
 * ~600 zipcodes (with city/state/country joins) into every case-edit page
 * load — that payload was 102 KB of uncompressed JSON to render at most 50
 * dropdown options.
 *
 * Two read patterns:
 *   - q=...     : prefix-match on zipcode `code`, plus contains-match on
 *                 city name and state name. Returns top 30.
 *   - ids[]=... : whole-row lookup by id. Used by the JS to resolve a
 *                 saved draft's zipId back to its display label without a
 *                 full search.
 *
 * If neither query parameter is supplied, returns an empty array (we do not
 * want this endpoint accidentally serving the entire table).
 */
class ZipcodeSearchController extends Controller
{
    private const RESULT_LIMIT = 30;

    public function search(Request $request): JsonResponse
    {
        $q   = trim((string) $request->query('q', ''));
        $ids = $request->query('ids');
        $ids = is_array($ids) ? array_values(array_filter(array_map('intval', $ids))) : [];

        if ($q === '' && empty($ids)) {
            return response()->json([]);
        }

        $query = Zipcode::select(['id', 'code', 'city_id'])
            ->with([
                'city:id,name,state_id',
                'city.state:id,name,state_code,country_id',
                'city.state.country:id,country_code',
            ])
            ->where('status', 'ACTIVE')
            ->whereHas('city', fn ($c) => $c->where('status', 'ACTIVE'));

        if (! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('code', 'like', $q . '%')
                  ->orWhereHas('city', fn ($c) => $c->where('name', 'like', '%' . $q . '%'))
                  ->orWhereHas('city.state', fn ($s) => $s->where('name', 'like', '%' . $q . '%'));
            });
        }

        $rows = $query->orderBy('code')->limit(self::RESULT_LIMIT)->get();

        return response()->json($rows->map(function (Zipcode $z) {
            return [
                'id'           => $z->id,
                'code'         => $z->code,
                'cityId'       => $z->city_id,
                'city'         => $z->city?->name,
                'stateId'      => $z->city?->state_id,
                'state'        => $z->city?->state?->name,
                'countryId'    => $z->city?->state?->country_id,
                'country'      => $z->city?->state?->country?->country_code,
                'displayLabel' => trim(implode(' — ', array_filter([
                    $z->code,
                    $z->city?->name,
                    $z->city?->state?->state_code ?? $z->city?->state?->name,
                ]))),
            ];
        })->values());
    }
}
