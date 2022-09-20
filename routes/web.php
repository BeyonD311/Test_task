<?php

/**
 * @var \Laravel\Lumen\Routing\Router $router
 * @var \App\Http\Controllers\TestController $test
 */


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->post('/api/files', "\App\Http\Controllers\FilesController@store");
$router->get('/api/connetions', "\App\Http\Controllers\ConnectionsController@index");
$router->get("/", "\App\Http\Controllers\TestController@test");

