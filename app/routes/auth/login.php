<?php
use Carbon\Carbon;

$app->get('/google', $guest(), function() use($app){
	if($app->request->get('code')){
		
		$app->google->authenticate($app->request->get('code'));
		$token = $app->google->getAccessToken();		
		$payload = $app->google->verifyIdToken($token['id_token']);

		if(!$payload['email_verified']){
			$app->flash('global', 'Your email has not been verified');
			return $app->response->redirect($app->urlFor('login'));
		}else{
			$user = $app->user->where('sub', $payload['sub'])->where('active', true)->first();
			if($user){
				$_SESSION[$app->config->get('auth.session')] = $user->id;
				$app->google->setAccessToken($token);

				$app->flash('global', 'You are now signed in.');
				return $app->response->redirect($app->urlFor('app'));
			}else{
				header("HTTP/1.1 401 Unauthorized");
    			exit;
			}
		}		
	}else{
		$app->flash('global', 'Could not log you in or account has not been activated.');
		return $app->response->redirect($app->urlFor('login'));
	}
	
});

$app->get('/login', $guest(), function() use($app){
	//return $app->response->redirect($app->google->createAuthUrl());
	return $app->render('auth/login.twig', ['authUrl' => $app->google->createAuthUrl()]);
})->name('login');

$app->post('/login', $guest(), function() use($app){
	$request = $app->request;
	$identifier = $request->post('identifier');
	$password = $request->post('password');
	$remember = $request->post('remember');

	$v = $app->validation;

	$v->validate([
		'identifier' => [$identifier, 'required'],
		'password' => [$password, 'required']
	]);

	if($v->passes()){
		$user = $app->user
		->where('active', true)
		->where(function($query) use($identifier){
			return $query->where('email', $identifier)->orWhere('username', $identifier);
		})->first();

		if($user && $app->hash->passwordCheck($password, $user->password)){
			$_SESSION[$app->config->get('auth.session')] = $user->id;

			if($remember === 'on'){
				$rememberIdentifier = $app->randomlib->generateString(128);
				$rememberToken = $app->randomlib->generateString(128);

				$user->updateRememberCredentials(
					$rememberIdentifier,
					$app->hash->hash($rememberToken)
				);

				$app->setCookie(
					$app->config->get('auth.remember'),
					"{$rememberIdentifier}___{$rememberToken}",
					Carbon::parse('+1 week')->timestamp
				);
			}

			$app->flash('global', 'You are now signed in.');
			return $app->response->redirect($app->urlFor('app'));
		}else{
			$app->flash('global', 'Could not log you in or account has not been activated.');
			return $app->response->redirect($app->urlFor('login'));
		}
	}

	$app->render('auth/login.twig', [
		'errors' => $v->errors(),
		'request' => $request
	]);
})->name('login.post');