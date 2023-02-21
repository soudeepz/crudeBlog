<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function showAvatarForm(){
        return view('avatar-form');
    }
    
    public function profile(User $user){

        return view('profile-posts', ['username' => $user->username, 'posts' => $user->posts()->latest()->get(), 'postCount' => $user->posts()->count()]);
    }
    
    public function logout(){
        auth()->logout();
        return redirect('/')->with('success','You have been Logged Out!');
    }

    public function showCorrectHomepage(){
        if(auth()->check()){
            return view('homepage-feed');
        } else{
            return view('homepage');
        }
    }

    public function login(Request $request){
        $incomingFields = $request->validate([
            'loginusername' => ['required'],
            'loginpassword' => ['required']
        ]);

        //check if the username exists and matches with the password
        if (auth()->attempt(['username' => $incomingFields['loginusername'], 'password' => $incomingFields['loginpassword']])){
            $request->session()->regenerate();
            return redirect('/')->with('success','You have logged In!');
        }else{
            return redirect('/')->with('failure', 'Invalid Login');
        }
    }

    public function register(Request $request){
        $incomingFields = $request->validate([
            'username' => ['required', 'min:3', 'max:30', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8', 'confirmed']
        ]);

        $incomingFields['password'] = bcrypt($incomingFields['password']); 

        $user = User::create($incomingFields);
        auth()->login($user);
        return redirect('/')->with('success','Thank you for creating an account');
    }
}
