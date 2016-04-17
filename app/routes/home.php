<?php
use VBT\Models\User;

$app->get('/', function() use($app){
	return $app->render('home.twig');
})->name('home');

$app->notFound(function() use($app){
	return $app->render('errors/404.twig');
});

//json error response
$app->get('/401', function() use($app){
	$response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->status(401);
    return $response->body(json_encode(['data' => 'Unauthorized']));
})->name('401');

