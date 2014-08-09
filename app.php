<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
require_once __DIR__.'/vendor/autoload.php';
require_once 'config-db.php';

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


############################## Middlewares ##############################
# http://silex.sensiolabs.org/doc/middlewares.html
$app->before(function (Request $request) use ($app) {
	$route_name = $request->attributes->get('_route');
	$routes_allowed = array();

	if (!in_array($route_name, $routes_allowed)) {
		
		# http://php.net/manual/pt_BR/features.http-auth.php
		$username = $request->server->get('PHP_AUTH_USER');
		$password = $request->server->get('PHP_AUTH_PW');

		$usuario = $app['db']->fetchAssoc("SELECT * FROM usuario WHERE email = ? AND senha = ?", array($username, $password));
		if (empty($usuario))
			return $app['return']('Unauthorized', true, 401);
		$app['usuario'] = $usuario;

	}

});

$app->after(function (Request $request, Response $response) {
	$response->headers->set('Content-Type', 'text/json');
});
	

############################## Actions ##############################
# http://silex.sensiolabs.org/doc/usage.html#routing
$app->get('/', function ()  use ($app) {
	
	return $app->json($app['usuario']);
	/*return $app['twig']->render('user.twig', array(
	 'users' => $users
	));*/
});