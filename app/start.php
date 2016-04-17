<?php

use Slim\Slim;
use Noodlehaus\Config;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use RandomLib\Factory;

use VBT\Mail\Mailer;
use VBT\Models\User;
use VBT\Models\Plan;
use VBT\Helpers\Hash;
use VBT\Validation\Validator;
use VBT\Middleware\BeforeMiddleware;
use VBT\Middleware\CsrfMiddleware;


session_cache_limiter(false);
session_start();

ini_set('display_errors', 'On');

define('INC_ROOT', dirname(__DIR__));

require INC_ROOT . '/vendor/autoload.php';

$app = new Slim([
	'mode' => file_get_contents(INC_ROOT . '/mode.php'),
	'view' => new Twig(),
	'templates.path' => INC_ROOT . '/app/views'
]);

$app->add(new BeforeMiddleware);
$app->add(new CsrfMiddleware);

$app->configureMode($app->config('mode'), function() use($app){
	$app->config = Config::load(INC_ROOT . "/app/config/{$app->mode}.php");
});

//require 'database.php';
require 'filters.php';
require 'routes.php';

$app->auth = false;

$app->container->set('user', function(){ return new User; });

$app->container->singleton('google', function() use($app){ 
	$googleClient = new Google_Client();

	$googleClient->setClientId('46538054709-pfh6ljl2n2b692fql97b6ok2dj5cdeqh.apps.googleusercontent.com');
	$googleClient->setClientSecret('wJ5vyslqOkw9aygcahW_lH8D');
	$googleClient->setRedirectUri('http://localhost:8888/google');
	$googleClient->setScopes('email');

	return $googleClient;
});

$app->container->singleton('hash', function() use($app){
	return new Hash($app->config);
});

$app->container->singleton('validation', function() use($app){
	return new Validator($app->user, $app->hash, $app->auth);
});

$app->container->singleton('mail', function() use($app){
	$mailer = new PHPMailer;

	$mailer->isSMTP();
	$mailer->Host = $app->config->get('mail.host');
	$mailer->SMTPAuth = $app->config->get('mail.smtp_auth');
	$mailer->SMTPSecure = $app->config->get('mail.smtp_secure');
	$mailer->Port = $app->config->get('mail.port');
	$mailer->Username = $app->config->get('mail.username');
	$mailer->Password = $app->config->get('mail.password');
	$mailer->isHTML($app->config->get('mail.html'));

	return new Mailer($app->view, $mailer);
});

$app->container->singleton('randomlib', function(){
	$factory = new Factory;
	return $factory->getMediumStrengthGenerator();
});

$app->container->singleton('db', function() use ($app) {
    $capsule = new Illuminate\Database\Capsule\Manager;
    $capsule->setFetchMode(PDO::FETCH_OBJ);
    $capsule->addConnection([
		'driver' => $app->config->get('db.driver'),
		'host' => $app->config->get('db.host'),
		//'port' => '4040',
		'database' => $app->config->get('db.database'),
		'username' => $app->config->get('db.username'),
		'password' => $app->config->get('db.password'),
		'charset' => $app->config->get('db.charset'),
		'collation' => $app->config->get('db.collation'),
		'prefix' => $app->config->get('db.prefix')
	]);
    $capsule->bootEloquent();
    return $capsule->getConnection();
});
$app->db;

$view = $app->view();

$view->parserOptions = [
	'debug' => $app->config->get('twig.debug'),
	'cache' => INC_ROOT . "/var/cache"
];

$view->parserExtensions = [
	new TwigExtension
];

