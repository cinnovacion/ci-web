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
    $login=$this->db->admin()->select('usuario');
    $passw=$this->db->admin()->select('password');
    echo ($parsedBody['user']);
    echo ($parsedBody['pass']);
    $contador = 0;
    $contadord = 0;

    //if($user=$this->db->login()->select([user:$parsedBody['user'],pass:$parsedBody['pass']])){}


    foreach ($login as $log) {
    	$contador++;
		if (strcmp($log['usuario'], $parsedBody['user']) == 0) {
			echo $log['usuario'].' se encuentra en la base de datos';
			break;
		}
    }
    foreach ($passw as $pas) {
    	$contadord++;
    	if ($contadord == $contador) {
    		if (strcmp($pas['password'], $parsedBody['pass']) == 0) {
				echo $log['password'].' la contraseña es correcta';
			}
    	}
    }
    echo("Usuario: ".$parsedBody['user']."</br>"."Pass: ".$parsedBody['pass']);
    //echo("Usuario: ".$parsedBody['user']."</br>"."Pass: ".$parsedBody['pass']);
    die();
})->setName('login');
//Mostrar pagina de visitas
$app->any('/visitas/visitas_reg', function ($request, $response, $args) {
    return $this->view->render($response, '/visitas/visitas.html');
})->setName('visitas');
//Agregar motivo visita
/*$app->any('/voluntarios/agr_carr', function ($request, $response, $args) {
	$parsedBody = $request->getParsedBody();
    $carr = $this->db->();
    $carr_i['nombre'] = $parsedBody['nombre_carr'];
    $carr -> insert($carr_i);
    var_dump($carr_i);
    var_dump($parsedBody);
    die();
})->setName('agr_carr');*/
//Mostrar lista de visitas
$app->any('/visitas/lista', function ($request, $response, $args) {
  $lista= $this->db->visita();
  return $this->view->render($response, '/visitas/lista.html',['lis_vis'=>$lista]);
})->setName('visitas_lista');
//Mostrar mas detalles de una visita
$app->any('/visitas/mas', function ($request, $response, $args) {
  $parsedBody = $request->getParsedBody();
  echo($parsedBody["cedu"]);
  $persona = $this->db->persona()->where('cedula',$parsedBody["cedu"]);
  return $this->view->render($response, '/visitas/mas.html',['lis_vis'=>$lista]);
})->setName('visitas_mas');

//Realizar registro de una visita
$app->any('/visitas/registro', function ($request, $response, $args) {
	$parsedBody = $request->getParsedBody();
	$visita=$this->db->visita();
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
	//fecha de visita
	//formato para interpretar fechas = $fecha=date("d-m-Y", time());
	//predeterminar la zona horaria
	date_default_timezone_set("America/Managua");
	$data['fecha']=time();
	$data['hora_llegada']=time();
	$data['hora_salida']=strtotime($parsedBody['hora_salida']);
	//insertar en la base de datos
	$visita()->insert($data);
	die();
})->setName('visitas_reg');


$app->any('/inicio/asistencia_reg', function ($request, $response, $args) {
	$parsedBody = $request->getParsedBody();
	date_default_timezone_set("America/Managua");
	//$asistencia $this->db->asistencia->select();

	//echo time()."<br>"; 
	//echo date("d-m-Y", time())."<br>"; die();
	if ($parsedBody["ced-vol"]) { 
		$persona = $this->db->persona()->where('cedula',$parsedBody["ced-vol"]); //tomamos a una persona segun cedula
		$data = $persona->fetch();

		$asistencia = $this->db->asistencia->where(array('persona_idpersona' => $data['idpersona'], 'hora_acumulada' => 0))->fetch();
		if ($asistencia){
			$dato = $asistencia;

			$salida = time();
			$acumuladas = time() - $dato['hora_entrada'];
			
			$dato['hora_salida'] = $salida;
			$dato['hora_acumulada'] = $acumuladas; 
			
			$asistencia->update(
				array("hora_acumulada" => time() - $dato['hora_entrada'],
					  "hora_salida" => time()
					)
				);

		}else{
			echo "No lo encuentra, tenemos que agregar la asistencia para ese dia";
			$registrando_asis = $this->db->asistencia();

			$datos_asis['hora_entrada'] = time();
			$datos_asis['persona_idpersona'] = $data['idpersona'];

			$registrando_asis->insert($datos_asis);
		}
	}elseif ($parsedBody["ced-tbj"]) {
		$persona = $this->db->persona()->where('cedula',$parsedBody["ced-tbj"]); //tomamos a una persona segun cedula
		$data = $persona->fetch();

		$asistencia = $this->db->asistencia->where(array('persona_idpersona' => $data['idpersona'], 'hora_acumulada' => 0))->fetch();
		if ($asistencia){
			$dato = $asistencia;

			$salida = time();
			$acumuladas = time() - $dato['hora_entrada'];
			
			$dato['hora_salida'] = $salida;
			$dato['hora_acumulada'] = $acumuladas; 
			
			$asistencia->update(
				array("hora_acumulada" => time() - $dato['hora_entrada'],
					  "hora_salida" => time()
					)
				);

		}else{
			echo "No lo encuentra, tenemos que agregar la asistencia para ese dia";
			$registrando_asis = $this->db->asistencia();

			$datos_asis['hora_entrada'] = time();
			$datos_asis['persona_idpersona'] = $data['idpersona'];

			$registrando_asis->insert($datos_asis);
		}
	}

	die();
})->setName('asistencia');

