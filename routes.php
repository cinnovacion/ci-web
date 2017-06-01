  <?php
// Archivo de rutas de la aplicacion y controlador con api's

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
date_default_timezone_set("America/Managua"); 

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
                return $response->withRedirect('/voluntarios/lista');

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
        $idpersona = $parsedBody['idpersona'];

        $asistencia = $this->db->asistencia->where(array('persona_idpersona' => $idpersona, 'hora_acumulada' => 0))->fetch();
        /*echo $asistencia;
        $asistencia->fetch();*/
        if ($asistencia){
            echo "Entramos a agregar salida";
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
            $datos_asis['persona_idpersona'] = $idpersona;
            $datos_asis['fecha'] = date("Y-m-d", time());

            $registrando_asis->insert($datos_asis);
        }  

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
        return $this->view->render($response, '/visitas/mas.html',['visita' => $visita]);
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

    $this->any('/conf', function ($request, $response, $args) {
        return $this->view->render($response, '/visitas/conf.html');
    })->setName('visitas');
});
//Grupo de rutas de Voluntarios
$app->group('/voluntarios', function () {

    $this->any('/asis_dia', function ($request, $response, $args) {
        $voluntario=$this->db->voluntario->select('persona_idpersona');
        $lista=$this->db->persona->select('nombre', 'apellido')->where('idpersona',$voluntario);

        $today = date('Y-m-d', time());

        $asistencia=$this->db->asistencia->select('fecha', 'hora_entrada', 'hora_salida', 'hora_acumulada')->where(array('persona_idpersona' => $voluntario, 'fecha' => $today));

        $todo[] = 0;
        foreach ($lista as $key => $value) {
            $todo['datos'][$key]['nombre'] = $value['nombre'];
            $todo['datos'][$key]['apellido'] = $value['apellido'];
        }
        foreach ($asistencia as $key => $value) {
            $todo['datos'][$key]['hora_entrada'] = $value['hora_entrada'];
            $todo['datos'][$key]['hora_salida'] = $value['hora_salida'];
            $todo['datos'][$key]['hora_acumulada'] = $value['hora_acumulada'];
        }

        return $this->view->render($response, '/voluntarios/asisxdia.html', $todo);
    })->setName('voluntarios_asistencia_dia');

    $this->any('/lista_asis_semana', function($request, $response, $args){
        $mes=5;
        $anio=2017;
        /*echo date(W,mktime(0,0,0,$mes,date(t, mktime(0,0,0,$mes,1,$anio)),$anio))-date(W,mktime(0,0,0,$mes,1,$anio));
        die();*/
        /**
         * Función para saber el numero de semanas que tiene un mes dado
         * Tiene que recibir el año y mes
         * Devuelve un array con el numero de la primera semana y la ultima
         */
        function semanasMes($year,$month)
        /*$fmin = $this->db->asistencia()->min('hora_entrada');
        echo $fmin;*/
        {
            # Obtenemos el ultimo dia del mes
            $ultimoDiaMes=date("t",mktime(0,0,0,$month,1,$year));
         
            # Obtenemos la semana del primer dia del mes
            $primeraSemana=date("W",mktime(0,0,0,$month,1,$year));
         
            # Obtenemos la semana del ultimo dia del mes
            $ultimaSemana=date("W",mktime(0,0,0,$month,$ultimoDiaMes,$year));
         
            # Devolvemos en un array los dos valores
            return array($primeraSemana,$ultimaSemana);
        }
         
        $year=2017;
        for($i=1;$i<=12;$i++)
        {
            list($primeraSemana,$ultimaSemana)=semanasMes($year,$i);
         
            echo "<br>Mes: ".$i."/".$year." - Primera semana: ".$primeraSemana." - Ultima semana: ".$ultimaSemana;
        }
        return $this->view->render($response, '/voluntarios/listaxtiempo.html',['lis_vol'=>$buscar]);
    })->setName('asisxinter');

    //Formulario de Registro
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
        return $response->withRedirect('/voluntarios/voluntarios_reg');
    })->setName('agr_org');

    $this->any('/agr_carr', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $carr = $this->db->carrera();
        $carr_i['nombre'] = $parsedBody['nombre_carr'];
        $carr -> insert($carr_i);
        return $response->withRedirect('/voluntarios/voluntarios_reg');
        die();
    })->setName('agr_carr');

    $this->any('/agr_area', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $area = $this->db->area();
        $area_i['nombre'] = $parsedBody['nombre_area'];
        $area_i['descripcion'] = $parsedBody['desc_area'];
        $area -> insert($area_i);
       return $response->withRedirect('/voluntarios/voluntarios_reg');
        die();
    })->setName('agr_area');

    $this->any('/agr_activ', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $activ = $this->db->actividades();
        $activ_i['nombre'] = $parsedBody['nombre_activ'];
        $activ_i['descripcion'] = $parsedBody['desc_activ'];
        $activ_i['area_idarea'] = $parsedBody['activ_area'];
        $activ -> insert($activ_i);
        return $response->withRedirect('/voluntarios/voluntarios_reg');
        die();
    })->setName('agr_activ');
    //Registrar Voluntarios
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
        return $response->withRedirect('/voluntarios/lista');
        die();
    })->setName('voluntarios_reg');

    //Mostrar lista de voluntarios registrados
    $this->any('/lista', function ($request, $response, $args) {
        $voluntario=$this->db->voluntario()->select('persona_idpersona');
        $lista['persona']=$this->db->persona()->where('idpersona',$voluntario)->limit(20);
        $lista['voluntario']=$this->db->voluntario()->where('persona_idpersona',$voluntario)->limit(20);
        return $this->view->render($response, '/voluntarios/lista.html',$lista);
    })->setName('voluntarios_lista');

    //Busqueda de un Voluntario
    $this->any('/busqueda', function($request,$response,$args){
      $parsedBody = $request->getParsedBody();
      $voluntario=$this->db->voluntario()->select('persona_idpersona');
      $buscar = $this->db->persona()->where(array('idpersona'=>$voluntario, 'nombre LIKE ?'=>'%'.$parsedBody['buscar'].'%'));
      return $this->view->render($response, '/voluntarios/lista.html',['lis_vol'=>$buscar]);
    })->setName('voluntarios_buscar');

    //Mostrar detalles de un voluntario
    $this->any('/detalles/{id}', function ($request, $response, $args) {
        $id = $request->getAttribute('id');
        $persona = $this->db->persona()->where('idpersona',$id);
        $volun = $this->db->voluntario()->where('persona_idpersona',$id);
        $asis = $this->db->asistencia()->where('persona_idpersona',$id);
        $acum = $this->db->asistencia()->where('persona_idpersona',$id)->sum('hora_acumulada');
        $items = [$persona,$volun,$asis,$acum];
        return $this->view->render($response, '/voluntarios/detalles.html',['lis_vol'=>$items]);
    })->setName('voluntarios_detalles');

    //Editar un voluntario
    $this->any('/editar/{id}', function ($request, $response, $args) {
        $id = $request->getAttribute('id');
        $data['persona'] = $this->db->persona()->where('idpersona',$id)->fetch();
        $data['voluntario'] = $this->db->voluntario()->where('persona_idpersona',$id)->fetch();
        $data['org'] = $this->db->Universidad();
        $data['carrera'] = $this->db->carrera();
        $data['area'] = $this->db->area();
        $data['actividades'] = $this->db->actividades();
        return $this->view->render($response, '/voluntarios/editar.html', $data);
    })->setName('voluntario_editar');

    //Actualizar Voluntario
    $this->any('/actualizar/{id}', function ($request, $response, $args) {
        $id = $request->getAttribute('id');
        $parsedBody = $request->getParsedBody();

        $vol_p=$this->db->persona()->where('idpersona',$id)->fetch();
        $vol_v=$this->db->voluntario()->where('persona_idpersona',$id)->fetch();
        //guardar datos en tabla persona
        $data['nombre']=$parsedBody['nombre'];
        $data['apellido']=$parsedBody['apellido'];
        $data['cedula']=$parsedBody['ced'];
        $data['direccion']=$parsedBody['direccion'];
        $data['telefono']=$parsedBody['no_telefono'];
        $data['correo']=$parsedBody['correo'];
        $data['area_idarea']=$parsedBody['area'];
        $vol_p->update($data);

        //guardar datos en tabla voluntario
        //predeterminar la zona horaria
        date_default_timezone_set("America/Managua");
        //$data1['carnet']=time()."-".$parsedBody['ced'];
        $data1['fecha_ingreso']=strtotime($parsedBody['fecha']);
        //$persona_id=$this->db->persona()->select('idpersona')->order('idpersona desc')->limit(1)->fetch();
        //$data1['persona_idpersona']=$persona_id['idpersona'];
        $data1['Universidad_idUniversidad']=$parsedBody['org'];
        $data1['carrera_idcarrera']=$parsedBody['carrera'];
        $vol_v->update($data1);
        die();
    })->setName('voluntarios_up');
});

