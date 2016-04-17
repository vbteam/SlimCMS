<?php 
	return [
		'app' => [
			'url' => 'http://localhost:8888',
			'hash' => [
				'algo' => PASSWORD_DEFAULT,
				'cost' => 10
			]
		],
		'db' => [
			'driver' => 'mysql',
			'host' => '127.0.0.1',
			'database' => 'vbteam',
			'username' => 'root',
			'password' => 'root',
			'charset' => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix' => ''
		],
		'auth' => [
			'session' => 'user_id',
			'remember' => 'user_r'
		],
		'mail' => [
			'smtp_auth' => true,
			'smtp_secure' => 'tls',
			'host' => 'secure180.servconfig.com',
			'username' => 'sovantha.sok@vbteam.net',
			'password' => 's0vAnth@',
			'port' => 465,
			'html' => true
		],
		'twig' => [
			'debug' => true
		],
		'csrf' => [
			'key' => 'csrf_token'
		]
	];
?>