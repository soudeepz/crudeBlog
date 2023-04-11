<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function createFollow(User $user){
        //you cannot follow yourself
        if ($user->id == auth()->user()->id){
            return back()->with('failure', 'You cannot follow yourself');
        }

        //you cannot follow someone you are already following
        $existCheck = Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->count();//if a record exists with the given data inside where()[the 2 arrays represent 2 column to search] then it will return true or false [0 or greater than 1 in this case]

        if ($existCheck){
            return back()->with('failure', 'You are already following ' . $user->username);
        }

        $newFollow = new Follow;
        $newFollow->user_id = auth()->user()->id;
        $newFollow->followeduser = $user->id;
        $newFollow->save();

        return back()->with('success', 'You have successfully followed ' . $user->username);
    }

    public function deleteFollow(User $user){
        Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->delete();
        return back()->with('success', 'User successfully unfollowed.');
    }
}
