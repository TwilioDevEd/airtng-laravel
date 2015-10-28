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
    protected $table = 'reservations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['message'];

    /**
     * Get the property for this reservation.
     */
    public function property()
    {
        return $this->belongsTo('App\VacationProperty', 'vacation_property_id');
    }

    /**
     * Get the user for this reservation.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function confirm()
    {
        $this->status = 'confirmed';
        $this->save();
    }

    public function reject()
    {
        $this->status = 'rejected';
        $this->save();
    }
}
