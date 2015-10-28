<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'country_code', 'phone_number'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];

    /**
     * Get vacation properties for the user.
     */
    public function properties()
    {
        return $this->hasMany('App\VacationProperty');
    }

    public function propertyReservations()
    {
        return $this->hasManyThrough('App\Reservation', 'App\VacationProperty', 'user_id', 'vacation_property_id');
    }

    public function reservations()
    {
        return $this->hasMany('App\Reservation');
    }

    public function pendingReservations()
    {
        return $this->propertyReservations()->where('status', '=', 'pending');
    }

    public function fullNumber()
    {
        return '+' . $this->country_code . $this->phone_number;
    }
}
