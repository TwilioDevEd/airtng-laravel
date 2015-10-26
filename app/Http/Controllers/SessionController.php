<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Support\MessageBag;

class SessionController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        if (Auth::attempt(['email' => $email, 'password' => $password], true))
        {
            return redirect()->route('home');
        }
        return view('login', ['errors' => new MessageBag(['Invalid username or password'])]);
    }
}
