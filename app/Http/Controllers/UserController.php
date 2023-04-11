<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function storeAvatar(Request $request){
        $request->validate([
            'avatar' => 'required|image|max:2048'
        ]);

        $user = auth()->user();

        $filename = $user->id . '-' . uniqid() . '.jpg';//uniqid() gives a random generated text

        $imgData = Image::make($request->file('avatar'))->fit(120)->encode('jpg');//fit(120) means image which is 120px X 120px square
        Storage::put('public/avatars/' . $filename, $imgData);

        $oldAvatar = $user->avatar;

        $user->avatar = $filename;
        $user->save();

        if ($oldAvatar != "/fallback-avatar.jpg"){//if the filename is anything other than fallback pic then delete it
            Storage::delete(str_replace("/storage/", "public/", $oldAvatar));
            //we cannot use /storage/avatars/file because it is meant for web browser. we need path that is meant relative to our storage folder public/avatars/file
        }

        return back()->with('success', 'Congrats on the new avatar');
    }

    public function showAvatarForm(){
        return view('avatar-form');
    }

    public function profile(User $user){
        $currentlyFollowing = 0;

        if(auth()->check()){
            $currentlyFollowing = Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->count();
        }

        return view('profile-posts', ['currentlyFollowing' => $currentlyFollowing, 'avatar' => $user->avatar,'username' => $user->username, 'posts' => $user->posts()->latest()->get(), 'postCount' => $user->posts()->count()]);
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
