  <?php
// Archivo de rutas de la aplicacion y controlador con api's

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

date_default_timezone_set("America/Managua");
setlocale(LC_ALL , 'es_ES');


$app->any('/', function ($request, $response, $args) {
    return $this->view->render($response, 'inicio.html');
})->setName('in1icio');

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
                return $response->withRedirect('/voluntarios/asis_dia/0');
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
        $asistencia = $this->db->asistencia->where(array('idpersona' => $idpersona, 'hora_acumulada' => 0))->fetch();
        if ($asistencia){
            //Entramos a agregar salida
            $salida = time();
            $acumuladas = date('H.is',$salida) - date('H.is',$asistencia['hora_entrada']); ///
            if (date('H.is',$salida)>12 && $acumuladas>1)
            {
              $acumuladas = $acumuladas-1;
            }
            $asistencia->update(
                array("hora_acumulada" => $acumuladas,
                "hora_salida" => $salida
            ));
            $lista['hora_entrada'] = $asistencia['hora_entrada'];
        }else{
            //No lo encuentra, tenemos que agregar la asistencia para ese dia
            $registrando_asis = $this->db->asistencia();
            $today = date('Y-m-d', time());
            $datos_asis['fecha'] = $today;
            $datos_asis['hora_entrada'] = time();
            $datos_asis['idpersona'] = $idpersona;
            $datos_asis['hora_acumulada']=0;
            $lista['hora_entrada'] = $datos_asis['hora_entrada'];
            $registrando_asis->insert($datos_asis);
        }
        $lista['persona'] = $this->db->persona()->where('idpersona',$idpersona);
        $lista['hora_acumulada']  = round($acumuladas,2);
        $lista['hora_salida'] = $salida;
        return $this->view->render($response, 'inicio.html',$lista);
    })->setName('asistencia');
});
//Mostrar pagina de visitas
$app->group('/visitas', function () {
    //
    $this->any('/visitas_reg', function ($request, $response, $args) {
        return $this->view->render($response, '/visitas/visitas.html');
    })->setName('visitas');

    //Mostrar lista de visitas
    $this->any('/lista/{pa}', function ($request, $response, $args) {
        $todo[] = 0;
        $pa = $request->getAttribute('pa');
        $vis=$this->db->visita()->select('persona_idpersona');
        $lista_num = $this->db->persona()->where('idpersona',$vis)->count();
        $limite = 25;
        $paginas = $lista_num/$limite;
        $paginas =ceil($paginas);
        for ($i=1; $i <=$paginas ; $i++) {
          if ($i== 1) {
              $partir_de = 0;
              $todo['paginas'][$i]['no_pag'] = $i;
              $todo['paginas'][$i]['partir_de'] = $partir_de;
              $todo['paginas'][$i]['nombre_pag'] = "visitas";
              $todo['paginas'][$i]['subdiv'] = "lista";
          }
          else {
              $partir_de = $limite + $partir_de;
              $todo['paginas'][$i]['no_pag'] = $i;
              $todo['paginas'][$i]['partir_de'] = $partir_de;
              $todo['paginas'][$i]['nombre_pag'] = "visitas";
              $todo['paginas'][$i]['subdiv'] = "lista";
          }
        }
        $visita= $this->db->visita()->select('cedula')->limit($limite,$pa);
        $id=$this->db->visita()->select('persona_idpersona');
        $persona=$this->db->persona()->where('idpersona',$id)->limit($limite,$pa);
        $i = 0;
        foreach ($persona as $key => $value) {
            $todo['datos'][$i]['nombre'] = $value['nombre'];
            $todo['datos'][$i]['apellido'] = $value['apellido'];
            $todo['datos'][$i]['idvisita'] = $value['idpersona'];
            $i +=1;
        }
        $i  = 0;
        foreach ($visita as $key => $value) {
          $todo['datos'][$i]['cedula'] = $value['cedula'];
          $i +=1;
        }


        return $this->view->render($response, '/visitas/lista.html',$todo);
    })->setName('visitas_lista');

    //Mostrar mas detalles de una visita
    $this->any('/mas/{id}', function ($request, $response, $args) {
        $id = $request->getAttribute('id');
        $idpersona=$this->db->visita()->select('persona_idpersona')->where('persona_idpersona',$id)->fetch();
        $mas['persona']=$this->db->persona()->where('idpersona',$idpersona['persona_idpersona']);
        $mas['visita'] = $this->db->visita()->where('persona_idpersona',$id);
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
        $persona->insert($data);
        $data1['cedula']=$parsedBody['ced'];
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
        return $response->withRedirect('/visitas/visitas_reg');
    })->setName('visitas_reg');

    //Busqueda de un visitante
    $this->any('/busqueda_visita/{pa}', function($request,$response,$args){
      $buscar = [];
      $pa = $request->getAttribute('pa');
      $parsedBody = $request->getParsedBody();
      $visita=$this->db->visita()->select('persona_idpersona');
      $lista_num = $this->db->persona()->where('idpersona',$visita)->where('nombre LIKE ?','%'.$parsedBody['buscar'].'%')->or('apellido LIKE ? ','%'.$parsedBody['buscar'].'%')->count();
      $limite = 25;
      $paginas = $lista_num/$limite;
      $paginas =ceil($paginas);
      for ($i=1; $i <=$paginas ; $i++) {
        if ($i== 1) {
            $partir_de = 0;
            $buscar['paginas'][$i]['no_pag'] = $i;
            $buscar['paginas'][$i]['partir_de'] = $partir_de;
        }
        else {
            $partir_de = $limite + $partir_de;
            $buscar['paginas'][$i]['no_pag'] = $i;
            $buscar['paginas'][$i]['partir_de'] = $partir_de;
        }
      }
      $busca = $this->db->persona()->where('idpersona',$visita)->where('nombre LIKE ?','%'.$parsedBody['buscar'].'%')->or('apellido LIKE ? ','%'.$parsedBody['buscar'].'%')->limit($limite,$pa);
      $id_per = $this->db->persona()->select('idpersona')->where('idpersona',$visita) -> where('nombre LIKE ?','%'.$parsedBody['buscar'].'%')->or('apellido LIKE ? ','%'.$parsedBody['buscar'].'%')->limit($limite,$pa);
      $vis = $this->db->visita()->where('persona_idpersona',$id_per);
      $i = 0;
        $i = 0;

        foreach ($busca as $key => $value) {
            $buscar['datos'][$i]['nombre'] = $value['nombre'];
            $buscar['datos'][$i]['apellido'] = $value['apellido'];
            $buscar['datos'][$i]['idvisita'] = $value['idpersona'];
            $i += 1;
        }
        $i = 0;
        foreach ($vis as $key => $value) {
          $buscar['datos'][$i]['cedula'] = $value['cedula'];
        }
      return $this->view->render($response, '/visitas/lista.html',$buscar);
      //$buscar = $this->db->persona()->where('idpersona',$trabajador)->where('nombre LIKE ? ','%'.$parsedBody['buscar'].'%')->or('apellido LIKE ? ','%'.$parsedBody['buscar'].'%');
    })->setName('visitantes_buscar');

});
//Grupo de rutas de Voluntarios
$app->group('/voluntarios', function () {

    $this->any('/asis_dia/{pa}', function ($request, $response, $args) {
        $voluntario=$this->db->voluntario()->select('persona_idpersona');
        $today = date('Y-m-d', time());

        $todo[] = 0;
        $pa = $request->getAttribute('pa');
        $lista_num = $this->db->asistencia()->select('asistencia.fecha','asistencia.hora_entrada','asistencia.hora_salida','asistencia.hora_acumulada','persona.nombre','persona.apellido')->where(array('persona.idpersona' => $voluntario, 'asistencia.fecha' => $today))->count();
        $limite = 25;
        $paginas = $lista_num/$limite;
        $paginas =ceil($paginas);
        for ($i=1; $i <=$paginas ; $i++) {
          if ($i== 1) {
              $partir_de = 0;
              $todo['paginas'][$i]['no_pag'] = $i;
              $todo['paginas'][$i]['partir_de'] = $partir_de;
              $todo['paginas'][$i]['nombre_pag'] = "voluntarios";
              $todo['paginas'][$i]['subdiv'] = "asis_dia";
          }
          else {
              $partir_de = $limite + $partir_de;
              $todo['paginas'][$i]['no_pag'] = $i;
              $todo['paginas'][$i]['partir_de'] = $partir_de;
              $todo['paginas'][$i]['nombre_pag'] = "voluntarios";
              $todo['paginas'][$i]['subdiv'] = "asis_dia";
          }
        }

        $todo['datos'] = $this->db->asistencia()->select('asistencia.fecha','asistencia.hora_entrada','asistencia.hora_salida','asistencia.hora_acumulada','persona.nombre','persona.apellido')->where(array('persona.idpersona' => $voluntario, 'asistencia.fecha' => $today))->limit($limite,$pa);
        return $this->view->render($response, '/voluntarios/asisxdia.html', $todo);
    })->setName('voluntarios_asistencia_dia');

    $this->any('/lista_asis_semana', function($request, $response, $args){
        $anio_min = $this->db->voluntario()->select('fecha_ingreso')->min('fecha_ingreso');
        $vol = $this->db->voluntario()->select('persona_idpersona');
        $anio_max = $this->db->asistencia()->select('hora_entrada')->where('idpersona')->max('hora_entrada');
        $for_min = date('Y',$anio_min);
        $for_max = date('Y',$anio_max);
        $w = 0;
        for ($i=$for_min; $i <=$for_max ; $i++) {
          $datos['asistencia'][$w]['anios']= $i;
          $w = $w+1;
        }
        return $this->view->render($response, '/voluntarios/listaxtiempo.html',$datos);
    })->setName('asisxinter');

    $this->any('/busqueda_semana/{pa}' , function($request,$response,$args){
        $pa = $request->getAttribute('pa');
        $anio_min = $this->db->voluntario()->select('fecha_ingreso')->min('fecha_ingreso');
        $vol = $this->db->voluntario()->select('persona_idpersona');
        $anio_max = $this->db->asistencia()->select('hora_entrada')->where('idpersona')->max('hora_entrada');
        $for_min = date('Y',$anio_min);
        $for_max = date('Y',$anio_max);
        $w = 0;
        for ($i=$for_min; $i <=$for_max ; $i++) {
          $datos['asistencia'][$w]['anios']= $i;
          $w = $w+1;
        }

        $parsedBody = $request->getParsedBody();
        $inicio = strtotime($parsedBody['f_inicio']);
        $final = strtotime($parsedBody['f_final'].' +23 hour');
        //echo $lista = $this->db->asistencia()->select('fecha','hora_entrada','hora_salida','persona_idpersona')->where('hora_entrada >= '.$inicio.' AND hora_entrada <= '.$final);
        $persona = $this->db->persona()->select('idpersona','nombre','apellido');
        $asis = $this->db->asistencia()->select('idpersona');

        $todo[] = 0;
        $lista_num = $this->db->asistencia()->select('asistencia.fecha','asistencia.hora_entrada','asistencia.hora_salida','asistencia.hora_acumulada','persona.nombre','persona.apellido')->where('hora_entrada >= '.$inicio.' AND hora_salida <= '.$final)->count();
        $limite = 25;
        $paginas = $lista_num/$limite;
        $paginas =ceil($paginas);
        for ($i=1; $i <=$paginas ; $i++) {
          if ($i== 1) {
              $partir_de = 0;
              $datos['paginas'][$i]['no_pag'] = $i;
              $datos['paginas'][$i]['partir_de'] = $partir_de;
          }
          else {
              $partir_de = $limite + $partir_de;
              $datos['paginas'][$i]['no_pag'] = $i;
              $datos['paginas'][$i]['partir_de'] = $partir_de;
          }
        }

        $lista = $this->db->asistencia()->select('asistencia.fecha','asistencia.hora_entrada','asistencia.hora_salida','asistencia.hora_acumulada','persona.nombre','persona.apellido')->where('hora_entrada >= '.$inicio.' AND hora_salida <= '.$final)->limit($limite,$pa);

        //echo count($datos['asistencia']);
        //echo date('w',$lista[0]['hora_entrada']);
        $datos['cabecera']['titulo'] = "Lunes ".date('d/m/Y',$inicio)." - Viernes ".date('d/m/Y',$final);
        $j = 0;
        for ($i=0; $i <= count($lista) ; $i++) {
          if (date('w',$lista[$i]['hora_entrada'])==1) {
            $datos['registro']['lunes'][$j] = $lista[$i];
            $j += 1;
          }
        }
        $j = 0;
        for ($i=0; $i <= count($lista) ; $i++) {
          if (date('w',$lista[$i]['hora_entrada'])==2) {
            $datos['registro']['martes'][$j]= $lista[$i];
            $j += 1;
          }
        }
        $j = 0;
        for ($i=0; $i <= count($lista) ; $i++) {
          if (date('w',$lista[$i]['hora_entrada'])==3) {
            $datos['registro']['miercoles'][$j] = $lista[$i];
            $j += 1;
          }
        }
        $j = 0;
        for ($i=0; $i <= count($lista) ; $i++) {
          if (date('w',$lista[$i]['hora_entrada'])==4) {
            $datos['registro']['jueves'][$j]= $lista[$i];
            $j += 1;
          }
        }
        $j = 0;
        for ($i=0; $i <= count($lista) ; $i++) {
          if (date('w',$lista[$i]['hora_entrada'])==5) {
            $datos['registro']['viernes'][$j] = $lista[$i];
            $j += 1;
          }
        }
        //echo json_encode($datos['registro']); die();
        $datos['cantidad'] = ceil(count($lista)/5);

        return $this->view->render($response, '/voluntarios/listaxtiempo.html',$datos);
    })->setName('busqueda_semana');

    //Formulario de Registro
    $this->any('/voluntarios_reg', function ($request, $response, $args) {
        $parsedBody  = $response->getBody();
        $org         = $this->db->institucion();
        $carrera     = $this->db->carrera();
        $actividades = $this->db->actividades();
        $area        = $this->db->area();
        $item = [$org,$carrera,$actividades,$area];
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
        $data1['fecha_ingreso']=strtotime($parsedBody['fecha']);
        $persona_id=$this->db->persona()->select('idpersona')->order('idpersona desc')->limit(1)->fetch();
        $data1['persona_idpersona']=$persona_id['idpersona'];
        $data1['institucion_idinstitucion']=$parsedBody['org'];
        $data1['carrera_idcarrera']=$parsedBody['carrera'];
        $data1['area_idarea']=$parsedBody['area'];
        $vol_v->insert($data1);

        return $response->withRedirect('/voluntarios/detalles/'.$persona_id['idpersona'].'_0');
    })->setName('voluntarios_reg');

    //Mostrar lista de voluntarios registrados
    $this->any('/lista/{pa}', function ($request, $response, $args) {
        $todo = [];
        $pa = $request->getAttribute('pa');
        $voluntario=$this->db->voluntario()->select('persona_idpersona');
        $lista_num = $this->db->persona()->where('idpersona',$voluntario)->count();
        $limite = 25;
        $paginas = $lista_num/$limite;
        $paginas =ceil($paginas);
        for ($i=1; $i <=$paginas ; $i++) {
          if ($i== 1) {
              $partir_de = 0;
              $todo['paginas'][$i]['no_pag'] = $i;
              $todo['paginas'][$i]['partir_de'] = $partir_de;
              $todo['paginas'][$i]['nombre_pag'] = "voluntarios";
              $todo['paginas'][$i]['subdiv'] = "lista";
          }
          else {
              $partir_de = $limite + $partir_de;
              $todo['paginas'][$i]['no_pag'] = $i;
              $todo['paginas'][$i]['partir_de'] = $partir_de;
              $todo['paginas'][$i]['nombre_pag'] = "voluntarios";
              $todo['paginas'][$i]['subdiv'] = "lista";
          }
        }
        $id = $this->db->voluntario()->select('persona_idpersona')->limit($limite,$pa);
        $personas=$this->db->persona()->where('idpersona',$voluntario)->limit($limite,$pa);
        $i = 0;
        foreach ($id as $key => $value) {
          $time = $this->db->asistencia()->where('idpersona',$value['persona_idpersona'])->sum('hora_acumulada');
          $horas[$i] = round($time,2);
          $i += 1;
        }
        $i = 0;
        foreach ($personas as $key => $value) {
          $todo['datos'][$i]['idpersona'] = $value['idpersona'];
          $todo['datos'][$i]['nombre'] = $value['nombre'];
          $todo['datos'][$i]['apellido'] = $value['apellido'];
          $todo['datos'][$i]['cedula'] = $value['cedula'];
          $i += 1;
        }
        $i = 0;
        foreach ($horas as $key => $value) {
          $todo['datos'][$i]['hora_acumulada'] = $value;
          $i += 1;
        }
        return $this->view->render($response, '/voluntarios/lista.html',$todo);
    })->setName('voluntarios_lista');

    //Busqueda de un Voluntario
    $this->any('/busqueda/{pa}', function($request,$response,$args){
      $pa = $request->getAttribute('pa');
      $voluntario=$this->db->voluntario()->select('persona_idpersona');
      $lista_num = $this->db->persona()->where('idpersona',$voluntario) -> where('nombre LIKE ?','%'.$parsedBody['buscar'].'%')->or('apellido LIKE ? ','%'.$parsedBody['buscar'].'%')->count();
      $limite = 25;
      $paginas = $lista_num/$limite;
      $paginas =ceil($paginas);
      for ($i=1; $i <=$paginas ; $i++) {
        if ($i== 1) {
            $partir_de = 0;
            $buscar['paginas'][$i]['no_pag'] = $i;
            $buscar['paginas'][$i]['partir_de'] = $partir_de;
        }
        else {
            $partir_de = $limite + $partir_de;
            $buscar['paginas'][$i]['no_pag'] = $i;
            $buscar['paginas'][$i]['partir_de'] = $partir_de;
        }
      }

      $parsedBody = $request->getParsedBody();

      $busca = $this->db->persona()->where('idpersona',$voluntario) -> where('nombre LIKE ?','%'.$parsedBody['buscar'].'%')->or('apellido LIKE ? ','%'.$parsedBody['buscar'].'%')->limit($limite,$pa);

      $i = 0;
        foreach ($busca as $key => $value) {
         $time = $this->db->asistencia()->where('idpersona',$value['persona_idpersona'])->sum('hora_acumulada');
          $horas[$i]['hora_acumulada'] = round($time,2);
          $i += 1;
        }

        $i = 0;

        foreach ($busca as $key => $value) {
            $buscar['datos'][$i]['nombre'] = $value['nombre'];
            $buscar['datos'][$i]['apellido'] = $value['apellido'];
            $buscar['datos'][$i]['cedula'] = $value['cedula'];
            $i += 1;
        }
        $i = 0;
        foreach ($horas as $key => $value) {
            $buscar['datos'][$i]['hora_acumulada'] = $value['hora_acumulada'];
            $i += 1;
        }




      return $this->view->render($response, '/voluntarios/lista.html',$buscar);
      //$buscar = $this->db->persona()->where('idpersona',$trabajador)->where('nombre LIKE ? ','%'.$parsedBody['buscar'].'%')->or('apellido LIKE ? ','%'.$parsedBody['buscar'].'%');
    })->setName('voluntarios_buscar');

    //Mostrar detalles de un voluntario
    $this->any('/detalles/{id}_{pa}', function ($request, $response, $args) {
        $id = $request->getAttribute('id');
        $lista['persona'] = $this->db->persona()->where('idpersona',$id);
        $lista['volun'] = $this->db->voluntario()->where('persona_idpersona',$id);
        $id_vol = $this->db->voluntario()->select('idvoluntario')->where('persona_idpersona',$id)->fetch();
        $pa = $request->getAttribute('pa');
        $lista_num = $this->db->asistencia()->where('idpersona',$id)->count();
        $limite = 25;
        $paginas = $lista_num/$limite;
        $paginas =ceil($paginas);
        for ($i=1; $i <=$paginas ; $i++) {
          if ($i== 1) {
              $partir_de = 0;
              $lista['paginas'][$i]['id'] = $id;
              $lista['paginas'][$i]['no_pag'] = $i;
              $lista['paginas'][$i]['partir_de'] = $partir_de;
              $lista['paginas'][$i]['nombre_pag'] = "voluntarios";
              $lista['paginas'][$i]['subdiv'] = "detalles";
          }
          else {
              $partir_de = $limite + $partir_de;
              $lista['paginas'][$i]['id'] = $id;
              $lista['paginas'][$i]['no_pag'] = $i;
              $lista['paginas'][$i]['partir_de'] = $partir_de;
              $lista['paginas'][$i]['nombre_pag'] = "voluntarios";
              $lista['paginas'][$i]['subdiv'] = "detalles";
          }
        }
        $lista['asis'] = $this->db->asistencia()->where('idpersona',$id)->limit($limite,$pa);
        $acumuladas = $this->db->asistencia()->where('idpersona',$id)->sum('hora_acumulada');
        $lista['acum'] = round($acumuladas,2);
        $id_area = $this->db->voluntario()->select('area_idarea')->where('persona_idpersona',$id)->fetch();
        $lista['area'] = $this->db->area()->where('idarea',$id_area['area_idarea']);
        $lista['actividades'] = $this->db->actividades()->where('area_idarea',$id_area['area_idarea']);
        $lista['actividad_extra'] = $this->db->actividad_extra()->where('voluntario_idvoluntario',$id_vol);
        /*echo json_encode($lista['area']); die();*/
        $lista['ultima'] = $this->db->asistencia()->where('idpersona',$id)->select('hora_entrada')->order('hora_entrada desc')->limit(1);

        return $this->view->render($response, '/voluntarios/detalles.html',$lista);
    })->setName('voluntarios_detalles');

    //Editar un voluntario
    $this->any('/editar/{id}', function ($request, $response, $args) {
        $id = $request->getAttribute('id');
        $data['persona'] = $this->db->persona()->where('idpersona',$id)->fetch();
        $data['voluntario'] = $this->db->voluntario()->where('persona_idpersona',$id)->fetch();
        $data['org'] = $this->db->institucion();
        $data['carrera'] = $this->db->carrera();
        $data['area'] = $this->db->area();
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
        $data1['area_idarea'] = $parsedBody['area'];
        $vol_v->update($data1);

        return $response->withRedirect('/voluntarios/detalles/'.$id.'_1_0');
    })->setName('voluntarios_up');

    //Agregar Horas
    $this->any('/hora', function ($request, $response, $args) {
        $parsedBody=$request->getParsedBody();
        $asis = $this->db->asistencia();
        $data['fecha']=$parsedBody['fecha'];
        $data['hora_entrada']=strtotime(''.$parsedBody['fecha'].' '.$parsedBody['entrada'].'');
        $data['hora_salida']=strtotime(''.$parsedBody['fecha'].' '.$parsedBody['salida']);
        $data['hora_acumulada']= date('H.is',$data['hora_salida'])- date('H.is',$data['hora_entrada']);
        if (date('H.is',$data['hora_salida'])>12 && $data['hora_acumulada']>1 && date('H.is',$data['hora_entrada'])<12)
        {
          $data['hora_acumulada'] = $data['hora_acumulada']-1;
        }
        $data['idpersona']=$parsedBody['id'];
        $asis->insert($data);
        return $response->withRedirect('/voluntarios/detalles/'.$parsedBody['id'].'_0');
    })->setName('agregar_hora');

    //Editar horas
    $this->any('/editar_h/{id}_{id_per}',function($request,$response,$args){
      $id = $request->getAttribute('id');
      $id_per = $request->getAttribute('id_per');
      $asistencia = $this->db->asistencia()->where('idasistencia',$id)->fetch();
      $data['asistencia'] = $asistencia;
      $data['voluntario_id'] = $id_per;
      return $this->view->render($response, '/voluntarios/editar_hora.html', $data);
    })->setName('editar_hora');

    //Actualizar_horas
    $this->any('/actualizar_h',function($request,$response,$args){
      $parsedBody = $request->getParsedBody();
      $asistencia = $this->db->asistencia()->where('idasistencia',$parsedBody['id']);
      $data['fecha']=$parsedBody['fecha'];
      $data['hora_entrada'] = strtotime(''.$parsedBody['fecha'].' '.$parsedBody['h_entrada']);
      $data['hora_salida'] = strtotime(''.$parsedBody['fecha'].' '.$parsedBody['h_salida']);
      $data['hora_acumulada'] = date('H.is',$data['hora_salida']) - date('H.is',$data['hora_entrada']);
      if (date('H.is',$data['hora_salida'])>12 && $data['hora_acumulada']>1 && date('H.is',$data['hora_entrada'])<12)
      {
        $data['hora_acumulada'] = $data['hora_acumulada']-1;
      }
      $asistencia->update(array("hora_entrada" => $data['hora_entrada'],"hora_salida" => $data['hora_salida'],"hora_acumulada" => $data['hora_acumulada']));
      return $response->withRedirect('/voluntarios/detalles/'.$parsedBody['id_per'].'_1_0');
    })->setName('actualizar_hora');

    $this->any('/agr_activ',function($request,$response,$args){
      $parsedBody = $request->getParsedBody();
      $activ_n = $this->db->actividad_extra();
      $persona_id = $this->db->voluntario()->select('persona_idpersona')->where('idvoluntario',$parsedBody['id_vol'])->fetch();
      $data['nombre'] = $parsedBody['nombre_activ'];
      $data['voluntario_idvoluntario'] = $parsedBody['id_vol'];
      $activ_n->insert($data);
      return $response->withRedirect('/voluntarios/detalles/'.$persona_id['persona_idpersona'].'_0');
    })->setName('actualizar_hora');

});

