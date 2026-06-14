<?php

use App\Http\Controllers\RecommendationChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/recommendations', [RecommendationChatController::class, 'index'])
    ->name('recommendations.index');

Route::post('/recommendations', [RecommendationChatController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('recommendations.store');
