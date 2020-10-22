<?php

use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;
use Orion\Tests\Fixtures\App\Http\Controllers\CompanyTeamsController;
use Orion\Tests\Fixtures\App\Http\Controllers\PostCategoryController;
use Orion\Tests\Fixtures\App\Http\Controllers\PostPostImageController;
use Orion\Tests\Fixtures\App\Http\Controllers\PostPostMetaController;
use Orion\Tests\Fixtures\App\Http\Controllers\PostsController;
use Orion\Tests\Fixtures\App\Http\Controllers\PostUserController;
use Orion\Tests\Fixtures\App\Http\Controllers\TeamsController;
use Orion\Tests\Fixtures\App\Http\Controllers\UserPostsController;

Route::group(['as' => 'api.', 'prefix' => 'api'], function () {
    Orion::resource('teams', TeamsController::class);
    Orion::resource('posts', PostsController::class)->withSoftDeletes();

    Orion::belongsToResource('posts', 'user', PostUserController::class);
    Orion::belongsToResource('posts', 'category', PostCategoryController::class)->withSoftDeletes();
    Orion::hasOneResource('posts', 'meta', PostPostMetaController::class);
    Orion::hasOneResource('posts', 'image', PostPostImageController::class)->withSoftDeletes();
    Orion::hasManyResource('companies', 'teams', CompanyTeamsController::class);
    Orion::hasManyResource('users', 'posts', UserPostsController::class)->withSoftDeletes();
});