$app->group('/trabajador', function (){

  //Vista de Registro de Trabajador
  $this->any('/trabajador_reg', function ($request, $response, $args) {
      $parsedBody  = $response->getBody();
      $org         = $this->db->Universidad();
      $carrera     = $this->db->carrera();
      $area        = $this->db->area();
      $actividades = $this->db->actividades();
      $item = [$org,$carrera,$area,$actividades];
      return $this->view->render($response, '/trabajador/trabajador.html',['template' => $item]);
  })->setName('trabajador');

  //Agregar Area
  $this->any('/agr_area_t', function ($request, $response, $args) {
      $parsedBody = $request->getParsedBody();
      $area = $this->db->area();
      $area_i['nombre'] = $parsedBody['nombre_area'];
      $area_i['descripcion'] = $parsedBody['desc_area'];
      $area -> insert($area_i);
      return $response->withRedirect('/trabajador/trabajador_reg');
      die();
  })->setName('agr_area_t');

  //Registrar Trabajador
  $this->any('/registro', function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();

            $trab_p=$this->db->persona();
            $trab_t=$this->db->trabajador();
            //guardar datos en tabla persona
            $data['nombre']=$parsedBody['nombre'];
            $data['apellido']=$parsedBody['apellido'];
            $data['cedula']=$parsedBody['ced'];
            $data['direccion']=$parsedBody['direccion'];
            $data['telefono']=$parsedBody['no_telefono'];
            $data['correo']=$parsedBody['correo'];
            $data['area_idarea']=$parsedBody['area'];
            $trab_p->insert($data);

            //guardar datos en tabla trabajador
            //predeterminar la zona horaria
            //date_default_timezone_set("America/Managua");
            $data1['carnet'] = $parsedBody['carnet'];
            $data1['cargo']=$parsedBody['cargo'];
            $persona_id=$this->db->persona()->select('idpersona')->order('idpersona desc')->limit(1)->fetch();
            $data1['persona_idpersona']=$persona_id['idpersona'];
            $trab_t->insert($data1);
            return $response->withRedirect('/trabajador/lista');
            die();
  })->setName('trabajador_reg');

  //Lista de Trabajadores
  $this->any('/lista', function ($request, $response, $args) {
      $trabajador=$this->db->trabajador()->select('persona_idpersona');
      $lista=$this->db->persona()->where('idpersona',$trabajador)->limit(20);
      return $this->view->render($response, '/trabajador/lista.html',['lis_trab'=>$lista]);
  })->setName('trabajador_lista');

  //Busqueda de un Trabjador
  $this->any('/busqueda', function($request,$response,$args){
    $parsedBody = $request->getParsedBody();
    $trabajador=$this->db->trabajador()->select('persona_idpersona');
    $buscar = $this->db->persona()->where(array('idpersona'=>$trabajador, 'nombre LIKE ?'=>'%'.$parsedBody['buscar'].'%'));
    echo $buscar; die();
    return $this->view->render($response, '/trabajador/lista.html',['lis_trab'=>$buscar]);
  })->setName('trabajador_buscar');

  //Mostrar detalles de un trabajador
  $this->any('/detalles/{id}', function ($request, $response, $args) {
      $id = $request->getAttribute('id');
      $persona = $this->db->persona()->where('idpersona',$id);
      $trab = $this->db->trabajador()->where('persona_idpersona',$id);
      $asis = $this->db->asistencia()->where('persona_idpersona',$id);
      $items = [$persona,$trab,$asis];
      return $this->view->render($response, '/trabajador/detalles.html',['lis_trab'=>$items]);
  })->setName('trabajador_detalles');

  //Editar un trabajador
  $this->any('/editar/{id}', function ($request, $response, $args) {
      $id = $request->getAttribute('id');
      $data['persona'] = $this->db->persona()->where('idpersona',$id)->fetch();
      $data['trabajador'] = $this->db->trabajador()->where('persona_idpersona',$id)->fetch();
      $data['area'] = $this->db->area();
      return $this->view->render($response, '/trabajador/editar.html', $data);
  })->setName('trabajador_editar');

  $this->any('/actualizar/{id}', function ($request, $response, $args) {
    $id = $request->getAttribute('id');
    $parsedBody = $request->getParsedBody();

            $trab_p=$this->db->persona()->where('idpersona',$id)->fetch();
            $trab_t=$this->db->trabajador()->where('persona_idpersona',$id)->fetch();
            //guardar datos en tabla persona
            $data['nombre']=$parsedBody['nombre'];
            $data['apellido']=$parsedBody['apellido'];
            $data['cedula']=$parsedBody['ced'];
            $data['direccion']=$parsedBody['direccion'];
            $data['telefono']=$parsedBody['no_telefono'];
            $data['correo']=$parsedBody['correo'];
            $data['area_idarea']=$parsedBody['area'];
            $trab_p->update($data);

            //guardar datos en tabla trabajador
            //predeterminar la zona horaria
            //date_default_timezone_set("America/Managua");
            $data1['carnet'] = $parsedBody['carnet'];
            $data1['cargo']=$parsedBody['cargo'];
            $persona_id=$this->db->persona()->select('idpersona')->order('idpersona desc')->limit(1)->fetch();
            //$data1['persona_idpersona']=$persona_id['idpersona'];
            $trab_t->update($data1);
            return $response->withRedirect('/trabajador/lista');
            die();
  })->setName('actualizar_trab');

});

$app->group('/ajax', function () {
    // Verificar si la cedula le pertenece a una persona
    $this->any('/cedula', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
         //tomamos a una persona según cedula
        if($persona = $this->db->persona()->where('cedula', $parsedBody['cedula'])->fetch())
            echo '<div class="row">
                <div class="column column-50 column-offset-25 alto">
                    <div class="centrar">
                        <div id="vald">
                            <h3 style="margin-top:5em; color:white;">Tu eres ' . $persona['nombre'] . ' ' . $persona['apellido'] . '?</h3>
                                <form method="POST" action="/inicio/asistencia_reg">
                                <button class="inverso" type="submit">Si</button>
                                <a class="button inverso" href="/" style="margin-left:50px;">No</a>
                                <input type="Hidden" name="idpersona" value="' . $persona['idpersona'] . '">
                            </form>
                        </div>
                    </div>
                </div>
            </div>';
        else echo NULL;
        return TRUE;
    });
});
