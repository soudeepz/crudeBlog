<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Mail\NewPostEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\SendNewPostEmail;
use Illuminate\Support\Facades\Mail;

class PostController extends Controller
{
    public function search($term) {
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
    }

    public function actuallyUpdate(Post $post, Request $request){
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);

        
        dispatch(new SendNewPostEmail(['sendTo' => auth()->user()->email, 'name' => auth()->user()->username, 'title' => $post->title]));

        return back()->with('success','Post successfully updated'); //back() will take the user back to the URL it came from
    }

    public function showEditForm(Post $post){
        return view('edit-post', ['post' => $post]);
    }

    public function delete(Post $post){
        $post->delete();
        return redirect('/profile/'.auth()->user()->username)->with('success', 'The Post has been deleted');
    }

    public function deleteApi(Post $post){
        $post->delete();
        return 'true';
    }

    public function showSinglePost(Post $post){
        $post['body'] = strip_tags(Str::markdown($post->body), '<p><ul><ol><li><em><strong><b><br><h1><h2><h3><h4>');
        return view('single-post', ['post' => $post]);
    }

    public function storeNewPost(Request $request){
        $incomingFields = $request->validate([
            'title' => ['required'],
            'body' => ['required']
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();


        $newPost = Post::create($incomingFields);

        dispatch(new SendNewPostEmail(['sendTo' => auth()->user()->email, 'name' => auth()->user()->username, 'title' => $newPost->title]));

        return redirect("/post/{$newPost->id}")->with('success', 'New Post Successfully Created!');
    }   
    
    public function storeNewPostApi(Request $request){
        $incomingFields = $request->validate([
            'title' => ['required'],
            'body' => ['required']
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();


        $newPost = Post::create($incomingFields);

        dispatch(new SendNewPostEmail(['sendTo' => auth()->user()->email, 'name' => auth()->user()->username, 'title' => $newPost->title]));

        return $newPost->id;
    }

    public function showCreateForm(){
        return view('create-post');
    }
}
