<?php
$app->get('/change-password', $authenticated(), function() use($app){
	return $app->render('auth/change.twig');
})->name('change');

$app->post('/change-password', $authenticated(), function() use($app){
	$request = $app->request;
	
	$passwordOld = $request->post('password_old');
	$password = $request->post('password');
	$passwordConfirm = $request->post('password_confirm');

	$v = $app->validation;
	$v->validate([
		'password_old' => [$passwordOld, 'required|matchesCurrentPassword'],
		'password' => [$password, 'required|min(6)'],
		'password_confirm' => [$passwordConfirm, 'required|matches(password)']
	]);

	if($v->passes()){
		$user = $app->auth;

		$user->update([
			'password' => $app->hash->password($password)
		]);

		//send email
		$app->mail->send('email/auth/change.php', [], function($message) use($user) {
			$message->to($user->email);
			$message->subject('You changed your password.');
		});

		$app->flash('global', 'Your new password has been changed!');
		return $app->response->redirect($app->urlFor('home'));
	}

	return $app->render('auth/change.twig', ['errors' => $v->errors()]);

})->name('change.post');