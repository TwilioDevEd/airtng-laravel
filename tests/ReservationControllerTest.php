<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\VacationProperty;
use App\Reservation;
use App\User;

class ReservationControllerTest extends TestCase
{
    use DatabaseTransactions;

    function testCreate() {
        // Given
        $this->startSession();
        $userData = [
            'name' => 'Captain Kirk host',
            'email' => 'jkirkh@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180101'
        ];

        $newUser = new User($userData);
        $newUser->save();

        $userData2 = [
            'name' => 'Captain Kirk guest',
            'email' => 'jkirkg@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180102'
        ];

        $newUser2 = new User($userData2);
        $newUser2->save();
        $this->be($newUser2);

        $propertyData = [
            'description' => 'Some description',
            'image_url' => 'http://www.someimage.com'
        ];
        $newProperty = new VacationProperty($propertyData);
        $newUser->properties()->save($newProperty);
        $this->assertCount(0, Reservation::all());

        $mockTwilioService = Mockery::mock('Services_Twilio')
                                ->makePartial();
        $mockTwilioAccount = Mockery::mock();
        $mockTwilioMessages = Mockery::mock();
        $mockTwilioAccount->messages = $mockTwilioMessages;
        $mockTwilioService->account = $mockTwilioAccount;

        $twilioNumber = config('services.twilio')['number'];
        $mockTwilioMessages
            ->shouldReceive('sendMessage')
            ->with($twilioNumber, $newUser->fullNumber(), 'Some reservation message')
            ->once();

        $this->app->instance(
            'Services_Twilio',
            $mockTwilioService
        );

        // When
        $response = $this->call(
            'POST',
            route('reservation-create', ['id' => $newProperty->id]),
            ['message' => 'Some reservation message',
             '_token' => csrf_token()]
        );

        // Then
        $reservationFromHost = $newUser->pendingReservations()->first()->fresh();
        $this->assertCount(1, Reservation::all());
        $reservation = Reservation::first();
        $this->assertEquals('Captain Kirk guest', $reservation->user->name);
        $this->assertEquals('Some reservation message', $newUser->pendingReservations()->first()->message);
        $this->assertEquals($newUser2->id, $reservationFromHost->user->id);
        $this->assertEquals($reservation->message, 'Some reservation message');
        $this->assertRedirectedToRoute('property-show', ['id' => $newProperty->id]);
        $this->assertSessionHas('status');
        $flashreservation = $this->app['session']->get('status');
        $this->assertEquals(
            $flashreservation,
            "Sending your reservation request now."
        );
    }

    public function testAcceptRejectConfirm()
    {
        // Given
        $this->startSession();
        $userData = [
            'name' => 'Captain Kirk host',
            'email' => 'jkirkh@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180101'
        ];

        $newUser = new User($userData);
        $newUser->save();

        $userData2 = [
            'name' => 'Captain Kirk guest',
            'email' => 'jkirkg@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180102'
        ];

        $newUser2 = new User($userData2);
        $newUser2->save();

        $propertyData = [
            'description' => 'Some description',
            'image_url' => 'http://www.someimage.com'
        ];
        $newProperty = new VacationProperty($propertyData);
        $newUser->properties()->save($newProperty);

        $reservationData = [
            'message' => 'Reservation message'
        ];

        $reservation = new Reservation($reservationData);
        $reservation->user()->associate($newUser2);
        $newProperty->reservations()->save($reservation);
        $reservation = $reservation->fresh();
        $this->assertEquals('pending', $reservation->status);

        $mockTwilioService = Mockery::mock('Services_Twilio')
                                ->makePartial();
        $mockTwilioAccount = Mockery::mock();
        $mockTwilioAvailablePhoneNumbers = Mockery::mock();
        $mockTwilioAccount->available_phone_numbers = $mockTwilioAvailablePhoneNumbers;
        $mockTwilioService->account = $mockTwilioAccount;
        $availableNumber = Mockery::mock();
        $availableNumber->phone_number = '+15551112222';
        $availableNumbers = Mockery::mock();
        $availableNumbers->available_phone_numbers = array($availableNumber);

        $mockTwilioIncomingPhoneNumbers = Mockery::mock();
        $mockTwilioAccount->incoming_phone_numbers = $mockTwilioIncomingPhoneNumbers;

        $twilioNumber = config('services.twilio')['number'];
        $mockTwilioAvailablePhoneNumbers
            ->shouldReceive('getList')
            ->with('US', 'Local', array(
                "AreaCode" => $newUser->areaCode(),
                "VoiceEnabled" => "true",
                "SmsEnabled" => "true"
            ))
            ->once()
            ->andReturn($availableNumbers);

        $mockTwilioIncomingPhoneNumbers
            ->shouldReceive('create')
            ->with(array(
                "PhoneNumber" => "+15551112222",
                "SmsApplicationSid" => config('services.twilio')['applicationSid'],
                "VoiceApplicationSid" => config('services.twilio')['applicationSid']
            ))
            ->once()
            ->andReturn('Some SID');

        $this->app->instance(
            'Services_Twilio',
            $mockTwilioService
        );

        // When
        $response = $this->call(
            'POST',
            route('reservation-incoming'),
            ['From' => '+15558180101',
             'Body' => 'yes']
        );
        //echo $response;
        $messageDocument = new SimpleXMLElement($response->getContent());

        $reservation = $reservation->fresh();
        $this->assertEquals('confirmed', $reservation->status);
        $this->assertNotNull(strval($messageDocument->Message[0]));
        $this->assertNotEmpty(strval($messageDocument->Message[0]));
        $this->assertEquals(strval($messageDocument->Message[0]), 'You have successfully confirmed the reservation.');
        $this->assertNotNull(strval($messageDocument->Message[1]));
        $this->assertNotEmpty(strval($messageDocument->Message[1]));
        $this->assertEquals(strval($messageDocument->Message[1]), 'Your reservation has been confirmed.');
        $this->assertEquals(strval($messageDocument->Message[1]->attributes()[0]), '+15558180102');
    }

