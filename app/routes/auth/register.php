<?php

use VBT\Models\UserPermission;

$app->get('/register',$authenticated(), function() use($app){
	return $app->render('auth/register.twig');
})->name('register');

$app->post('/register', $authenticated(), function() use($app){

	$request = $app->request;
	$email = $request->post('email');
	$username = $request->post('username');
	$password = $request->post('password');
	$password_confirm = $request->post('password_confirm');

	$v = $app->validation;
	$v->validate([
		'email' => [$email, 'required|email|uniqueEmail'],
		'username' => [$username, 'required|alnumDash|max(20)|uniqueUsername'],
		'password' => [$password, 'required|min(6)'],
		'password_confirm' => [$password_confirm, 'required|matches(password)']
	]);

	if($v->passes()){
		$identifier = $app->randomlib->generateString(128);

		$user = $app->user->create([
			'email' => $email,
			'username' => $username,
			'password' => $app->hash->password($password),
			'active_hash' => $app->hash->hash($identifier)
		]);

		$user->permissions()->create(UserPermission::$defaults);

		$app->mail->send('email/auth/registered.twig',
			['user' => $user, 'identifier' => $identifier],
			function($message) use($user){
			$message->to($user->email);
			$message->subject('Thanks for registering.');
		});

		$app->flash('global', 'You have been registered!');

		return $app->response->redirect($app->urlFor('users'));
	}

	return $app->render('auth/register.twig', ['errors' => $v->errors(), 'request' => $request]);

})->name('register.post');