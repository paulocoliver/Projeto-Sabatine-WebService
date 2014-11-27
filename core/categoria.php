<?php 
$app['getCategoria'] = $app->protect(function ($params = array()) use ($app) {
	
	$query = "SELECT * FROM categoria";
	$where = array();
	$func  = "fetchAll";
	
	if(!empty($params['id'])){
		$where[] = "id = {$params['id']}";
		$func    = "fetchAssoc";
	}
	if(!empty($params['id_categoria_pai'])){
		$where[] = "id_categoria_pai = {$params['id_categoria_pai']}";
	}
	
	$where[] = "id_usuario = {$app['usuario']['id']}";
	$query  .= " WHERE ".implode(' AND ', $where);
	
	//echo $query;
	//exit;
	$res = $app['db']->$func($query);
	return !empty($res) ? $res : array();
});

$app->get('/categoria', function ()  use ($app) {
	$categoria  = $app['getCategoria']();	
	return $app['return']($categoria);
});

$app->get('/categoria/{id}', function ($id)  use ($app) {
	$categoria  = $app['getCategoria'](array('id' => $id));
	
	if (empty($categoria))
		return $app['return']('Categoria não encontrada', true, 404);
	
	return $app['return']($categoria);
})->assert('id', '\d+');

$app->post('/categoria', function ()  use ($app) {
	try {
		$content = $app['content_decode']();
		$result  = $app['db']->insert(
			'categoria',
			array(
				'titulo'  		   => $content->titulo,
				'id_usuario'  	   => $app['usuario']['id'],
				'id_categoria_pai' => empty($content->id_categoria_pai) ? NULL : $content->id_categoria_pai
			)
		);
		
		if (empty($result))
			throw new Exception('error_insert_categoria');
		
		$categoria = $app['getCategoria']($app['db']->lastInsertId());
		return $app['return']($categoria);
		
	} catch (Exception $e) {
		die($e);
		return $app['return']('Error create', true);
	}
});

$app->put('/categoria', function ()  use ($app) {
	try {
		$content = $app['content_decode']();
		
		if (!empty($content->titulo))
			$data['titulo'] = $content->titulo;
		
		if (!empty($content->id_categoria_pai))
			$data['id_categoria_pai'] = $content->id_categoria_pai;
		
		$where = array('id' => $content->id);
		$result = $app['db']->update('categoria', $data, $where);
		
		return $app['return']($result);
	} catch (Exception $e) {
		return $app['return']('Error updade', true);
	}
});

$app->delete('/categoria/{id}', function ($id)  use ($app) {
	try {		
		$where  = array('id' => $id);
		$result = $app['db']->delete('categoria', $where);
		return $app['return']($result);
	} catch (Exception $e) {
		return $app['return']('Error delete', true);
	}
});
?>