    public function testAcceptRejectReject()
    {
        // Given
        $this->startSession();
        $userData = [
            'name' => 'Captain Kirk host',
            'email' => 'jkirkh@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180101'
        ];

        $newUser = new User($userData);
        $newUser->save();

        $userData2 = [
            'name' => 'Captain Kirk guest',
            'email' => 'jkirkg@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180102'
        ];

        $newUser2 = new User($userData2);
        $newUser2->save();

        $propertyData = [
            'description' => 'Some description',
            'image_url' => 'http://www.someimage.com'
        ];
        $newProperty = new VacationProperty($propertyData);
        $newUser->properties()->save($newProperty);

        $reservationData = [
            'message' => 'Reservation message'
        ];

        $reservation = new Reservation($reservationData);
        $reservation->user()->associate($newUser2);
        $newProperty->reservations()->save($reservation);
        $reservation = $reservation->fresh();
        $this->assertEquals('pending', $reservation->status);

        // When
        $response = $this->call(
            'POST',
            route('reservation-incoming'),
            ['From' => '+15558180101',
             'Body' => 'any other string']
        );
        //echo $response;
        $messageDocument = new SimpleXMLElement($response->getContent());

        $reservation = $reservation->fresh();
        $this->assertEquals('rejected', $reservation->status);
        $this->assertNotNull(strval($messageDocument->Message[0]));
        $this->assertNotEmpty(strval($messageDocument->Message[0]));
        $this->assertEquals(strval($messageDocument->Message[0]), 'You have successfully rejected the reservation.');
        $this->assertNotNull(strval($messageDocument->Message[1]));
        $this->assertNotEmpty(strval($messageDocument->Message[1]));
        $this->assertEquals(strval($messageDocument->Message[1]), 'Your reservation has been rejected.');
        $this->assertEquals(strval($messageDocument->Message[1]->attributes()[0]), '+15558180102');
    }

