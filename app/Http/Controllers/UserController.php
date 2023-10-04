<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Events\OurExampleEvent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
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

    private function getSharedData($user) {
        $currentlyFollowing = 0;

        if(auth()->check()){
            $currentlyFollowing = Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->count();
        }

        View::share('sharedData', ['currentlyFollowing' => $currentlyFollowing, 'avatar' => $user->avatar,'username' => $user->username, 'postCount' => $user->posts()->count(), 'followerCount' => $user->followers()->count(), 'followingCount' => $user->followingTheseUsers()->count()]);
    }

    public function profile(User $user){
        $this->getSharedData($user);
        return view('profile-posts', ['posts' => $user->posts()->latest()->get()]);
    }

    public function profileFollowers(User $user){
        $this->getSharedData($user);
        return view('profile-followers', ['followers' => $user->followers()->latest()->get()]);
    }

    public function profileFollowing(User $user){
        $this->getSharedData($user);
        return view('profile-following', ['following' => $user->followingTheseUsers()->latest()->get()]);
    }


    public function logout(){
        event(new OurExampleEvent(['username' => auth()->user()->username, 'action' => 'logout']));
        auth()->logout();
        return redirect('/')->with('success','You have been Logged Out!');
    }

    public function showCorrectHomepage(){
        if(auth()->check()){
            return view('homepage-feed', ['posts' => auth()->user()->feedPosts()->latest()->paginate(4)]);
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
            event(new OurExampleEvent(['username' => auth()->user()->username, 'action' => 'login']));
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
