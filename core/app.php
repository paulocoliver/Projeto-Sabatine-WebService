<?php
# SERVER: http://ec2-54-191-139-233.us-west-2.compute.amazonaws.com/

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

require_once 'config-db.php';
//$db = new Doctrine\DBAL\Connection();

$app = new Silex\Application();
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
	'db.options' => array(
		'driver'    => 'pdo_mysql',
		'host'      => $db['host'],
        'dbname'    => $db['dbname'],
        'user'      => $db['user'],
        'password'  => $db['password'],
        'charset'   => 'utf8',
	),
));

############################## Services ##############################
# http://silex.sensiolabs.org/doc/services.html
$app['return'] = $app->protect(function ($result, $error=false, $code=200) use ($app) {
	$status = 'success';
	if ($error === true) {
		$status = 'error';
		if (!is_array($result))
			$result = array('message' => $result);
	}
	return $app->json(array('status' => $status, 'result' => $result), $code);//json_encode(array('status' => $status, 'result' => $result));
});

$app['login'] = $app->protect(function ($email, $senha) use ($app) {
	return $app['db']->fetchAssoc("SELECT * FROM usuario WHERE email = ? AND senha = ?", array($email, $senha));
});
$app['content_decode'] = $app->protect(function () use ($app) {
	return @json_decode($app['request']->getContent());
});


############################## Middlewares ##############################
# http://silex.sensiolabs.org/doc/middlewares.html
$app->before(function (Request $request) use ($app) {
	try {
		$api_key  = $request->headers->get('x-api-key');
		if ($api_key != 'd2UyM3dlMjN3ZTIz')
			throw new Exception('error header x-api-key');
		
		$route_name   = $request->attributes->get('_route');
		$routes_allow = array('POST_usuario', 'POST_usuario_login');
		
		if (!in_array($route_name, $routes_allow)) {
			# http://php.net/manual/pt_BR/features.http-auth.php
			$usuario = $app['login']($request->getUser(), $request->getPassword());
			if (empty($usuario))
				throw new Exception('Unauthorized');
			$app['usuario'] = $usuario;
		}
	} catch (Exception $e) {
		if ($e->getMessage() == 'Unauthorized')
			return $app['return']('Unauthorized', true, 401);
		else
			return $app['return']($e->getMessage(), true, 500);
	}
});

$app->after(function (Request $request, Response $response) {
	$response->headers->set('Content-Type', 'text/json');
});
	

############################## Actions ##############################
# http://silex.sensiolabs.org/doc/usage.html#routing
$app->get('/', function ()  use ($app) {
	return $app['return']('');
});

require "usuario.php";
require "categoria.php";
require "transacao.php";
