<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('/post', [PostController::class, 'get']);
Route::get('/post/{id}', [PostController::class, 'getSinglePost']);
Route::post('/post/create', [PostController::class, 'store']);
Route::put('/post/edit/{id}', [PostController::class, 'update']);
Route::delete('/post/delete/{id}', [PostController::class, 'delete']);
Route::get('/downloadCsv', [PostController::class, 'downloadCsv']);
Route::post('/uploadCsv', [PostController::class, 'uploadCsv']);