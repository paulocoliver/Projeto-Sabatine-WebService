<?php 
$app['getTransacao'] = $app->protect(function ($params = array()) use ($app) {
	
	$query = "SELECT * FROM transacao";
	$where = array();
	$func  = "fetchAll";
	
	if(!empty($params['id'])){
		$where[] = "id = {$params['id']}";
		$func    = "fetchAssoc";
	}
	
	if(!empty($params['id_usuario'])){
		$where[] = "id_usuario = {$params['id_usuario']}";
	}
	
	if(!empty($params['id_categoria'])){
		$where[] = "id_categoria = {$params['id_categoria']}";
	}
	
	if(!empty($params['data_ini'])){
		$where[] = "data_ini >= {$params['data_ini']}";
	}
	
	if(!empty($params['data_end'])){
		$where[] = "data_end <= {$params['data_end']}";
	}

	if(count($where)){
		$query.= " WHERE ".implode(' AND ', $where);
	}
	
	$query.= " ORDER BY data";
	
	return $app['db']->$func($query);
});

$app->get('/transacao', function ()  use ($app) {
	
	$content = $app['content_decode']();
	
	$id	= (!empty($content) && !empty($content->id) ? $content->id : null);
	$transacao  = $app['getTransacao']($id);
	
	return $app['return']($transacao);
});

$app->post('/transacao', function ()  use ($app) {
	try {
		$content = $app['content_decode']();
		
		$data = array(
			'id_usuario' => $app['usuario']['id'],
			'titulo' 	 => $content->titulo,
			'data'		 => $content->data,
			'tipo' 		 => $content->tipo,
			'valor'	 	 => $content->valor,
		);
		
		if(isset($content->id_categoria)){
			$data['id_categoria'] = (empty($content->id_categoria) ? NULL : $content->id_categoria);
		}
		
		$result  = $app['db']->insert('transacao',$data);
		
		if (empty($result))
			throw new Exception('error_insert_transacao');
		
		$categoria = $app['getTransacao']($app['db']->lastInsertId());
		return $app['return']($categoria);
		
	} catch (Exception $e) {
		die($e);
		return $app['return']('Error create', true);
	}
});

$app->put('/transacao', function ()  use ($app) {
	try {
		$content = $app['content_decode']();
		
		$data = array();
		if (!empty($content->titulo))
			$data['titulo'] = $content->titulo;
		
		if (!empty($content->data))
			$data['data'] = $content->data;
		
		if (!empty($content->tipo))
			$data['tipo'] = $content->tipo;

		if (!empty($content->valor))
			$data['valor'] = $content->valor;
			
		if(isset($content->id_categoria)){
			$data['id_categoria'] = (empty($content->id_categoria) ? NULL : $content->id_categoria);
		}
		
		$where = array('id' => $content->id);
		$result = $app['db']->update('transacao', $data, $where);
		return $app['return']($result);
	} catch (Exception $e) {
		return $app['return']('Error updade', true);
	}
});

$app->delete('/transacao', function ()  use ($app) {
	try {
		$content = $app['content_decode']();
		
		$where  = array('id' => $content->id);
		$result = $app['db']->delete('transacao', $where);
		return $app['return']($result);
	} catch (Exception $e) {
		return $app['return']('Error delete', true);
	}
});
?>