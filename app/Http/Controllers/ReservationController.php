<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Reservation;
use App\VacationProperty;
use Services_Twilio as TwilioRestClient;

class ReservationController extends Controller
{
    /**
     * Store a new reservation
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(TwilioRestClient $client, Request $request, Authenticatable $user, $id)
    {
        $this->validate(
            $request, [
                'message' => 'required|string'
            ]
        );
        $property = VacationProperty::find($id);
        $reservation = new Reservation($request->all());
        $reservation->user()->associate($user);

        $property->reservations()->save($reservation);

        $this->notifyHost($client, $reservation);

        $request->session()->flash(
            'status',
            "Sending your reservation request now."
        );
        return redirect()->route('property-show', ['id' => $property->id]);
    }

    private function notifyHost($client, $reservation)
    {
        $host = $reservation->property->user;

        $twilioNumber = config('services.twilio')['number'];

        try {
            $client->account->messages->sendMessage(
                $twilioNumber, // From a Twilio number in your account
                $host->fullNumber(), // Text any number
                $reservation->message
            );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
            exit;
        }
    }
}
