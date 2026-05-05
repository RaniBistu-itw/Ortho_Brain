<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseShippingAddress extends Model
{
    protected $table = 'case_shipping_addresses';

    protected $fillable = [
        'case_id',
        'practice_name',
        'doctor_name',
        'street_address_1',
        'street_address_2',
        'zip_id',
        'city_id',
        'state_id',
        'country_id',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function zip()
    {
        return $this->belongsTo(Zipcode::class, 'zip_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
