<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;
use Illuminate\Routing\Controllers\Middleware;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/admins-only', function(){
    return "Only Admins can view this page";
})->middleware('can:visitAdminPages');

//User related routes
Route::get('/', [UserController::class, "showCorrectHomepage"])->name('login');
Route::post('/register', [UserController::class, "register"])->middleware('guest');
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth');
Route::get('/manage-avatar', [UserController::class, 'showAvatarForm'])->middleware('mustBeLoggedIn');
Route::post('/manage-avatar', [UserController::class, 'storeAvatar'])->middleware('mustBeLoggedIn');

//Follow related routes
Route::post('/create-follow/{user:username}', [FollowController::class, 'createFollow'])->middleware('mustBeLoggedIn');
Route::post('/remove-follow/{user:username}', [FollowController::class, 'deleteFollow'])->middleware('mustBeLoggedIn');

//Blog post related routes
Route::get('/create-post',[PostController::class, 'showCreateForm'])->middleware('mustBeLoggedIn');
Route::post('/create-post', [PostController::class, 'storeNewPost'])->middleware('auth');
Route::get('/post/{post}',[PostController::class, 'showSinglePost']);
Route::delete('/post/{post}', [PostController::class, 'delete'])->middleware('can:delete,post');
Route::get('/post/{post}/edit', [PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}', [PostController::class, 'actuallyUpdate'])->middleware('can:update,post');

//Profile related routes
Route::get('/profile/{user:username}', [UserController::class, 'profile']);