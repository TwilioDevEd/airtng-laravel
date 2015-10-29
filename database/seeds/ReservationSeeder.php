<?php
use Illuminate\Database\Seeder;
use App\User;
use App\VacationProperty;
use App\Reservation;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hostUser = new User(
            ['name' => 'Captain Kirk',
             'email' => 'jkirk@enterprise.space',
             'password' => Hash::make('strongpassword'),
             'country_code' => '1',
             'phone_number' => '5558180101']
        );
        $hostUser->save();
        $guestUser = new User(
            ['name' => 'Mr. Spock',
             'email' => 'spock@enterprise.space',
             'password' => Hash::make('l1v3l0ngandpr0sp3r'),
             'country_code' => '1',
             'phone_number' => '5558180202']
        );
        $guestUser->save();

        $property = new VacationProperty(
        ['description' => 'USS Enterprise',
         'image_url' => 'http://www.ex-astris-scientia.org/articles/new_enterprise/enterprise-11-11-08.jpg']
        );
        $hostUser->properties()->save($property);

        $reservation = new Reservation(
            ['message' => 'I want to reserve a room in your ship']
        );
        $reservation->respond_phone_number = $guestUser->fullNumber();
        $reservation->user()->associate($hostUser);
        $property->reservations()->save($reservation);
    }
}