$app->any('/voluntarios/voluntarios_reg', function ($request, $response, $args) {
	$parsedBody = $response->getBody();
    $org = $this->db->Universidad();
    $carrera = $this->db->carrera();
    $area = $this->db->area();
    $actividades = $this->db->actividades();
    $item = [$org,$carrera,$area,$actividades];
    return $this->view->render($response, '/voluntarios/voluntarios.html',['template' => $item]);
})->setName('voluntarios');
//Agregar datos a listas deplegables
$app->any('/voluntarios/agr_org', function ($request, $response, $args) {
	$parsedBody = $request->getParsedBody();
    $org = $this->db->Universidad();
    $org_i['nombre'] = $parsedBody['nombre_org'];
    $org -> insert($org_i);
    var_dump($org_i);
    var_dump($parsedBody);
})->setName('agr_org');

$app->any('/voluntarios/agr_carr', function ($request, $response, $args) {
	$parsedBody = $request->getParsedBody();
    $carr = $this->db->carrera();
    $carr_i['nombre'] = $parsedBody['nombre_carr'];
    $carr -> insert($carr_i);
    var_dump($carr_i);
    var_dump($parsedBody);
    die();
})->setName('agr_carr');

$app->any('/voluntarios/agr_area', function ($request, $response, $args) {
	$parsedBody = $request->getParsedBody();
    $area = $this->db->area();
    $area_i['nombre'] = $parsedBody['nombre_area'];
    $area_i['descripcion'] = $parsedBody['desc_area'];
    $area -> insert($area_i);
    var_dump($area_i);
    var_dump($parsedBody);
    die();
})->setName('agr_area');

$app->any('/voluntarios/agr_activ', function ($request, $response, $args) {
	$parsedBody = $request->getParsedBody();
    $activ = $this->db->actividades();
    $activ_i['nombre'] = $parsedBody['nombre_activ'];
    $activ_i['descripcion'] = $parsedBody['desc_activ'];
    $activ_i['area_idarea'] = $parsedBody['activ_area'];
    $activ -> insert($activ_i);
    var_dump($activ_i);
    var_dump($parsedBody);
    die();
})->setName('agr_activ');

$app->any('/voluntarios/registro', function ($request, $response, $args) {	 
	$parsedBody = $request->getParsedBody();

	$vol_p=$this->db->persona();
	$vol_v=$this->db->voluntario();
    //guardar datos en tabla persona
    $data['nombre']=$parsedBody['nombre'];
    $data['apellido']=$parsedBody['apellido'];
    $data['cedula']=$parsedBody['ced'];
    $data['direccion']=$parsedBody['direccion'];
    $data['telefono']=$parsedBody['no_telefono'];
    $data['correo']=$parsedBody['correo'];
    $data['area_idarea']=$parsedBody['area'];
    $vol_p->insert($data);

    //guardar datos en tabla voluntario
    //predeterminar la zona horaria
    date_default_timezone_set("America/Managua");
    $data1['carnet']=time()."-".$parsedBody['ced'];
    $data1['fecha_ingreso']=strtotime($parsedBody['fecha']);
    $persona_id=$this->db->persona()->select('idpersona')->order('idpersona desc')->limit(1)->fetch();
    echo json_encode($persona_id['idpersona']);
    $data1['persona_idpersona']=$persona_id['idpersona'];
    $data1['Universidad_idUniversidad']=$parsedBody['org'];
    $data1['carrera_idcarrera']=$parsedBody['carrera'];
    echo "<p>datos de voluntario</p>";
    var_dump($data1);
    $vol_v->insert($data1);
    die();
})->setName('voluntarios_reg');

$app->any('/voluntarios/lista', function ($request, $response, $args) {
$parsedBody = $response->getBody();
$voluntario=$this->db->voluntario()->select('persona_idpersona');
$lista=$this->db->persona()->where('idpersona',$voluntario);
return $this->view->render($response, '/voluntarios/lista.html',['lis_vol'=>$lista]);
})->setName('voluntarios_lista');


$app->any('/voluntarios/lista/mas', function ($request, $response, $args) {
$lista= $this->db->persona();
$parsedBody=$this->getParsedBody();
var_dump($parsedBody);
})->setName('voluntarios_lista');


$app->any('/voluntarios/lista/detalles', function ($request, $response, $args) {
$lista= $this->db->persona();
return $this->view->render($response, '/voluntarios/mas.html',['lis_vol'=>$lista]);
})->setName('voluntarios_detalles');