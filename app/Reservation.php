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
        return $this->belongsTo('App\User', 'user_id');
    }

    public function confirm($twilioNumber)
    {
        $this->status = 'confirmed';
        $this->twilio_number = $twilioNumber;
        $this->save();
    }

    public function reject()
    {
        $this->status = 'rejected';
        $this->save();
    }
}
