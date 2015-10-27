<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\VacationProperty;
use App\User;

class VacationPropertyControllerTest extends TestCase
{
    use DatabaseTransactions;

    function testCreateNewProperty() {
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

        $validDescription = 'Some description';
        $validImageUrl = 'http://www.someimage.com';
        $this->assertCount(0, VacationProperty::all());

        // When
        $response = $this->call(
            'POST',
            route('property-create'),
            ['description' => $validDescription,
             'image_url' => $validImageUrl,
             '_token' => csrf_token()]
        );

        // Then
        $this->assertCount(1, VacationProperty::all());
        $property = VacationProperty::first();
        $this->assertEquals($property->description, $validDescription);
        $this->assertEquals($property->image_url, $validImageUrl);
        $this->assertRedirectedToRoute('property-index');
        $this->assertSessionHas('status');
        $flashMessage = $this->app['session']->get('status');
        $this->assertEquals(
            $flashMessage,
            "Property successfully created"
        );
    }

    function testEditProperty() {
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
        $this->assertCount(1, VacationProperty::all());

        // When
        $response = $this->call(
            'POST',
            route('property-edit-action', ['id' => $newProperty->id]),
            ['description' => 'edited description',
             'image_url' => 'http://www.modified.net',
             '_token' => csrf_token()]
        );

        // Then
        $this->assertCount(1, VacationProperty::all());
        $property = VacationProperty::first();
        $this->assertEquals($property->description, 'edited description');
        $this->assertEquals($property->image_url, 'http://www.modified.net');
        $this->assertRedirectedToRoute('property-show', ['id' => $property->id]);
    }
}
