<?php
// Archivo de rutas de la aplicacion y controlador con api's

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->any('/', function ($request, $response, $args) {
    return $this->view->render($response, 'inicio.html');
})->setName('inicio');

// Ruta para iniciar sesion como administrador
$app->any('/inicio/login', function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $login=$this->db->login()->select('user');
    $passw=$this->db->login()->select('pass');
    echo ($parsedBody['user']);
    echo ($parsedBody['pass']);
    $contador = 0;
    $contadord = 0;

    if($user=$this->db->login()->select([user:$parsedBody['user'], pass:$parsedBody['pass']])){
    	
    }

    foreach ($login as $log) {
    	$contador++;
		if (strcmp($log['user'], $parsedBody['user']) == 0) {
			echo $log['user'].' se encuentra en la base de datos';
			break;
		}
    }
    foreach ($passw as $pas) {
    	$contadord++; 
    	if ($contadord == $contador) {
    		if (strcmp($pas['pass'], $parsedBody['pass']) == 0) {
				echo $log['pass'].' la contraseña es correcta';
			}
    	}
    }
    echo("Usuario: ".$parsedBody['user']."</br>"."Pass: ".$parsedBody['pass']);
    die();
})->setName('login');

$app->any('/visitas/visitas_reg', function ($request, $response, $args) {
    return $this->view->render($response, '/visitas/visitas.html');
})->setName('visitas');


$app->any('/visitas/registro', function ($request, $response, $args) {
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
	$fecha = getdate();
	$fecha_visita=$fecha['year']."-".$fecha['mon']."-".$fecha['mday'];
	$data['fecha']=$fecha_visita;

	
	echo "<p>hola</p>";
	var_dump($data);
	echo "<p>hola</p>";
	var_dump($fecha_visita);
	echo "<p>hola</p>";
	var_dump($fecha);
	$registro()->insert($data);
	die();	
})->setName('visitas_reg');


$app->any('/inicio/asistencia_reg', function ($request, $response, $args) {
	$parsedBody = $request->getParsedBody();
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

$app->any('/voluntarios/voluntarios_reg', function ($request, $response, $args) {
    return $this->view->render($response, '/voluntarios/voluntarios.html');
})->setName('voluntarios');

$app->any('/voluntarios/registro', function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    //$vol_reg = $this->db->reg_vol();
    var_dump($parsedBody);
    echo ($parsedBody['nombre'] ." hello word");
    
   /* $data['nombre']=$parsedBody['nombre'];
    $data['apellido']=$parsedBody['apellido'];
    $data['cedula']=$parsedBody['ced'];
    $vol_reg->insert($data);*/
    die();
})->setName('voluntarios_reg');