//Grupo de Trabajador
$app->group('/trabajador', function (){

  //Vista de Registro de Trabajador
  $this->any('/trabajador_reg', function ($request, $response, $args) {
      $datos['area'] = $this->db->area();
      return $this->view->render($response, '/trabajador/trabajador.html',$datos);
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
            $persona_id=$this->db->persona()->select('idpersona')->order('idpersona desc')->limit(1)->fetch();
            $data1['persona_idpersona']=$persona_id['idpersona'];
            $data1['area_idarea']=$parsedBody['area'];
            $trab_t->insert($data1);

            return $response->withRedirect('/trabajador/detalles/'.$persona_id['idpersona'].'_0');
            die();
  })->setName('trabajador_reg');

  //Lista de Trabajadores
  $this->any('/lista/{pa}', function ($request, $response, $args) {
      $pa = $request->getAttribute('pa');
      $trabajador=$this->db->trabajador()->select('persona_idpersona');
      $lista_num = $this->db->persona()->where('idpersona',$trabajador)->count();
      $limite = 25;
      $paginas = $lista_num/$limite;
      $paginas =ceil($paginas);
      for ($i=1; $i <=$paginas ; $i++) {
        if ($i== 1) {
            $partir_de = 0;
            $lista['paginas'][$i]['no_pag'] = $i;
            $lista['paginas'][$i]['partir_de'] = $partir_de;
            $lista['paginas'][$i]['nombre_pag'] = "trabajador";
            $lista['paginas'][$i]['subdiv'] = "lista";
        }
        else {
            $partir_de = $limite + $partir_de;
            $lista['paginas'][$i]['no_pag'] = $i;
            $lista['paginas'][$i]['partir_de'] = $partir_de;
            $lista['paginas'][$i]['nombre_pag'] = "trabajador";
            $lista['paginas'][$i]['subdiv'] = "lista";
        }
      }
      $lista['persona']=$this->db->persona()->where('idpersona',$trabajador)->limit($limite,$pa);
      return $this->view->render($response, '/trabajador/lista.html',$lista);
  })->setName('trabajador_lista');

  //Busqueda de un Trabjador
  $this->any('/busqueda/{pa}', function($request,$response,$args){
    $pa = $request->getAttribute('pa');
    $trabajador=$this->db->trabajador()->select('persona_idpersona');
    $lista_num = $this->db->persona()->where('idpersona',$trabajador) -> where('nombre LIKE ?','%'.$parsedBody['buscar'].'%')->or('apellido LIKE ? ','%'.$parsedBody['buscar'].'%')->count();
    $limite = 25;
    $paginas = $lista_num/$limite;
    $paginas =ceil($paginas);
    for ($i=1; $i <=$paginas ; $i++) {
      if ($i== 1) {
          $partir_de = 0;
          $buscar['paginas'][$i]['no_pag'] = $i;
          $buscar['paginas'][$i]['partir_de'] = $partir_de;
      }
      else {
          $partir_de = $limite + $partir_de;
          $buscar['paginas'][$i]['no_pag'] = $i;
          $buscar['paginas'][$i]['partir_de'] = $partir_de;
      }
    }
    $parsedBody = $request->getParsedBody();
    $buscar['persona'] = $this->db->persona()->where('idpersona',$trabajador) -> where('nombre LIKE ?','%'.$parsedBody['buscar'].'%')->or('apellido LIKE ? ','%'.$parsedBody['buscar'].'%')->limit($limite,$pa);
    return $this->view->render($response, '/trabajador/lista.html',$buscar);
  })->setName('trabajador_buscar');

  //Mostrar detalles de un trabajador
  $this->any('/detalles/{id}_{pa}', function ($request, $response, $args) {
      $id = $request->getAttribute('id');
      $lista['persona'] = $this->db->persona()->where('idpersona',$id);
      $area = $this->db->trabajador()->where('persona_idpersona',$id)->fetch();
      $lista['trab'] = $this->db->trabajador()->where('persona_idpersona',$id);
      $pa = $request->getAttribute('pa');
      $lista_num = $this->db->asistencia()->where('idpersona',$id)->count();
      $limite = 25;
      $paginas = $lista_num/$limite;
      $paginas =ceil($paginas);
      for ($i=1; $i <=$paginas ; $i++) {
        if ($i== 1) {
            $partir_de = 0;
            $lista['paginas'][$i]['id'] = $id;
            $lista['paginas'][$i]['no_pag'] = $i;
            $lista['paginas'][$i]['partir_de'] = $partir_de;
            $lista['paginas'][$i]['nombre_pag'] = "trabajador";
            $lista['paginas'][$i]['subdiv'] = "detalles";
        }
        else {
            $partir_de = $limite + $partir_de;
            $lista['paginas'][$i]['id'] = $id;
            $lista['paginas'][$i]['no_pag'] = $i;
            $lista['paginas'][$i]['partir_de'] = $partir_de;
            $lista['paginas'][$i]['nombre_pag'] = "trabajador";
            $lista['paginas'][$i]['subdiv'] = "detalles";
        }
      }

      $lista['asis'] = $this->db->asistencia()->where('idpersona',$id)->limit($limite,$pa);
      $trab_ad = $this->db->trabajador()->where('persona_idpersona',$id)->fetch();
      $admin = $this->db->admin()->where('trabajador_idtrabajador',$trab_ad['idtrabajador']);
      $lista['cant'] = count($admin);
      $lista['area'] = $this->db->area()->where('idarea',$area['area_idarea']);
      return $this->view->render($response, '/trabajador/detalles.html',$lista);
  })->setName('trabajador_detalles');

  //Editar un trabajador
  $this->any('/editar/{id}', function ($request, $response, $args) {
      $id = $request->getAttribute('id');
      $data['persona'] = $this->db->persona()->where('idpersona',$id)->fetch();
      $data['trabajador'] = $this->db->trabajador()->where('persona_idpersona',$id)->fetch();
      $data['area'] = $this->db->area();
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
            $data1['area_idarea']=$parsedBody['area'];
            //$persona_id=$this->db->persona()->select('idpersona')->order('idpersona desc')->limit(1)->fetch();
            //$data1['persona_idpersona']=$persona_id['idpersona'];
            $trab_t->update($data1);
            return $response->withRedirect('/trabajador/detalles/'.$id.'_0');
  })->setName('actualizar_trab');

  //agregar_hora
  $this->any('/hora', function ($request, $response, $args) {
      $parsedBody=$request->getParsedBody();
      $asis = $this->db->asistencia();
      $data['fecha']=$parsedBody['fecha'];
      $data['hora_entrada']=strtotime(''.$parsedBody['fecha'].' '.$parsedBody['entrada'].'');
      $data['hora_salida']=strtotime(''.$parsedBody['fecha'].' '.$parsedBody['salida']);
      $data['hora_acumulada']= date('H.is',$data['hora_salida'])- date('H.is',$data['hora_entrada']);
      if (date('H.is',$data['hora_salida'])>12 && $data['hora_acumulada']>1 && date('H.is',$data['hora_entrada'])<12)
      {
        $data['hora_acumulada'] = $data['hora_acumulada']-1;
      }
      $data['idpersona']=$parsedBody['id'];
      $asis->insert($data);
      return $response->withRedirect('/trabajador/detalles/'.$parsedBody['id'].'_0');
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
    $data['fecha']=$parsedBody['fecha'];
    $data['hora_entrada'] = strtotime(''.$parsedBody['fecha'].' '.$parsedBody['h_entrada']);
    $data['hora_salida'] = strtotime(''.$parsedBody['fecha'].' '.$parsedBody['h_salida']);
    $data['hora_acumulada'] = date('H.is',$data['hora_salida']) - date('H.is',$data['hora_entrada']);
    if (date('H.is',$data['hora_salida'])>12 && $data['hora_acumulada']>1 && date('H.is',$data['hora_entrada'])<12)
    {
      $data['hora_acumulada'] = $data['hora_acumulada']-1;
    }
    $asistencia->update(array("hora_entrada" => $data['hora_entrada'],"hora_salida" => $data['hora_salida'],"hora_acumulada" => $data['hora_acumulada']));
    return $response->withRedirect('/trabajador/detalles/'.$parsedBody['id_per'].'_0');
  })->setName('actualizar_hora');

  //agregar_admin
  $this->any('/agr_admin', function ($request, $response, $args) {
      $parsedBody=$request->getParsedBody();
      $admin = $this->db->admin();
      $data['usuario'] = $parsedBody['user'];
      $data['password'] = $parsedBody['pass'];
      $data['trabajador_idtrabajador'] = $parsedBody['id_trab'];
      $admin->insert($data);
      return $response->withRedirect('/trabajador/detalles/'.$parsedBody['id_per'].'_0');
  })->setName('agregar_admin');

  //quitar_admin
  $this->any('/deg_admin', function ($request, $response, $args) {
      $parsedBody=$request->getParsedBody();
      $data['usuario'] = $parsedBody['user'];
      $data['password'] = $parsedBody['pass'];
      $data['trabajador_idtrabajador'] = $parsedBody['id_trab'];
      $admin = $this->db->admin()->where('trabajador_idtrabajador',$parsedBody['id_trab'])->fetch();
      $admin->delete();
      return $response->withRedirect('/trabajador/detalles/'.$parsedBody['id_per'].'_1_0');
  })->setName('agregar_admin');

  $this->any('/lista_asis_mes/{id}', function($request, $response, $args){
      $id = $request->getAttribute('id');

      $anio_min = $this->db->asistencia()->select('hora_entrada')->where('idpersona',$id)->min('hora_entrada');
      $vol = $this->db->voluntario()->select('persona_idpersona');
      $for_max = date('Y');
      $for_min = date('Y',$anio_min);
      $w = 0;
      for ($i=$for_min; $i <=$for_max ; $i++) {
        $datos['asistencia'][$w]['anios']= $i;
        $w = $w+1;
      }

      $mes = date('m');
      $anio = date('Y');
      $my_date = new DateTime();
      $meses_t = array("","january","february","march","april","may","june","july","august","september","october","november","december");
      $meses_s = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
      //cantidad a utilizar en los ciclos
      $cant = array("first","second","third","fourth","fifth","sixth");
      $datos['cabecera']['titulo'] = $meses_s[intval($mes)]." del ".$anio;
      //Representacion numerica del mes es = m;
      //conseguir el primer dia del mes
      $p_dia = $my_date->modify('first day of '.$meses_t[$mes].' '.$anio);
      $p_dia_f = $p_dia->format('Y-m-d');
      $p_dia_ft = strtotime($p_dia_f);
      $u_dia = $my_date->modify('last day of '.$meses_t[$mes].' '.$anio);
      $u_dia_f = $u_dia->format('Y-m-d');
      $u_dia_ft = strtotime($u_dia_f);
      $lista = $this->db->asistencia()->select('fecha','hora_entrada','hora_salida','idpersona')->where('fecha >= "'.$p_dia_f.'" AND fecha <= "'.$u_dia_f.'" AND idpersona LIKE '.$id);
      $pers = $this->db->persona()->select('nombre','apellido')->where('idpersona',$id)->fetch();
      $datos['informacion']['nombre'] = $pers['nombre']." ".$pers['apellido'];

      $j = 0;
      $por = count($lista);
      for ($i=0; $i <= count($lista) ; $i++) {
        if (date('w',$lista[$i]['hora_entrada'])==1) {
          $datos['registro']['lunes'][$j] = $lista[$i];
          $j += 1;
        }
      }
      $j = 0;
      for ($i=0; $i <= $por ; $i++) {
        if (date('w',$lista[$i]['hora_entrada']) ==2) {
          $datos['registro']['martes'][$j]= $lista[$i];
          $j += 1;
        }
      }
      $j = 0;
      for ($i=0; $i <= $por ; $i++) {
        if (date('w',$lista[$i]['hora_entrada'])==3) {
          $datos['registro']['miercoles'][$j]=$lista[$i];
          $j += 1;
        }
      }
      $j = 0;
      for ($i=0; $i <= $por ; $i++) {
        if (date('w',$lista[$i]['hora_entrada'])==4) {
          $datos['registro']['jueves'][$j]= $lista[$i];
          $j += 1;
        }
      }
      $j = 0;
      for ($i=0; $i <= $por ; $i++) {
        if (date('w',$lista[$i]['hora_entrada'])==5) {
          $datos['registro']['viernes'][$j] = $lista[$i];
          $j += 1;
        }
      }
      $j = 0;
      for ($i=0; $i <= $por ; $i++) {
        if (date('w',$lista[$i]['hora_entrada'])==6) {
          $datos['registro']['sabado'][$j] = $lista[$i];
          $j += 1;
        }
      }
      //echo json_encode($datos['registro']); die();
      $datos['cantidad'] = ceil(count($lista)/6);
      //echo json_encode($datos['registro']); die();
      $datos['id']['persona'] = $id;
      return $this->view->render($response, '/trabajador/listaxmes.html',$datos);
  })->setName('asisxmes');

  $this->any('/busqueda_asis_mes', function($request, $response, $args){
      $parsedBody = $request->getParsedBody();
      $id = $parsedBody['id_trab'];
      $anio_min = $this->db->asistencia()->select('hora_entrada')->where('idpersona',$id)->min('hora_entrada');
      $vol = $this->db->voluntario()->select('persona_idpersona');
      $for_max = date('Y');
      $for_min = date('Y',$anio_min);
      $w = 0;
      for ($i=$for_min; $i <=$for_max ; $i++) {
        $datos['asistencia'][$w]['anios']= $i;
        $w = $w+1;
      }

      $mes = $parsedBody['mes'];
      $anio = $parsedBody['anio'];
      $my_date = new DateTime();
      $meses_t = array("january","february","march","april","may","june","july","august","september","october","november","december");
      $meses_s = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
      //cantidad a utilizar en los ciclos
      $cant = array("first","second","third","fourth","fifth","sixth");
      $datos['cabecera']['titulo'] = $meses_s[intval($mes)]." del ".$anio;
      //Representacion numerica del mes es = m;
      //conseguir el primer dia del mes
      $p_dia = $my_date->modify('first day of '.$meses_t[$mes].' '.$anio);
      $p_dia_f = $p_dia->format('Y-m-d');
      $p_dia_ft = strtotime($p_dia_f);
      $u_dia = $my_date->modify('last day of '.$meses_t[$mes].' '.$anio);
      $u_dia_f = $u_dia->format('Y-m-d');
      $u_dia_ft = strtotime($u_dia_f);
      $lista = $this->db->asistencia()->select('fecha','hora_entrada','hora_salida','idpersona')->where('fecha >= "'.$p_dia_f.'" AND fecha <= "'.$u_dia_f.'" AND idpersona LIKE '.$id);
      $pers = $this->db->persona()->select('nombre','apellido')->where('idpersona',$id)->fetch();
      $datos['informacion']['nombre'] = $pers['nombre']." ".$pers['apellido'];
      $j = 0;
      $por = count($lista);
      for ($i=0; $i <= count($lista) ; $i++) {
        if (date('w',$lista[$i]['hora_entrada'])==1) {
          $datos['registro']['lunes'][$j] = $lista[$i];
          $j += 1;
        }
      }
      $j = 0;
      for ($i=0; $i <= $por ; $i++) {
        if (date('w',$lista[$i]['hora_entrada']) ==2) {
          $datos['registro']['martes'][$j]= $lista[$i];
          $j += 1;
        }
      }
      $j = 0;
      for ($i=0; $i <= $por ; $i++) {
        if (date('w',$lista[$i]['hora_entrada'])==3) {
          $datos['registro']['miercoles'][$j]=$lista[$i];
          $j += 1;
        }
      }
      $j = 0;
      for ($i=0; $i <= $por ; $i++) {
        if (date('w',$lista[$i]['hora_entrada'])==4) {
          $datos['registro']['jueves'][$j]= $lista[$i];
          $j += 1;
        }
      }
      $j = 0;
      for ($i=0; $i <= $por ; $i++) {
        if (date('w',$lista[$i]['hora_entrada'])==5) {
          $datos['registro']['viernes'][$j] = $lista[$i];
          $j += 1;
        }
      }
      $j = 0;
      for ($i=0; $i <= $por ; $i++) {
        if (date('w',$lista[$i]['hora_entrada'])==6) {
          $datos['registro']['sabado'][$j] = $lista[$i];
          $j += 1;
        }
      }
      //echo json_encode($datos['registro']); die();
      $datos['cantidad'] = ceil(count($lista)/6);
      //echo json_encode($datos['registro']); die();
      $datos['id']['persona'] = $id;
      return $this->view->render($response, '/trabajador/listaxmes.html',$datos);
  })->setName('busquedaxmes');

  $this->any('/lista_admin', function ($request, $response, $args) {
        $todo[]=0;
        $pa = $request->getAttribute('pa');
        $lista_num = $this->db->persona()->select("nombre","apellido")->where("idpersona",$trabajador)->count();
        $limite = 25;
        $paginas = $lista_num/$limite;
        $paginas =ceil($paginas);
        for ($i=1; $i <=$paginas ; $i++) {
          if ($i== 1) {
            $partir_de = 0;
            $todo['paginas'][$i]['id'] = $id;
            $todo['paginas'][$i]['no_pag'] = $i;
            $todo['paginas'][$i]['partir_de'] = $partir_de;
          }
          else {
            $partir_de = $limite + $partir_de;
            $todo['paginas'][$i]['id'] = $id;
            $todo['paginas'][$i]['no_pag'] = $i;
            $todo['paginas'][$i]['partir_de'] = $partir_de;
          }
        }

        $idtrab= $this->db->admin()->select("trabajador_idtrabajador");

        $usuariotrab= $this->db->admin()->select("usuario","activo");
        $trabajador = $this->db->trabajador()->select("persona_idpersona")->where("idtrabajador",$idtrab);
        $persona = $this->db->persona()->select("nombre","apellido")->where("idpersona",$trabajador)->limit($limite,$pa);


        foreach ($persona as $key => $value) {
            $todo['datos'][$key]['nombre'] = $value['nombre'];
            $todo['datos'][$key]['apellido'] = $value['apellido'];
        }


        foreach ($usuariotrab as $key => $value) {
            $todo['datos'][$key]['usuario'] = $value['usuario'];
            $todo['datos'][$key]['activo'] = $value['activo'];
        }
        foreach ($idtrab as $key => $value) {
            $todo['datos'][$key]['id'] = $value['trabajador_idtrabajador'];
        }
        //echo json_encode($todo); die();
        //echo json_encode($todo); die();
    return $this->view->render($response, '/trabajador/listaAdmin.html',$todo);
})->setName('admin');

$this->any('/update_admin/{id}', function ($request, $response, $args) {
    $id = $request->getAttribute('id');
    $Estado = $this->db->admin()->select("activo")->where("trabajador_idtrabajador",$id)->fetch();
    if ($Estado['activo']==1) {
          $asistencia=$this->db->admin()->where("trabajador_idtrabajador",$id)->update(array('activo'=>0));
    }
    else
    {
        $asistencia=$this->db->admin()->where("trabajador_idtrabajador",$id)->update(array('activo'=>1));
    }
    return $response->withRedirect('/trabajador/lista_admin');
})->setName('updateAdm');

 $this->any('/buscar_administradores', function($request,$response,$args){
    $parsedBody = $request->getParsedBody();
    $idtrab= $this->db->admin()->select("trabajador_idtrabajador");
    $parsedBody['buscar'];
    $usuariotrab= $this->db->admin()->select("usuario","activo");
    $trabajador = $this->db->trabajador()->select("persona_idpersona")->where("idtrabajador",$idtrab);
    //posible or

    $buscar = $this->db->persona()->where('idpersona',$trabajador)->where('nombre LIKE ? ','%'.$parsedBody['buscar'].'%')->or('apellido LIKE ? ','%'.$parsedBody['buscar'].'%');
    //$personas = $this->db->persona()->where('idpersona ',$trabajador)->and('nombre LIKE ? %'.$parsedBody['buscar'].'%');
    //echo $buscar['datos'] = $this->db->persona()->where(array('idpersona'=>$trabajador, 'nombre LIKE ?'=>'%'.$parsedBody['buscar'].'%') );


    $i = 0;
    foreach ($buscar as $key => $value) {
        $todo['datos'][$i]['nombre'] = $value['nombre'];
        $todo['datos'][$i]['apellido'] = $value['apellido'];
        $i += 1;
    }
    $i = 0;
    foreach ($buscar as $key => $value) {
        $id[$i]=$value['idpersona'];
        $i += 1;
    }

    $trabajadorInverso = $this->db->trabajador()->select("idtrabajador")->where("persona_idpersona",$id);
    $admin_inversoU= $this->db->admin()->select("usuario","activo")->where("trabajador_idtrabajador",$trabajadorInverso);
    $i = 0;
    foreach ($admin_inversoU as $key => $value) {
         $todo['datos'][$i]['usuario'] = $value['usuario'];
         $todo['datos'][$i]['activo'] = $value['activo'];
         $i += 1;
    }
    //echo json_encode($todo); die();
    return $this->view->render($response, '/trabajador/listaAdmin.html',$todo);
  })->setName('Admin_buscar');

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
    $this->any('/admin', function ($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
         //se comprueba que sea un administrador
        $prueba = $this->db->admin()->select('password','activo')->where('usuario', $parsedBody['usuario'])->fetch();
        if ($prueba['activo']==1) {
            if (strcmp($prueba['password'], $parsedBody['password']) == 0) {
                return FALSE;
            }else{
              echo "<p style='color:red; margin:0;'>la contraseña es incorrecta.</p>";
              return TRUE;
            }
        }else{
          echo "<p style='color:red; margin:0;'>la contraseña es incorrecta.</p>";
          return TRUE;
        }

    });
    $this->any('/semanas', function ($request, $response, $args) {
      $parsedBody = $request->getParsedBody();
      $my_date = new DateTime();
      $dias_t = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
      $meses_t = array("january","february","march","april","may","june","july","august","september","october","november","december");
      $mes = $parsedBody['mes'];
      $anio = $parsedBody['anio'];
      //cantidad a utilizar en los ciclos
      $cant = array("first","second","third","fourth","fifth","sixth");
      //Representacion numerica del mes es = m;
      //conseguir el primer dia del mes
      $p_dia = $my_date->modify('first day of '.$meses_t[$mes].' '.$anio);
      //dar formato para comparar
      $p_dia_f = $p_dia->format('dmY');
      //dar formato para imprimir
      $p_dia_ft = $dias_t[$p_dia->format('w')].$p_dia->format(' - d-m-Y');
      //conseguir el ultimo dia del mes
      $u_dia = $my_date->modify('last day of '.$meses_t[$mes].' '.$anio);
      //dar formato para comparar
      $u_dia_f = $u_dia->format('dmY');
      //dar formato para imprimir
      $u_dia_ft = $dias_t[$u_dia->format('w')].$u_dia->format(' - d-m-Y');
      //conseguir el primer lunes del mes
      $p_lunes = $my_date->modify('first monday of '.$meses_t[$mes].' '.$anio);
      //dar formato para comparar
      $p_lunes_f = $p_lunes->format('dmY');
      //dar formato para imprimir
      $p_lunes_ft = $dias_t[$p_lunes->format('w')].$p_lunes->format(' - d-m-Y');
      //conseguir el ultimo lunes del mes
      $u_lunes = $my_date->modify('last monday of '.$meses_t[$mes].' '.$anio);
      //dar formato para comparar
      $u_lunes_f = $u_lunes->format('dmY');
      //dar formato para imprimir
      $u_lunes_ft = $dias_t[$u_lunes->format('w')].$u_lunes->format(' - d-m-Y');
      //conseguir el primer viernes del mes
      $p_viernes = $my_date->modify('first friday of '.$meses_t[$mes].' '.$anio);
      //dar formato para comparar
      $p_viernes_f = $p_viernes->format('dmY');
      //dar formato para imprimir
      $p_viernes_ft = $dias_t[$p_viernes->format('w')].$p_viernes->format(' - d-m-Y');
      //conseguir el ultimo viernes del mes
      $u_viernes = $my_date->modify('last friday of '.$meses_t[$mes].' '.$anio);
      //dar formato para comparar
      $u_viernes_f = $u_viernes->format('dmY');
      //dar formato para imprimir
      $u_viernes_ft = $dias_t[$u_viernes->format('w')].$u_viernes->format(' - d-m-Y');
      //revisar si el primer lunes no coincide con el primer dia en dado caso se busca la semana ultima del mes anterior
      if ($p_viernes_f < $p_lunes_f) {
        if ($p_lunes->format('m')==01) {
          // en caso de que el mes sea enero se aplica esta condicion
          $an_s = $anio -1;
          $p_semana = $my_date->modify('last monday of '.$meses_t[11].' '.$an_s);
          $p_semana_l = $dias_t[$p_semana->format('w')].$p_semana->format(' - d-m-Y');
          $p_semana_v = $p_viernes_ft;
        }
        else {
          $p_semana = $my_date->modify('last monday of '.$meses_t[$mes-1].' '.$anio);
          $p_semana_l = $dias_t[$p_semana->format('w')].$p_semana->format(' - d-m-Y');
          $p_semana_v = $p_viernes_ft;
        }
      }
      //revisar si el ultimo viernes coincide con el ultimo dia en dado caso se busca la primer semana del mes siguiente y sea distinto de un sabado o domingo
      if ($my_date->modify('last day of '.$meses_t[$mes].' '.$anio)->format('w') && $my_date->modify('last day of '.$meses_t[$mes].' '.$anio)->format('w') != 6) {
        if ($u_viernes_f!=$u_dia_f) {
          if ($u_viernes->format('m')==12) {
            // en caso de que el mes sea diciembre se aplica esta condicion
            $an_t = $anio +1;
            $u_semana_l = $u_lunes_ft;
            $u_semana = $my_date->modify('first friday of '.$meses_t[0].' '.$an_t);
            $u_semana_v = $dias_t[$u_semana->format('w')].$u_semana->format(' - d-m-Y');
          }
          else {
            $u_semana_l = $u_lunes_ft;
            $u_semana = $my_date->modify('first friday of '.$meses_t[$mes+1].' '.$anio);
            $u_semana_v = $dias_t[$u_semana->format('w')].$u_semana->format(' - d-m-Y');
          }

        }
      }
      $i = 0;
      $j = 0;
      $l = 0;
      $v = 0;
      //ciclo en el que se registraran en el array todas las semanas del mes
      do {
        if ($j==0 && $p_viernes_f < $p_lunes_f) {
          $datos['semana'][$j]['lunes'] = $p_semana_l;
          $datos['semana'][$j]['viernes'] = $p_semana_v;
          echo "<option value='".$datos['semana'][$j]['lunes']." - ".$datos['semana'][$j]['viernes']."'>".$datos['semana'][$j]['lunes']." - ".$datos['semana'][$j]['viernes']."</option>";
          $v = $v+1;
        }
        else {
           $lunes = $my_date->modify($cant[$l].' monday of '.$meses_t[$mes].' '.$anio);
           $datos['semana'][$j]['lunes'] = $dias_t[$lunes->format('w')].$lunes->format(' - d-m-Y');
           $viernes = $my_date->modify($cant[$v].' friday of '.$meses_t[$mes].' '.$anio);
           $datos['semana'][$j]['viernes'] = $dias_t[$viernes->format('w')].$viernes->format(' - d-m-Y');
           $final = $viernes->format('dmY');
           echo "<option value='".$datos['semana'][$j]['lunes']." - ".$datos['semana'][$j]['viernes']."'>".$datos['semana'][$j]['lunes']." - ".$datos['semana'][$j]['viernes']."</option>";
           $l = $l +1;
           $v = $v +1;
        }
        $j = $j +1;
        if ($final == $u_viernes_f) {
          $i = $i + 1;
        }
      } while ($i != 1);
      if ($my_date->modify('last day of '.$meses_t[$mes].' '.$anio)->format('w') != 0 && $my_date->modify('last day of '.$meses_t[$mes].' '.$anio)->format('w') != 6) {
        if ($u_viernes_f!=$u_dia_f) {
          $j = $j+1;
          $datos['semana'][$j]['lunes'] = $u_semana_l;
          $datos['semana'][$j]['viernes'] = $u_semana_v;
          echo "<option value='".$datos['semana'][$j]['lunes']." - ".$datos['semana'][$j]['viernes']."'>".$datos['semana'][$j]['lunes']." - ".$datos['semana'][$j]['viernes']."</option>";
        }
      }
    });
        return true;
});
