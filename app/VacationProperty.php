<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VacationProperty extends Model
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
     * Get the user that owns the property.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function reservations()
    {
        return $this->hasMany('App\Reservation');
    }
}
