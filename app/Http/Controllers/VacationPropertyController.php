<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Authenticatable;
use App\VacationProperty;

class VacationPropertyController extends Controller
{
    /**
     * Store a new property
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createNewProperty(Request $request, Authenticatable $user)
    {
        $this->validate(
            $request, [
                'description' => 'required|string',
                'image_url' => 'required|url'
            ]
        );

        $newProperty = new VacationProperty($request->all());
        $user->properties()->save($newProperty);

        $request->session()->flash(
            'status',
            "Property successfully created"
        );
        return redirect()->route('property-index');
    }

    public function index()
    {
        $properties = VacationProperty::All();
        return view('property.index', ['properties' => $properties]);
    }

    public function show($id)
    {
        $property = VacationProperty::find($id);

        return view('property.show', ['property' => $property]);
    }

    public function editForm($id)
    {
        $property = VacationProperty::find($id);

        return view('property.edit', ['property' => $property]);
    }

    public function editProperty(Request $request, $id) {
        $property = VacationProperty::find($id);
        $property->update($request->all());

        return redirect()->route('property-show', ['id' => $id]);
    }
}
