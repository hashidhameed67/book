<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthorApiController;

Route::apiResource('authors', AuthorApiController::class);