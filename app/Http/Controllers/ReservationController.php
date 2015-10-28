<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Reservation;
use App\User;
use App\VacationProperty;
use DB;
use Services_Twilio as TwilioRestClient;
use Services_Twilio_Twiml as TwilioTwiml;

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
        $reservation->respond_phone_number = $user->fullNumber();
        $reservation->user()->associate($property->user);

        $property->reservations()->save($reservation);

        $this->notifyHost($client, $reservation);

        $request->session()->flash(
            'status',
            "Sending your reservation request now."
        );
        return redirect()->route('property-show', ['id' => $property->id]);
    }

    public function acceptReject(Request $request)
    {
        $hostNumber = $request->input('From');
        $smsInput = strtolower($request->input('Body'));
        $host = User::where(DB::raw("CONCAT('+',country_code::text, phone_number::text)"), 'LIKE', "%".$hostNumber."%")
                    ->get()
                    ->first();
        $reservation = $host->pendingReservations()->first();
        $smsResponse = null;
        if (!is_null($reservation))
        {
            if ($smsInput === 'yes' || $smsInput === 'accept')
            {
                $reservation->confirm();
            }
            else
            {
                $reservation->reject();
            }

            $smsResponse = 'You have successfully ' . $reservation->status . ' the reservation.';
        }
        else
        {
            $smsResponse = 'Sorry, it looks like you don\'t have any reservations to respond to.';
        }

        return response($this->respond($smsResponse, $reservation))->header('Content-Type', 'application/xml');
    }

    private function respond($smsResponse, $reservation)
    {
        $response = new TwilioTwiml;
        $response->message($smsResponse);

        if (!is_null($reservation))
        {
            $response->message(
                'Your reservation has been ' . $reservation->status . '.',
                ['to' => $reservation->respond_phone_number]
            );
        }
        return $response;
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
            Log::error($e->getMessage());
        }
    }
}
