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
        if ($asistencia){
            //Entramos a agregar salida
            $salida = time();
            $acumuladas = date('H.is',$salida) - date('H.is',$asistencia['hora_entrada']); ///
            if (date('H.is',$salida)>12 && $acumuladas>1)
            {
              $data['hora_acumulada'] = $data['hora_acumulada']-1;
            }
            $asistencia->update(
                array("hora_acumulada" => $acumuladas,
                "hora_salida" => $salida
            ));
        }else{
            //No lo encuentra, tenemos que agregar la asistencia para ese dia
            $registrando_asis = $this->db->asistencia();
            $today = date('Y-m-d', time());
            $datos_asis['fecha'] = $today;
            $datos_asis['hora_entrada'] = time();
            $datos_asis['persona_idpersona'] = $idpersona;
            $datos_asis['hora_acumulada']=0;

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
        $lista['visita']= $this->db->visita();
        $id=$this->db->visita()->select('persona_idpersona')->fetch();
        $lista['persona']=$this->db->persona()->where('idpersona',$id['persona_idpersona']);
        return $this->view->render($response, '/visitas/lista.html',$lista);
    })->setName('visitas_lista');

    //Mostrar mas detalles de una visita
    $this->any('/mas/{id}', function ($request, $response, $args) {
        $id = $request->getAttribute('id');
        $idpersona=$this->db->visita()->select('persona_idpersona')->where('idvisita',$id)->fetch();
        $mas['persona']=$this->db->persona()->where('idpersona',$idpersona['persona_idpersona']);
        $mas['visita'] = $this->db->visita()->where('idvisita',$id);
        return $this->view->render($response, '/visitas/mas.html',$mas);
    })->setName('visitas_mas');

    //Realizar registro de una visita
    $this->any('/registro', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $visita=$this->db->visita();
        $persona=$this->db->persona();
        //organizando los datos en un nuevo arreglo
        var_dump($parsedBody);

        $data['nombre']=$parsedBody['nombre'];
        $data['apellido']=$parsedBody['apellido'];
        $data['cedula']=$parsedBody['ced'];
        $persona->insert($data);
        $data1['placa']=$parsedBody['no_placa'];
        $data1['org']=$parsedBody['org_vi'];
        $data1['tipo_visita']=$parsedBody['tipo_visita'];

        /*Condicion para almacenar correctamente el motivo segun el tipo de visita*/
        if ($parsedBody['tipo_visita']=='Externa'){
            $data1['motivo']=$parsedBody['motivo_ext'];
        }
        else if ($parsedBody['tipo_visita']=='Interna')
        {
            $data1['motivo']=$parsedBody['motivo_int'];
        }
        //fecha de visita
        //formato para interpretar fechas = $fecha=date("d-m-Y", time());
        //predeterminar la zona horaria
        date_default_timezone_set("America/Managua");
        $data1['fecha']=time();
        $data1['hora_llegada']=time();
        $data1['hora_salida']=strtotime($parsedBody['hora_salida']);
        $persona_id=$this->db->persona()->select('idpersona')->order('idpersona desc')->limit(1)->fetch();
        $data1['persona_idpersona']=$persona_id['idpersona'];
        //insertar en la base de datos
        var_dump($fecha);
        $visita()->insert($data1);
        die();
    })->setName('visitas_reg');
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
        $org         = $this->db->institucion();
        $carrera     = $this->db->carrera();
        $actividades = $this->db->actividades();
        $item = [$org,$carrera,$actividades];
        return $this->view->render($response, '/voluntarios/voluntarios.html',['template' => $item]);
    })->setName('voluntarios');

    //Agregar datos a listas deplegables
    $this->any('/agr_org', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $org = $this->db->institucion();
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

    //Registrar Voluntarios
    $this->any('/registro', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();

        $vol_p=$this->db->persona();
        $vol_v=$this->db->voluntario();
        $vol_a=$this->db->voluntario_has_actividades();
        //guardar datos en tabla persona
        $data['nombre']=$parsedBody['nombre'];
        $data['apellido']=$parsedBody['apellido'];
        $data['cedula']=$parsedBody['ced'];
        $data['direccion']=$parsedBody['direccion'];
        $data['telefono']=$parsedBody['no_telefono'];
        $data['correo']=$parsedBody['correo'];
        $vol_p->insert($data);

        //guardar datos en tabla voluntario
        //predeterminar la zona horaria
        date_default_timezone_set("America/Managua");
        $data1['carnet']=time()."-".$parsedBody['ced'];
        $data1['area'] = $parsedBody['area'];
        $data1['fecha_ingreso']=strtotime($parsedBody['fecha']);
        $persona_id=$this->db->persona()->select('idpersona')->order('idpersona desc')->limit(1)->fetch();
        $data1['persona_idpersona']=$persona_id['idpersona'];
        $data1['institucion_idinstitucion']=$parsedBody['org'];
        $data1['carrera_idcarrera']=$parsedBody['carrera'];
        $vol_v->insert($data1);

        $voluntario_id=$this->db->voluntario()->select('idvoluntario')->order('idvoluntario desc')->limit(1)->fetch();
        $actividades_id=$this->db->actividades()->select('idactividades')->where('area',$parsedBody['area']);
        for ($i=1; $i <= count($actividades_id) ; $i++) {
          $data2['voluntario_idvoluntario']=$voluntario_id['idvoluntario'];
          $data2['actividades_idactividades'] = $actividades_id[$i]['idactividades'];
          $vol_a->insert($data2);
        }
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
      $buscar['persona'] = $this->db->persona()->where(array('idpersona'=>$voluntario, 'nombre LIKE ?'=>'%'.$parsedBody['buscar'].'%'));
      return $this->view->render($response, '/voluntarios/lista.html',$buscar);
    })->setName('voluntarios_buscar');

    //Mostrar detalles de un voluntario
    $this->any('/detalles/{id}', function ($request, $response, $args) {
        $id = $request->getAttribute('id');
        $lista['persona'] = $this->db->persona()->where('idpersona',$id);
        $lista['volun'] = $this->db->voluntario()->where('persona_idpersona',$id);
        $id_vol = $this->db->voluntario()->select('idvoluntario')->where('persona_idpersona',$id)->fetch();
        $lista['asis'] = $this->db->asistencia()->where('persona_idpersona',$id);
        $lista['acum'] = $this->db->asistencia()->where('persona_idpersona',$id)->sum('hora_acumulada');
        $act_id = $this->db->voluntario_has_actividades()->select('actividades_idactividades')->where('voluntario_idvoluntario',$id_vol);
        for ($i=0; $i <count($act_id) ; $i++) {
          $idactiv[$i] = $act_id[$i]['actividades_idactividades'];
        }
        $lista['area'] = $this->db->actividades()->where('idactividades',$idactiv);
        /*echo json_encode($lista['area']); die();*/
        $lista['ultima'] = $this->db->asistencia()->where('persona_idpersona',$id)->select('hora_entrada')->order('hora_entrada desc')->limit(1);
        //die();
        return $this->view->render($response, '/voluntarios/detalles.html',$lista);
    })->setName('voluntarios_detalles');

    //Editar un voluntario
    $this->any('/editar/{id}', function ($request, $response, $args) {
        $id = $request->getAttribute('id');
        $data['persona'] = $this->db->persona()->where('idpersona',$id)->fetch();
        $data['voluntario'] = $this->db->voluntario()->where('persona_idpersona',$id)->fetch();
        $data['org'] = $this->db->institucion();
        $data['carrera'] = $this->db->carrera();
        return $this->view->render($response, '/voluntarios/editar.html', $data);
    })->setName('voluntario_editar');

    //Actualizar Voluntario
    $this->any('/actualizar/{id}', function ($request, $response, $args) {
        $id = $request->getAttribute('id');
        $parsedBody = $request->getParsedBody();

        $vol_p=$this->db->persona()->where('idpersona',$id)->fetch();
        $vol_v=$this->db->voluntario()->where('persona_idpersona',$id)->fetch();
        $vol_a=$this->db->voluntario_has_actividades()->where('voluntario_idvoluntario',$vol_v['idvoluntario'])->fetch();
        //guardar datos en tabla persona
        $data['nombre']=$parsedBody['nombre'];
        $data['apellido']=$parsedBody['apellido'];
        $data['cedula']=$parsedBody['ced'];
        $data['direccion']=$parsedBody['direccion'];
        $data['telefono']=$parsedBody['no_telefono'];
        $data['correo']=$parsedBody['correo'];
        $vol_p->update($data);

        //guardar datos en tabla voluntario
        //predeterminar la zona horaria
        date_default_timezone_set("America/Managua");
        //$data1['carnet']=time()."-".$parsedBody['ced'];
        $data1['fecha_ingreso']=strtotime($parsedBody['fecha']);
        //$persona_id=$this->db->persona()->select('idpersona')->order('idpersona desc')->limit(1)->fetch();
        //$data1['persona_idpersona']=$persona_id['idpersona'];
        $data1['institucion_idinstitucion']=$parsedBody['org'];
        $data1['carrera_idcarrera']=$parsedBody['carrera'];
        $vol_v->update($data1);

        /*
        $voluntario_id=$this->db->voluntario()->select('idvoluntario')->order('idvoluntario desc')->limit(1)->fetch();
        $actividades_id=$this->db->actividades()->select('idactividades')->where('area',$parsedBody['area']);
        for ($i=1; $i <= count($actividades_id) ; $i++) {
          $data2['voluntario_idvoluntario']=$voluntario_id['idvoluntario'];
          $data2['actividades_idactividades'] = $actividades_id[$i]['idactividades'];
          $vol_a->insert($data2);
        }
        */
        $actividades_id=$this->db->actividades()->select('idactividades')->where('')->fetch();
        $data2['actividades_idactividades']=$actividades_id['idactividades'];
        $vol_a->update($data2);
        die();

    })->setName('voluntarios_up');

    //Agregar Horas
    $this->any('/hora', function ($request, $response, $args) {
        $parsedBody=$request->getParsedBody();
        $asis = $this->db->asistencia();
        $data['hora_entrada']=strtotime(''.$parsedBody['fecha'].' '.$parsedBody['entrada'].'');
        $data['hora_salida']=strtotime(''.$parsedBody['fecha'].' '.$parsedBody['salida']);
        $data['hora_acumulada']= date('H.is',$data['hora_salida'])- date('H.is',$data['hora_entrada']);
        if (date('H.is',$data['hora_salida'])>12 && $data['hora_acumulada']>1)
        {
          $data['hora_acumulada'] = $data['hora_acumulada']-1;
        }
        $data['persona_idpersona']=$parsedBody['id'];
        $asis->insert($data);
    })->setName('agregar_hora');

    //Editar horas
    $this->any('/editar_h/{id}',function($request,$response,$args){
      $id = $request->getAttribute('id');
      $asistencia = $this->db->asistencia()->where('idasistencia',$id)->fetch();
      $data['asistencia'] = $asistencia;
      return $this->view->render($response, '/voluntarios/editar_hora.html', $data);
    })->setName('editar_hora');

    //Actualizar_horas
    $this->any('/actualizar_h',function($request,$response,$args){
      $parsedBody = $request->getParsedBody();
      $asistencia = $this->db->asistencia()->where('idasistencia',$parsedBody['id']);
      $data['hora_entrada'] = strtotime(''.$parsedBody['fecha'].' '.$parsedBody['h_entrada']);
      $data['hora_salida'] = strtotime(''.$parsedBody['fecha'].' '.$parsedBody['h_salida']);
      $data['hora_acumulada'] = date('H.is',$data['hora_salida']) - date('H.is',$data['hora_entrada']);
      if (date('H.is',$data['hora_salida'])>12 && $data['hora_acumulada']>1)
      {
        $data['hora_acumulada'] = $data['hora_acumulada']-1;
      }
      $asistencia->update(array("hora_entrada" => $data['hora_entrada'],"hora_salida" => $data['hora_salida'],"hora_acumulada" => $data['hora_acumulada']));
      return $response->withRedirect('/voluntarios/lista');
    })->setName('actualizar_hora');

    $this->any('/agr_activ',function($request,$response,$args){
      $parsedBody = $request->getParsedBody();
      $activ = $this->db->voluntario_has_actividades();
      $activ_n = $this->db->actividades();
      $data['nombre'] = $parsedBody['nombre_activ'];
      $activ_n->insert($data);
      $activ_id = $this->db->actividades()->select('idactividades')->where('nombre',$parsedBody['nombre_activ'])->fetch();
      $data1['voluntario_idvoluntario']= $parsedBody['id_vol'];
      $data1['actividades_idactividades']=$activ_id;
      $activ->insert($data1);
      return $response->withRedirect('/voluntarios/lista');
    })->setName('actualizar_hora');

});

