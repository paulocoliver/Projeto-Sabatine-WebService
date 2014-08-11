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
		/*$api_key  = $request->headers->get('API_KEY');
		if ($api_key != 'd2UyM3dlMjN3ZTIz')
			throw new Exception('error api_key');*/
		
		$route_name = $request->attributes->get('_route');
		$routes_allow = array('POST_usuario', 'POST_usuario_login');
		
		if (!in_array($route_name, $routes_allow)) {
			# http://php.net/manual/pt_BR/features.http-auth.php
			$usuario = $app['login']($request->getUser(), $request->getPassword());
			if (empty($usuario))
				throw new Exception('error auth user');
			$app['usuario'] = $usuario;
		}
	} catch (Exception $e) {
		return $app['return']('Unauthorized', true, 401);
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
/*
 * Actions usuario
 */
$app->post('/usuario/login', function ()  use ($app) {
	$content = $app['content_decode']();
	$usuario = $app['login']($content->email, $content->senha);
	if (!empty($usuario))
		return $app['return']($usuario);
	else	
		return $app['return']('Erro Login', true);
});
$app->get('/usuario', function ()  use ($app) {
	return $app['return']($app['usuario']);
});
$app->post('/usuario', function ()  use ($app) {
	try {
		$content = $app['content_decode']();
		$result = $app['db']->insert(
			'usuario',
			array(
				'nome'  => $content->nome,
				'email' => $content->email,
				'senha' => $content->senha,
			)
		);
		if (empty($result))
			throw new Exception('error_insert');
		
		$usuario = $app['login']($content->email, $content->senha);
		return $app['return']($usuario);
		
	} catch (Exception $e) {
		return $app['return']('Error add', true);
	}
});
$app->put('/usuario', function ()  use ($app) {
	$content = $app['content_decode']();
	
	$data = array();
	if (!empty($content->nome))
		$data['nome'] = $content->nome;
	
	if (!empty($content->email))
		$data['email'] = $content->email;
	
	if (!empty($content->senha))
		$data['senha'] = $content->senha;
	
	$where = array('id' => $app['usuario']['id']);
	$result = $app['db']->update('usuario', $where, $data);
	return $app['return']($result);
});
$app->delete('/usuario', function ()  use ($app) {
	return $app['return']('delete');
});
