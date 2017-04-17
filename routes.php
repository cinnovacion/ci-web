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
	//$asistencia $this->db->asistencia->select();

	//echo time()."<br>"; 
	//echo date("d-m-Y", time())."<br>"; die();
	if ($parsedBody["ced-vol"]) { 
		$persona = $this->db->persona()->where('cedula',$parsedBody["ced-vol"]); //tomamos a una persona segun cedula
		$data = $persona->fetch();
		//echo $data['idpersona']; die();

		//$asistencia = $this->db->asistencia->where('persona_idpersona = '.$data['idpersona'].' AND hora_entrada != NULL')->fetch();
		if ($asistencia = $this->db->asistencia->where('persona_idpersona = '.$data['idpersona'].' AND hora_entrada <> NULL')->fetch()){
			echo $asistencia;
			echo "Encontro el ID con la FECHA de hoy, entonces vamos a anotar la hora de salida";

		}else{
			echo "No lo encuentra, tenemos que agregar la asistencia para ese dia";
			$registrando_asis = $this->db->asistencia();

			$datos_asis['hora_entrada'] = time();
			$datos_asis['persona_idpersona'] = $data['idpersona'];

			$registrando_asis->insert($datos_asis);

			echo "Se hizo el insert";
	}

	}elseif ($parsedBody["ced-tbj"]) {
		$persona = $this->db->persona()->where('cedula',$parsedBody["ced-tbj"]);
		if($data = $persona->fetch()){
			echo json_encode(array(
				'idpersona' => $data['idpersona'],
				'nombre' => $data['nombre'],
				'apellido' => $data['apellido'],
				'cedula' => $data['cedula'],
				'dirección' => $data['dirección'],
				'teléfono' => $data['teléfono'],
				'correo' => $data['correo'],
			));
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

$app->post('/voluntarios/registro', function ($request, $response, $args) {	 $parsedBody = $request->getParsedBody();

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
$lista= $this->db->voluntario()->select("voluntario.*, persona.nombre, table2.apellido");
/*$cantidad=$this->db->voluntario()->count("*");
for ($i=1; $i <=$cantidad ; $i++) { 
$lista_r= $this->db->voluntario()->where('idvoluntario',$i);
$lista_f=$lista[$i];
}*/
/*
$voluntario=$this->db->voluntario()->select('persona_idpersona');
    $cantidad=$this->db->voluntario()->count("*");
*/
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