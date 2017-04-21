<?php
// Archivo de rutas de la aplicacion y controlador con api's

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->any('/', function ($request, $response, $args) {
    return $this->view->render($response, 'inicio.html');
})->setName('inicio');

// Ruta para iniciar sesion como administrador
$app->group('/inicio', function () {
    $this->any('/login', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $user    = $parsedBody['user'];
        $contra  = $parsedBody['pass'];

        //verificando si el usuario y cedula existen
        //$prueba = $this->db->admin->where(array('usuario' => $user, 'password' =>$contra))->fetch();

        //verificando usuario y si es correcto verificar contraseña
        $prueba = $this->db->admin->select('password')->where('usuario', $user)->fetch();
        if ($prueba) {
            echo "El usuario es correcto";
            if (strcmp($prueba['password'], $contra) == 0) {
                echo "La contraseña tambien es correcta";
            }else{
                echo "La contraseña no es correcta";
            }
        }else{
            echo "El usuario es incorrecto";
        }

    })->setName('login');

    // Registro de asistencia -- Voluntarios y Trabajadores --
    $this->any('/asistencia_reg', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        date_default_timezone_set("America/Managua");

        if ($parsedBody["ced-vol"]) {
            //tomamos a una persona segun cedula
            $persona = $this->db->persona()->where('cedula',$parsedBody["ced-vol"])->fetch();
            if ($persona) {
                $asistencia = $this->db->asistencia->where(array('persona_idpersona' => $persona['idpersona'], 'hora_acumulada' => 0))->fetch();
                if ($asistencia){
                    $salida = time();
                    $acumuladas = $salida - $asistencia['hora_entrada'];
                    $asistencia->update(
                        array("hora_acumulada" => $acumuladas,
                        "hora_salida" => $salida
                    ));
                }else{
                    echo "No lo encuentra, tenemos que agregar la asistencia para ese dia";
                    $registrando_asis = $this->db->asistencia();

                    $datos_asis['hora_entrada'] = time();
                    $datos_asis['persona_idpersona'] = $persona['idpersona'];

                    $registrando_asis->insert($datos_asis);
                }
            }else{
                echo "No lo encuentra";
                // Se le debe decir al usuario que la cedula que ingreso no se encuentra registrada
                // para que escriba una correcta.
            }
        }elseif ($parsedBody["ced-tbj"]) {
            $persona = $this->db->persona()->where('cedula',$parsedBody["ced-tbj"])->fetch(); //tomamos a una persona segun cedula
            if ($persona) {
                $asistencia = $this->db->asistencia->where(array('persona_idpersona' => $persona['idpersona'], 'hora_acumulada' => 0))->fetch();
                if ($asistencia){

                    $salida = time();
                    $acumuladas = $salida - $asistencia['hora_entrada'];

                    $asistencia->update(
                        array("hora_acumulada" => $acumuladas,
                        "hora_salida" => $salida
                    ));
                }else{
                    echo "No lo encuentra, tenemos que agregar la asistencia para ese dia";
                    $registrando_asis = $this->db->asistencia();

                    $datos_asis['hora_entrada'] = time();
                    $datos_asis['persona_idpersona'] = $persona['idpersona'];

                    $registrando_asis->insert($datos_asis);
                }
            }else{
                echo "No lo encuentra";
                // Se le debe decir al usuario que la cedula que ingreso no se encuentra registrada
                // para que escriba una correcta.
            }
        }

        die();
    })->setName('asistencia');
});

//Mostrar pagina de visitas
$app->group('/visitas', function () {
    //
    $this->any('/visitas_reg', function ($request, $response, $args) {
        return $this->view->render($response, '/visitas/visitas.html');
    })->setName('visitas');

    //Mostrar lista de visitas
    $this->any('/lista', function ($request, $response, $args) {
        $lista= $this->db->visita();
        return $this->view->render($response, '/visitas/lista.html',['lis_vis'=>$lista]);
    })->setName('visitas_lista');

    //Mostrar mas detalles de una visita
    $this->any('/mas/{id}', function ($request, $response, $args) {
        $id = $request->getAttribute('id');

        $visita = $this->db->visita()->where('idvisita',$id);
        $fecha['fecha'] = $visita['fecha'];
        return $this->view->render($response, '/visitas/mas.html',['lis_vis'=>$visita]);

        $visita = $this->db->visita()->where('idvisita',$id)->fetch();
        $fecha['fecha'] = date("d-m-Y",$visita['fecha']);
        $fecha['llegada'] = date("H:i:s",$visita['hora_llegada']);
        $fecha['salida'] = date("H:i:s",$visita['hora_salida']);
        $item = [$visita,$fecha];
        return $this->view->render($response, '/visitas/mas.html',['template' => $visita]);

    })->setName('visitas_mas');

    //Realizar registro de una visita
    $this->any('/registro', function ($request, $response, $args) {
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
        var_dump($fecha);
        $visita()->insert($data);
        die();
    })->setName('visitas_reg');
});

$app->group('/voluntarios', function () {
    $this->any('/voluntarios_reg', function ($request, $response, $args) {
        $parsedBody  = $response->getBody();
        $org         = $this->db->Universidad();
        $carrera     = $this->db->carrera();
        $area        = $this->db->area();
        $actividades = $this->db->actividades();
        $item = [$org,$carrera,$area,$actividades];
        return $this->view->render($response, '/voluntarios/voluntarios.html',['template' => $item]);
    })->setName('voluntarios');

    //Agregar datos a listas deplegables
    $this->any('/agr_org', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $org = $this->db->Universidad();
        $org_i['nombre'] = $parsedBody['nombre_org'];
        $org -> insert($org_i);
        var_dump($org_i);
        var_dump($parsedBody);
    })->setName('agr_org');

    $this->any('/agr_carr', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $carr = $this->db->carrera();
        $carr_i['nombre'] = $parsedBody['nombre_carr'];
        $carr -> insert($carr_i);
        var_dump($carr_i);
        var_dump($parsedBody);
        die();
    })->setName('agr_carr');

    $this->any('/agr_area', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $area = $this->db->area();
        $area_i['nombre'] = $parsedBody['nombre_area'];
        $area_i['descripcion'] = $parsedBody['desc_area'];
        $area -> insert($area_i);
        var_dump($area_i);
        var_dump($parsedBody);
        die();
    })->setName('agr_area');

    $this->any('/agr_activ', function ($request, $response, $args) {
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

    $this->any('/registro', function ($request, $response, $args) {
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

    //Mostrar lista de voluntarios registrados
    $this->any('/lista', function ($request, $response, $args) {
        $voluntario=$this->db->voluntario()->select('persona_idpersona');
        $lista=$this->db->persona()->where('idpersona',$voluntario)->limit(20);
        return $this->view->render($response, '/voluntarios/lista.html',['lis_vol'=>$lista]);
    })->setName('voluntarios_lista');

    //Mostrar detalles de un voluntario
    $this->any('/detalles/{id}', function ($request, $response, $args) {
        $id = $request->getAttribute('id');
        $persona = $this->db->persona()->where('idpersona',$id);
        $volun = $this->db->voluntario()->where('persona_idpersona',$id);
        $items = [$persona,$volun];
        return $this->view->render($response, '/voluntarios/detalles.html',['lis_vol'=>$items]);
    })->setName('voluntarios_detalles');
});

$app->group('/ajax', function () {

});
