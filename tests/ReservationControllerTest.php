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
            'name' => 'Captain Kirk',
            'email' => 'jkirk@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180101'
        ];

        $newUser = new User($userData);
        $newUser->save();
        $this->be($newUser);

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
        $this->assertCount(1, Reservation::all());
        $reservation = Reservation::first();
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
            'name' => 'Captain Kirk',
            'email' => 'jkirk@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180101'
        ];

        $newUser = new User($userData);
        $newUser->save();

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
        $reservation->user()->associate($newUser);
        $newProperty->reservations()->save($reservation);
        $reservation = $reservation->fresh();
        $this->assertEquals('pending', $reservation->status);

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
        $this->assertNotNull($messageDocument->Message);
        $this->assertNotEmpty($messageDocument->Message);
        $this->assertEquals(strval($messageDocument->Message), 'You have successfully confirmed the reservation.');
    }

    public function testAcceptRejectReject()
    {
        // Given
        $this->startSession();
        $userData = [
            'name' => 'Captain Kirk',
            'email' => 'jkirk@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180101'
        ];

        $newUser = new User($userData);
        $newUser->save();

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
        $reservation->user()->associate($newUser);
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
        $messageDocument = new SimpleXMLElement($response->getContent());

        $reservation = $reservation->fresh();
        $this->assertEquals('rejected', $reservation->status);
        $this->assertNotNull($messageDocument->Message);
        $this->assertNotEmpty($messageDocument->Message);
        $this->assertEquals(strval($messageDocument->Message), 'You have successfully rejected the reservation.');
    }

    public function testAcceptRejectNoPending()
    {
        // Given
        $this->startSession();
        $userData = [
            'name' => 'Captain Kirk',
            'email' => 'jkirk@enterprise.space',
            'password' => 'strongpassword',
            'country_code' => '1',
            'phone_number' => '5558180101'
        ];

        $newUser = new User($userData);
        $newUser->save();

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
        $reservation->user()->associate($newUser);
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
        $this->assertNotNull($messageDocument->Message);
        $this->assertNotEmpty($messageDocument->Message);
        $this->assertEquals(strval($messageDocument->Message), 'Sorry, it looks like you don\'t have any reservations to respond to.');
    }
}
