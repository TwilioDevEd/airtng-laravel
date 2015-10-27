<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vacation_properties';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['description', 'image_url'];

    /**
     * Get the property for this reservation.
     */
    public function property()
    {
        return $this->belongsTo('App\VacationProperty');
    }
}
