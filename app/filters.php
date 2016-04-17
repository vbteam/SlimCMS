<?php

$authenticationCheck = function($required, $mode = '') use($app){
	return function() use($required, $mode, $app){
		if(!$app->auth && $required){
			if($mode == 'api'){
				return $app->redirect($app->urlFor('401'));
			}else{
				return $app->redirect($app->urlFor('login'));
			}
		}
		// if((!$app->auth && $required) || ($app->auth && !$required)){}
	};
};

$authenticated = function($mode = '') use($authenticationCheck){
	return $authenticationCheck(true, $mode);
};

$guest = function($mode = '') use($authenticationCheck){
	return $authenticationCheck(false, $mode);
};

$admin = function() use($app){
	return function() use($app){
		if(!$app->auth || !$app->auth->isAdmin()){
			return $app->redirect($app->urlFor('home'));
		}
	};
};