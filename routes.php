<?php
// Archivo de rutas de la aplicacion y controlador con api's

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->any('/', function ($request, $response, $args) {
    return $this->view->render($response, 'inicio.html');
})->setName('inicio');


$app->any('/visitas', function ($request, $response, $args) {
    return $this->view->render($response, 'visitas.html');
})->setName('visitas');


$app->any('/inicio/asistencia', function ($request, $response, $args) {
	$parsedBody = $request->getParsedBody();
	$registro=$this->db->registro();
	//organizando los datos en un nuevo arreglo
	var_dump($parsedBody);

		$data['nombre']=$parsedBody['nombre'];
		$data['apellido']=$parsedBody['apellido'];
		$data['cedula']=$parsedBody['ced'];
		$data['placa']=$parsedBody['no_placa'];
		$data['org']=$parsedBody['org_vi'];
		$data['tipo_visita']=$parsedBody['tipo_visita'];

	/*Condicion para almacenar correctamente el motivo segun el tipo de visita*/
	if ($parsedBody['tipo_visita']=='Externa'){
		$data['motivo']=$parsedBody['motivo_ext'];
	}
	else if ($parsedBody['tipo_visita']=='Interna')
	{
		$data['motivo']=$parsedBody['motivo_int'];
	}

	echo "<p>hola</p>";
	var_dump($data);

	$registro()->insert($data);
	die();	
})->setName('visitas_reg');

	$asistencia = $this->db->asisprueba();

	if ($parsedBody["ced-vol"]) {
		echo "Cedula del voluntario: ".$parsedBody["ced-vol"];
		$data['cedula'] = $parsedBody["ced-vol"];
		$row = $asistencia->insert($data);
	}elseif ($parsedBody["ced-tbj"]) {
		$data['cedula'] = $parsedBody["ced-tbj"];
		$row = $asistencia->insert($data);
		echo "Cedula del trabajador";
	}else{
		echo "El campo esta vacio".$parsedBody;
	}

	die();
})->setName('asistencia');
