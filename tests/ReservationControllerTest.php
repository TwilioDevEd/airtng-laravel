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
}