    public function testAcceptRejectNoPending()
    {
        // Given
        $this->startSession();
        $userData = [
            'name' => 'Captain Kirk host',
            'email' => 'jkirkh@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180101'
        ];

        $newUser = new User($userData);
        $newUser->save();

        $userData2 = [
            'name' => 'Captain Kirk guest',
            'email' => 'jkirkg@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180102'
        ];

        $newUser2 = new User($userData2);
        $newUser2->save();

        $propertyData = [
            'description' => 'Some description',
            'image_url' => 'http://www.someimage.com'
        ];
        $newProperty = new VacationProperty($propertyData);
        $newUser->properties()->save($newProperty);

        $reservationData = [
            'message' => 'Reservation message'
        ];

        $reservation = new Reservation($reservationData);
        $reservation->status = 'confirmed';
        $reservation->user()->associate($newUser2);
        $newProperty->reservations()->save($reservation);
        $reservation = $reservation->fresh();
        $this->assertEquals('confirmed', $reservation->status);

        // When
        $response = $this->call(
            'POST',
            route('reservation-incoming'),
            ['From' => '+15558180101',
             'Body' => 'yes']
        );
        $messageDocument = new SimpleXMLElement($response->getContent());

        $reservation = $reservation->fresh();
        $this->assertEquals('confirmed', $reservation->status);
        $this->assertNotNull(strval($messageDocument->Message[0]));
        $this->assertNotEmpty(strval($messageDocument->Message[0]));
        $this->assertEquals(strval($messageDocument->Message[0]), 'Sorry, it looks like you don\'t have any reservations to respond to.');
    }

    public function testConnectSms()
    {
        // Given
        $userData = [
            'name' => 'Captain Kirk host',
            'email' => 'jkirkh@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180101'
        ];

        $newUser = new User($userData);
        $newUser->save();

        $userData2 = [
            'name' => 'Captain Kirk guest',
            'email' => 'jkirkg@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180102'
        ];

        $newUser2 = new User($userData2);
        $newUser2->save();

        $propertyData = [
            'description' => 'Some description',
            'image_url' => 'http://www.someimage.com'
        ];
        $newProperty = new VacationProperty($propertyData);
        $newUser->properties()->save($newProperty);

        $reservationData = [
            'message' => 'Reservation message'
        ];

        $reservation = new Reservation($reservationData);
        $reservation->status = 'confirmed';
        $reservation->twilio_number = '+15551112222';
        $reservation->user()->associate($newUser2);
        $newProperty->reservations()->save($reservation);
        $reservation = $reservation->fresh();
        $this->assertEquals('confirmed', $reservation->status);

        // When
        $response = $this->call(
            'GET',
            route('reservation-connect-sms'),
            ['To' => '+15551112222',
             'From' => '+15558180101',
             'Body' => 'Some Message']
        );
        $messageDocument = new SimpleXMLElement($response->getContent());

        $this->assertNotNull(strval($messageDocument->Message[0]));
        $this->assertNotEmpty(strval($messageDocument->Message[0]));
        $this->assertEquals(strval($messageDocument->Message[0]), 'Some Message');
        $this->assertEquals(strval($messageDocument->Message[0]->attributes()[0]), '+15558180102');
    }

    public function testConnectVoice()
    {
        // Given
        $userData = [
            'name' => 'Captain Kirk host',
            'email' => 'jkirkh@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180101'
        ];

        $newUser = new User($userData);
        $newUser->save();

        $userData2 = [
            'name' => 'Captain Kirk guest',
            'email' => 'jkirkg@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180102'
        ];

        $newUser2 = new User($userData2);
        $newUser2->save();

        $propertyData = [
            'description' => 'Some description',
            'image_url' => 'http://www.someimage.com'
        ];
        $newProperty = new VacationProperty($propertyData);
        $newUser->properties()->save($newProperty);

        $reservationData = [
            'message' => 'Reservation message'
        ];

        $reservation = new Reservation($reservationData);
        $reservation->status = 'confirmed';
        $reservation->twilio_number = '+15551112222';
        $reservation->user()->associate($newUser2);
        $newProperty->reservations()->save($reservation);
        $reservation = $reservation->fresh();
        $this->assertEquals('confirmed', $reservation->status);

        // When
        $response = $this->call(
            'GET',
            route('reservation-connect-voice'),
            ['To' => '+15551112222',
             'From' => '+15558180101']
        );
        $messageDocument = new SimpleXMLElement($response->getContent());

        $this->assertNotNull(strval($messageDocument->Play[0]));
        $this->assertNotEmpty(strval($messageDocument->Play[0]));
        $this->assertEquals(strval($messageDocument->Play[0]), 'http://howtodocs.s3.amazonaws.com/howdy-tng.mp3');
        $this->assertNotNull(strval($messageDocument->Dial[0]));
        $this->assertNotEmpty(strval($messageDocument->Dial[0]));
        //$this->assertEquals(strval($messageDocument->Dial[0]), 'Some Message');
        //$this->assertEquals(strval($messageDocument->Dial[0]->attributes()[0]), '+15558180102');
    }
}
