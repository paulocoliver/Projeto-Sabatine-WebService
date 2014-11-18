<?php 
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
		$result  = $app['db']->insert(
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
		return $app['return']('Error create', true);
	}
});

$app->put('/usuario', function ()  use ($app) {
	try {
		$content = $app['content_decode']();
		
		$data = array();
		if (!empty($content->nome))
			$data['nome'] = $content->nome;
		
		if (!empty($content->email))
			$data['email'] = $content->email;
		
		if (!empty($content->senha))
			$data['senha'] = $content->senha;
		
		$where = array('id' => $app['usuario']['id']);
		$result = $app['db']->update('usuario', $data, $where);
		return $app['return']($result);
	} catch (Exception $e) {
		return $app['return']('Error updade', true);
	}
	
});
$app->delete('/usuario', function ()  use ($app) {
	try {
		$where  = array('id' => $app['usuario']['id']);
		$result = $app['db']->delete('usuario', $where);
		return $app['return']($result);
	} catch (Exception $e) {
		return $app['return']('Error delete', true);
	}
});
?>