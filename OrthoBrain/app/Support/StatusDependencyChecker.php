<?php

namespace App\Support;

use App\Models\City;
use App\Models\Country;
use App\Models\Practice;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\State;
use App\Models\Zipcode;
use Illuminate\Database\Eloquent\Model;

class StatusDependencyChecker
{
    public static function activeDependents(Model $model): array
    {
        if ($model instanceof ProductCategory) {
            return self::nonZero([
                'Sub-Categories' => $model->subcategories()->where('status', true)->count(),
                'Products'       => $model->products()->where('status', 'ACTIVE')->count(),
            ]);
        }

        if ($model instanceof ProductSubcategory) {
            return self::nonZero([
                'Products' => $model->products()->where('status', 'ACTIVE')->count(),
            ]);
        }

        if ($model instanceof Country) {
            $stateIds = $model->states()->pluck('id');
            $cityIds  = City::whereIn('state_id', $stateIds)->pluck('id');

            return self::nonZero([
                'States'    => $model->states()->where('status', 'ACTIVE')->count(),
                'Cities'    => City::whereIn('state_id', $stateIds)->where('status', 'ACTIVE')->count(),
                'Zipcodes'  => Zipcode::whereIn('city_id', $cityIds)->where('status', 'ACTIVE')->count(),
                'Practices' => Practice::where('country_id', $model->id)->where('status', 'ACTIVE')->count(),
            ]);
        }

        if ($model instanceof State) {
            $cityIds = $model->cities()->pluck('id');

            return self::nonZero([
                'Cities'    => $model->cities()->where('status', 'ACTIVE')->count(),
                'Zipcodes'  => Zipcode::whereIn('city_id', $cityIds)->where('status', 'ACTIVE')->count(),
                'Practices' => Practice::where('state_id', $model->id)->where('status', 'ACTIVE')->count(),
            ]);
        }

        if ($model instanceof City) {
            return self::nonZero([
                'Zipcodes'  => $model->zipcodes()->where('status', 'ACTIVE')->count(),
                'Practices' => $model->practices()->where('status', 'ACTIVE')->count(),
            ]);
        }

        if ($model instanceof Zipcode) {
            return self::nonZero([
                'Practices' => Practice::where('zip_id', $model->id)->where('status', 'ACTIVE')->count(),
            ]);
        }

        return [];
    }

    public static function buildResponsePayload(Model $model, array $dependents): array
    {
        $name = $model->name ?? $model->code ?? ('#' . $model->getKey());

        $parts = [];
        foreach ($dependents as $label => $count) {
            $parts[] = $count . ' active ' . ($count === 1 ? rtrim($label, 's') : $label);
        }
        $summary = implode(', ', $parts);

        return [
            'ok'                    => false,
            'requires_confirmation' => true,
            'dependents'            => $dependents,
            'subject'               => (string) $name,
            'message'               => 'Heads up: "' . $name . '" has ' . $summary . '. Deactivate anyway?',
        ];
    }

    private static function nonZero(array $counts): array
    {
        return array_filter($counts, fn ($n) => $n > 0);
    }
}
