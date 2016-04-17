<?php

$app->get('/recover', $guest(), function() use($app){
	return $app->render('auth/recover.twig');
})->name('recover');

$app->post('/recover', $guest(), function() use($app){
	$request = $app->request;
	$email = $request->post('email');
	
	$v = $app->validation;
	$v->validate([
		'email' => [$email, 'required|email']
	]);

	if($v->passes()){
		$user = $app->user->where('email', $email)->first();

		if(!$user){

			$app->flash('global', 'Could not find that user');
			return $app->response->redirect($app->urlFor('recover'));

		}else{
			$identifier = $app->randomlib->generateString(128);
			$user->update([
				'recover_hash' => $app->hash->hash($identifier)
			]);

			//send email
			$app->mail->send('email/auth/recovered.twig', ['user' => $user, 'identifier' => $identifier], function($message) use($user){
				$message->to($user->email);
				$message->subject('Recover your password');
			});

			$app->flash('global', 'We have emailed you instruction to reset your password');
			return $app->response->redirect($app->urlFor('home'));
		}
	}

	$app->render('auth/recover.twig', [
		'errors' => $v->errors(),
		'request' => $request
	]);

})->name('recover.post');

$app->get('/reset', $guest(), function() use($app){
	$request = $app->request;
	$email = $request->get('email');
	$identifier = $request->get('identifier');

	$hashedIdentifier = $app->hash->hash($identifier);

	$user = $app->user->where('email', $email)->first();
	if(!$user){
		return $app->response->redirect($app->urlFor('home'));
	}

	if(!$user->recover_hash){
		return $app->response->redirect($app->urlFor('home'));
	}

	if(!$app->hash->hashCheck($user->recover_hash, $hashedIdentifier)){
		return $app->response->redirect($app->urlFor('home'));
	}

	return $app->render('auth/reset.twig', [
		'email' => $email,
		'identifier' => $identifier
	]);

})->name('reset');

$app->post('/reset', $guest(), function() use($app){
	$email = $app->request->get('email');
	$identifier = $app->request->get('identifier');
	$password = $app->request->post('password');
	$passwordConfirm = $app->request->post('password_confirm');

	$hashedIdentifier = $app->hash->hash($identifier);

	$user = $app->user->where('email', $email)->first();

	if(!$user){
		return $app->response->redirect($app->urlFor('home'));
	}

	if(!$user->recover_hash){
		return $app->response->redirect($app->urlFor('home'));
	}

	if(!$app->hash->hashCheck($user->recover_hash, $hashedIdentifier)){
		return $app->response->redirect($app->urlFor('home'));
	}

	$v = $app->validation;
	$v->validate([
		'password' => [$password, 'required|min(6)'],
		'password_confirm' => [$passwordConfirm, 'required|matches(password)']
	]);

	if($v->passes()){
		$user->update([
			'password' => $app->hash->password($password),
			'recover_hash' => null
		]);

		$app->flash('global', 'Your password has been now reset and you can now sign in.');
		return $app->response->redirect($app->urlFor('home'));
	}

	return $app->render('auth/reset.twig', [
		'errors' => $v->errors(),
		'email' => $email,
		'identifier' => $identifier
	]);
})->name('reset.post');