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
use Twilio\Rest\Client;
use Twilio\Twiml;
use Log;

class ReservationController extends Controller
{
    public function index(Authenticatable $user)
    {
        $reservations = array();

        foreach ($user->propertyReservations as $reservation)
        {
            array_push($reservations, $reservation->fresh());
        }
        return view(
            'reservation.index',
            [
                'hostReservations' => $reservations,
                'guestReservations' => $user->reservations
            ]
        );
    }

    /**
     * Store a new reservation
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Client $client, Request $request, Authenticatable $user, $id)
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
        return redirect()->route('reservation-index', ['id' => $property->id]);
    }

    public function acceptReject(Client $client, Request $request)
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
            $reservation = $reservation->fresh();

            if (strpos($smsInput, 'yes') !== false || strpos($smsInput, 'accept') !== false)
            {
                $reservation->confirm($this->getNewTwilioNumber($client, $host));
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

    public function connectSms(Request $request)
    {
        $twilioNumber = $request->input('To');
        $incomingNumber = $request->input('From');
        $messageBody = $request->input('Body');

        $reservation = $this->getReservationFromNumber($twilioNumber);
        $host = $reservation->property->user;
        $guest = $reservation->user;

        if ($incomingNumber === $host->fullNumber())
        {
            $outgoingNumber = $guest->fullNumber();
        }
        else
        {
            $outgoingNumber = $host->fullNumber();
        }

        return response($this->connectSmsResponse($messageBody, $outgoingNumber))->header('Content-Type', 'application/xml');
    }

    public function connectVoice(Request $request)
    {
        $twilioNumber = $request->input('To');
        $incomingNumber = $request->input('From');

        $reservation = $this->getReservationFromNumber($twilioNumber);
        $host = $reservation->property->user;
        $guest = $reservation->user;

        if ($incomingNumber === $host->fullNumber())
        {
            $outgoingNumber = $guest->fullNumber();
        }
        else
        {
            $outgoingNumber = $host->fullNumber();
        }

        return response($this->connectVoiceResponse($outgoingNumber))->header('Content-Type', 'application/xml');
    }

    private function getReservationFromNumber($twilioNumber)
    {
        return Reservation::where('twilio_number', '=', $twilioNumber)->first();
    }

    private function connectVoiceResponse($outgoingNumber)
    {
        $response = new Twiml();
        $response->play('http://howtodocs.s3.amazonaws.com/howdy-tng.mp3');
        $response->dial($outgoingNumber);

        return $response;
    }

    private function connectSmsResponse($messageBody, $outgoingNumber)
    {
        $response = new Twiml();
        $response->message(
            $messageBody,
            ['to' => $outgoingNumber]
        );

        return $response;
    }

    private function respond($smsResponse, $reservation)
    {
        $response = new Twiml();
        $response->message($smsResponse);

        if (!is_null($reservation))
        {
            $response->message(
                'Your reservation has been ' . $reservation->status . '.',
                ['to' => $reservation->user->fullNumber()]
            );
        }
        return $response;
    }

    private function notifyHost($client, $reservation)
    {
        $host = $reservation->property->user;

        $twilioNumber = config('services.twilio')['number'];

        $messageBody = $reservation->message . ' - Reply \'yes\' or \'accept\' to confirm the reservation, or anything else to reject it.';

        try {
            $client->messages->create(
                $host->fullNumber(), // Text any number
                [
                    'from' => $twilioNumber, // From a Twilio number in your account
                    'body' => $messageBody
                ]
            );
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function getNewTwilioNumber($client, $host)
    {
        $numbers = $client->availablePhoneNumbers('US')->local->read(
            [
                'areaCode' => $host->areaCode(),
                'voiceEnabled' => 'true',
                "smsEnabled" => 'true'
            ]
        );

        if (empty($numbers)) {
            $numbers = $client->availablePhoneNumbers('US')->local->read(
                [
                    'voiceEnabled' => 'true',
                    "smsEnabled" => 'true'
                ]
            );
        }
        $twilioNumber = $numbers[0]->phoneNumber;

        $newNumber = $client->incomingPhoneNumbers->create(
            [
                "phoneNumber" => $twilioNumber,
                "smsApplicationSid" => config('services.twilio')['applicationSid'],
                "voiceApplicationSid" => config('services.twilio')['applicationSid']
            ]
        );

        if ($newNumber) {
            return $twilioNumber;
        } else {
            return 0;
        }
    }
}
