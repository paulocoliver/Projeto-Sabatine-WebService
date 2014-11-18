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
	
	if(!empty($params['id_usuario'])){
		$where[] = "id_usuario = {$params['id_usuario']}";
	}
	
	if(count($where)){
		$query.= " WHERE ".implode(' AND ', $where);
	}
	
	return $app['db']->$func($query);
});

$app->get('/categoria', function ()  use ($app) {
	
	$content 	= $app['content_decode']();
	
	$id = null;
	
	if(!empty($content) && $content->id){
		$id = $content->id;
	}
	$id			= !empty($content) ? $content->id : null;
	$categoria  = $app['getCategoria']($id);
	
	return $app['return']($categoria);
});

$app->post('/categoria', function ()  use ($app) {
	try {
		$content = $app['content_decode']();
		$result  = $app['db']->insert(
			'categoria',
			array(
				'titulo'  		   => $content->titulo,
				'id_usuario'  	   => $content->id_usuario,
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
		
		$data = array();
		if (!empty($content->titulo))
			$data['titulo'] = $content->titulo;
		
		if (!empty($content->id_categoria_pai))
			$data['id_categoria_pai'] = $content->id_categoria_pai;
		
		if (!empty($content->id_usuario))
			$data['id_usuario'] = $content->id_usuario;
		
		$where = array('id' => $content->id);
		$result = $app['db']->update('categoria', $data, $where);
		return $app['return']($result);
	} catch (Exception $e) {
		return $app['return']('Error updade', true);
	}
});

$app->delete('/categoria', function ()  use ($app) {
	try {
		$content = $app['content_decode']();
		
		$where  = array('id' => $content->id);
		$result = $app['db']->delete('categoria', $where);
		return $app['return']($result);
	} catch (Exception $e) {
		return $app['return']('Error delete', true);
	}
});
?>