//Grupo de Trabajador
$app->group('/trabajador', function (){

  //Vista de Registro de Trabajador
  $this->any('/trabajador_reg', function ($request, $response, $args) {
      return $this->view->render($response, '/trabajador/trabajador.html');
  })->setName('trabajador');

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
            $trab_p->insert($data);

            //guardar datos en tabla trabajador
            //predeterminar la zona horaria
            //date_default_timezone_set("America/Managua");
            $data1['carnet'] = $parsedBody['carnet'];
            $data1['cargo']=$parsedBody['cargo'];
            $data1['area']=$parsedBody['area'];
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
      return $this->view->render($response, '/trabajador/editar.html', $data);
  })->setName('trabajador_editar');

  //Actualizar un Trabajador
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
            $trab_p->update($data);

            //guardar datos en tabla trabajador
            //predeterminar la zona horaria
            //date_default_timezone_set("America/Managua");
            $data1['carnet'] = $parsedBody['carnet'];
            $data1['cargo']=$parsedBody['cargo'];
            $data1['area']=$parsedBody['area'];
            //$persona_id=$this->db->persona()->select('idpersona')->order('idpersona desc')->limit(1)->fetch();
            //$data1['persona_idpersona']=$persona_id['idpersona'];
            $trab_t->update($data1);
            return $response->withRedirect('/trabajador/lista');
            die();
  })->setName('actualizar_trab');

  $this->any('/hora', function ($request, $response, $args) {
      $parsedBody=$request->getParsedBody();
      $asis = $this->db->asistencia();
      $data['hora_entrada']=strtotime(''.$parsedBody['fecha'].' '.$parsedBody['entrada'].'');
      $data['hora_salida']=strtotime(''.$parsedBody['fecha'].' '.$parsedBody['salida']);
      $data['hora_acumulada']= date('H.is',$data['hora_salida'])- date('H.is',$data['hora_entrada']);
      if (date('H.is',$data['hora_salida'])>12 && $data['hora_acumulada']>1)
      {
        $data['hora_acumulada'] = $data['hora_acumulada']-1;
      }
      $data['persona_idpersona']=$parsedBody['id'];
      $asis->insert($data);
  })->setName('agregar_hora');

  $this->any('/editar_h/{id}',function($request,$response,$args){
    $id = $request->getAttribute('id');
    $asistencia = $this->db->asistencia()->where('idasistencia',$id)->fetch();
    $data['asistencia'] = $asistencia;
    return $this->view->render($response, '/trabajador/editar_hora.html', $data);
  })->setName('editar_hora');

  //Actualizar_horas
  $this->any('/actualizar_h',function($request,$response,$args){
    $parsedBody = $request->getParsedBody();
    $asistencia = $this->db->asistencia()->where('idasistencia',$parsedBody['id']);
    $data['hora_entrada'] = strtotime(''.$parsedBody['fecha'].' '.$parsedBody['h_entrada']);
    $data['hora_salida'] = strtotime(''.$parsedBody['fecha'].' '.$parsedBody['h_salida']);
    $data['hora_acumulada'] = date('H.is',$data['hora_salida']) - date('H.is',$data['hora_entrada']);
    if (date('H.is',$data['hora_salida'])>12 && $data['hora_acumulada']>1)
    {
      $data['hora_acumulada'] = $data['hora_acumulada']-1;
    }
    $asistencia->update(array("hora_entrada" => $data['hora_entrada'],"hora_salida" => $data['hora_salida'],"hora_acumulada" => $data['hora_acumulada']));
    return $response->withRedirect('/trabajador/lista');
  })->setName('actualizar_hora');

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
                                <button id="f_step" class="inverso" type="submit">Si</button>
                                <a class="button inverso" href="/" style="margin-left:50px;">No</a>
                                <input type="Hidden" id="idpersona" name="idpersona" value="' . $persona['idpersona'] . '">
                            </form>
                        </div>
                    </div>
                </div>
            </div>';
        else echo NULL;
        return TRUE;
    });

    $this->any('/f_step', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $persona = $this->db->persona()->where('idpersona',$parsedBody['idpersona']);
        $asistencia = $this->db->asistencia()->select('hora_entrada')->where('persona_idpersona',$parsedBody['idpersona']);
         //tomamos a una persona según cedula
            echo '<div class="row">
                <div class="column column-50 column-offset-25 alto">
                    <div class="centrar">
                        <div id="vald">
                            <h2 style="margin-top:5em; color:white;">Nombre: ' . $persona['nombre'] . ' ' . $persona['apellido']. '</h3>
                            <h2 style="margin-top:5em; color:white;">Hora de Entrada: '.date('H:i:s',$asistencia['hora_entrada']). '</h3>
                                <a class="button inverso" href="/" style="margin-left:50px;">Aceptar</a>
                                <input type="Hidden" id="idpersona" name="idpersona" value="' . $persona['idpersona'] . '">
                            </form>
                        </div>
                    </div>
                </div>
            </div>';
        return TRUE;
    });
});
