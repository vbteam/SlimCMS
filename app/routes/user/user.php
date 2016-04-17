<?php

$app->get('/u/:username',  $authenticated(), function($username) use($app){
	$user = $app->user->where('username', $username)->first();
	if(!$user){
		$app->notFound();
	}

	return $app->render('user/profile.twig', ['user' => $user]);

})->name('profile');

$app->get('/users', $authenticated(), function() use($app){
	$users = $app->user->all();
	return $app->render('user/all.twig', ['users' => $users]);
})->name('users');

$app->post('/users/:id', $authenticated(), function($id) use($app){

	$request = $app->request;
	$email = $request->post('email');
	$firstName = $request->post('first_name');
	$lastName = $request->post('last_name');

	$v = $app->validation;
	$v->validate([
		'email' => [$email, 'required|email|uniqueEmail'],
		'first_name' => [$firstName, 'alpha|max(50)'],
		'last_name' => [$lastName, 'alpha|max(50)']
	]);

	if($v->passes()){
		
		$app->auth->update([
			'email' => $email,
			'first_name' => $firstName,
			'last_name' => $lastName
		]);

		$app->flash('global', 'Your details have been updated.');
		return $app->response->redirect($app->urlFor('profile', ['username' => $app->auth->username]));
		
	}

	return $app->render('user/profile.twig', ['user' => $app->auth, 'errors' => $v->errors()]);

	
})->name('update');

$app->get('/admin', $admin(), function() use($app){
	return $app->render('user/admin.twig');
})->name('admin');