<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::get('/materials', [MaterialController::class, 'index']);
Route::get('/materials/{id}', [MaterialController::class, 'show']);
Route::post('/materials', [MaterialController::class, 'store']);
Route::put('/materials/{id}', [MaterialController::class, 'update']);
Route::delete('/materials/{id}',[MaterialController::class, 'destroy']);


Route::get('/languages', [LanguageController::class, 'index']);
Route::get('/languages/{id}', [LanguageController::class, 'show']);
Route::post('/languages', [LanguageController::class, 'store']);
Route::put('/languages/{id}', [LanguageController::class, 'update']);
Route::delete('/languages/{id}',[LanguageController::class, 'destroy']);


Route::get('/countries', [CountryController::class, 'index']);
Route::get('/countries/{id}', [CountryController::class, 'show']);
Route::post('/countries', [CountryController::class, 'store']);
Route::put('/countries/{id}', [CountryController::class, 'update']);
Route::delete('/countries/{id}',[CountryController::class, 'destroy']);


Route::get('/topics', [TopicController::class, 'index']);
Route::get('/topics/{id}', [TopicController::class, 'show']);
Route::post('/topics', [TopicController::class, 'store']);
Route::put('/topics/{id}', [TopicController::class, 'update']);
Route::delete('/topics/{id}',[TopicController::class, 'destroy']);


Route::get('/tags', [TagController::class, 'index']);
Route::get('/tags/{id}', [TagController::class, 'show']);
Route::post('/tags', [TagController::class, 'store']);
Route::put('/tags/{id}', [TagController::class, 'update']);
Route::delete('/tags/{id}',[TagController::class, 'destroy']);

Route::post('/login', [AuthController::class, 'login']);
