<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    function testNewUser() {
        // Given
        $this->startSession();
        $validName = 'Captain Kirk';
        $validEmail = 'jkirk@enterprise.space';
        $validPassword = 'strongpassword';
        $validCountryCode = '1';
        $validPhoneNumber = '5558180101';
        $this->assertCount(0, User::all());

        // When
        $response = $this->call(
            'POST',
            route('user-create'),
            ['name' => $validName,
             'email' => $validEmail,
             'password' => $validPassword,
             'country_code' => $validCountryCode,
             'phone_number' => $validPhoneNumber,
             '_token' => csrf_token()]
        );

        // Then
        $this->assertCount(1, User::all());
        $user = User::first();
        $this->assertEquals($user->name, $validName);
        $this->assertEquals($user->email, $validEmail);
        $this->assertEquals($user->country_code, $validCountryCode);
        $this->assertEquals($user->phone_number, $validPhoneNumber);
        $this->assertRedirectedToRoute('home');
        $this->assertSessionHas('status');
        $flashMessage = $this->app['session']->get('status');
        $this->assertEquals(
            $flashMessage,
            "User created successfully"
        );
    }
}
