<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Hash;
use App\User;
use Auth;
use \Illuminate\Database\QueryException as QueryException;

class UserController extends Controller
{
    /**
     * Store a new user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createNewUser(Request $request)
    {
        $this->validate(
            $request, [
                'name' => 'required|string',
                'email' => 'required|unique:users|email',
                'password' => 'required',
                'country_code' => 'required',
                'phone_number' => 'required|numeric'
            ]
        );

        $values = $request->all();
        $values['password'] = Hash::make($values['password']);

        $newUser = new User($values);
        $newUser->save();

        Auth::login($newUser);

        $request->session()->flash(
            'status',
            "User created successfully"
        );
        return redirect()->route('home');
    }
}
