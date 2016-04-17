<?php 
	return [
		'app' => [
			'url' => 'http://localhost',
			'hash' => [
				'algo' => PASSWORD_BCRYPT,
				'cost' => 10
			]
		],
		'db' => [
			'driver' => 'mysql',
			'host' => '127.0.0.1',
			'database' => 'vbteam5_db',
			'username' => 'root',
			'password' => 'root',
			'charset' => 'utf8',
			'collation' => 'utf8_unicode_ci'
		],
		'auth' => [
			'session' => 'user_id',
			'remember' => 'user_r'
		],
		'mail' => [
			'smtp_auth' => true,
			'smtp_secure' => 'tls',
			'host' => 'smtp.gmail.com',
			'username' => 'sovantha.sok@vbteam.net',
			'password' => '',
			'port' => 587,
			'html' => true
		],
		'twig' => [
			'debug' => true
		],
		'csrf' => [
			'session' => 'csrf_token'
		]
	];
?>