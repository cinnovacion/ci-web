(function($){
    $(function(){

        function centrar(elemento) {
            var el_alto = $(elemento).height();
            var pantalla_alto = $(window).height();
            var nav = $('nav').height();
            var margen = ((pantalla_alto / 2) - (el_alto / 2)) - nav;
            $(elemento).css('margin-top', margen);
        }


    	// Funcionalidad de las pestañas
    	$('#trabajadores a').on('click', function() {
            var titulo = $(this).attr("class");
            if (titulo.indexOf("active") == -1) {
                $(this).addClass('active');
                $('#tab_tbj').toggle();
                $('#voluntarios a').removeClass('active');
                $('#tab_vol').hide();
            }
    	});

    	$('#voluntarios a').on('click', function() {
            var titulo = $(this).attr("class");
            if (titulo.indexOf("active") == -1) {
                $(this).addClass('active');
                $('#tab_vol').toggle();
                $('#trabajadores a').removeClass('active');
                $('#tab_tbj').hide();
            }
    	});

        //MODAL LOGIN
        $('#btn_admin').click(function(){
            $('#modal_login').css('display', 'block');
        });
        $('.close').click(function(){
           $('#modal_login').css('display', 'none');
        });
        $(window).click(function(event){
            if (event.target.id == $('#modal_login').attr('id')) {
                $('#modal_login').css('display', 'none');
            }
        });

        //radio-button motivos de visitas
        $('#tipo_visita_0').click(function(){
            if ($(this).is(':checked')) {
                $('#motivo_interno').css('display', 'block');
                $('#motivo_externo').css('display', 'none');
            }
        });
        $('#tipo_visita_1').click(function(){
            if ($(this).is(':checked')) {
                $('#motivo_interno').css('display', 'none');
                $('#motivo_externo').css('display', 'block');
            }
        });

        //Agregar Org
        $('#btn_agr_org').click(function(){
            $('#modal_agr_org').css('display', 'block');
        });
        $('.close').click(function(){
           $('#modal_agr_org').css('display', 'none');
        });
        $(window).click(function(event){
            if (event.target.id == $('#modal_agr_org').attr('id')) {
                $('#modal_agr_org').css('display', 'none');
            }
        });

        //Agregar Carrera
        $('#btn_agr_carr').click(function(){
            $('#modal_agr_carr').css('display', 'block');
        });
        $('.close').click(function(){
           $('#modal_agr_carr').css('display', 'none');
        });
        $(window).click(function(event){
            if (event.target.id == $('#modal_agr_carr').attr('id')) {
                $('#modal_agr_carr').css('display', 'none');
            }
        });

        //Agregar Actividad
        $('#btn_agr_mot').click(function(){
            $('#modal_agr_mot').css('display', 'block');
        });
        $('.close').click(function(){
           $('#modal_agr_mot').css('display', 'none');
        });
        $(window).click(function(event){
            if (event.target.id == $('#modal_agr_mot').attr('id')) {
                $('#modal_agr_mot').css('display', 'none');
            }
        });

        //Agregar Horas
        $('#btn_agr_hora').click(function(){
            $('#modal_agr_hora').css('display', 'block');
        });
        $('.close').click(function(){
           $('#modal_agr_hora').css('display', 'none');
        });
        $(window).click(function(event){
            if (event.target.id == $('#modal_agr_hora').attr('id')) {
                $('#modal_agr_hora').css('display', 'none');
            }
        });

        $('#btn_agr_activ').click(function(){
            $('#modal_agr_activ').css('display', 'block');
        });
        $('.close').click(function(){
           $('#modal_agr_activ').css('display', 'none');
        });
        $(window).click(function(event){
            if (event.target.id == $('#modal_agr_activ').attr('id')) {
                $('#modal_agr_activ').css('display', 'none');
            }
        });

        // ALERT DISPLAY
        $('.closebtn').click(function(){
            $(this).parent().css('display', 'none');
        });

        $('#submit').click(function(event){
            event.preventDefault();
            var x = $('.cedula_val').val();
            if (x.length<16 && x.length>0) {
              var y = x.substring(0,3)+"-"+x.substring(3,9)+"-"+x.substring(9);
              $('.cedula_val').val(y);
            }
            $.ajax({
                url: "/ajax/cedula",
                data: { cedula : $('#cedula').val() },
                type: 'POST',
                dataType: 'html',
                success: function(html){
                    if(html.length>2) {
                        $('body').css('background-color', '#448AFF');
                        $('.container').html(html);
                    }
                    else {
                        $('#cedula').css('border-color', 'red');
                        $('.alert').css('display', 'block');
                    }
                }
            });
        });
        $('#admin').click(function(event){
          event.preventDefault();
          $.ajax({
            url: "/ajax/admin",
            data: {usuario: $('#usuario').val(),password:$('#password').val()},
            type: 'POST',
            dataType: 'html',
            success: function(html){
              if (html.length>2) {
                $('.adm').css('display', 'block');
                $('#usuario').focus();
                $('#usuario').css('border-color', 'red');
                $('#password').css('border-color', 'red');
              }
              else {
                $('#adm').submit();
              }
            }
          });
        });
    	  $( "#datepicker" ).datepicker($.datepicker.regional[ "es" ]);
        $( "#accordion" ).accordion({
          collapsible: true,
          active: false,
          heightStyle: "fill",
        });

        $( "#accordion" ).accordion({
          collapsible: true,
          active: false,
          heightStyle: "fill",
        });

        //Registrar admin
        $('#btn_agr_admin').click(function(){
            $('#modal_agr_admin').css('display', 'block');
        });
        $('.close').click(function(){
           $('#modal_agr_admin').css('display', 'none');
        });
        $(window).click(function(event){
            if (event.target.id == $('#modal_agr_admin').attr('id')) {
                $('#modal_agr_admin').css('display', 'none');
            }
        });

        //Degradar admin
        $('#btn_deg_admin').click(function(){
            $('#modal_deg_admin').css('display', 'block');
        });
        $('.close').click(function(){
           $('#modal_deg_admin').css('display', 'none');
        });
        $(window).click(function(event){
            if (event.target.id == $('#modal_deg_admin').attr('id')) {
                $('#modal_deg_admin').css('display', 'none');
            }
        });
        $('#can_admin').click(function(event){
          event.preventDefault();
          $('#modal_deg_admin').css('display', 'none');
        })

        //Validar contraseña en el registro de administradores
        $('#reg_admin').click(function(){
          if ($('#pass').val() != $('#pass_d').val()) {
            event.preventDefault();
            $('.adm').css('display', 'block');
            $('#pass_d').focus();
            $('#pass_d').css('border-color','red')
          }
        });
        //Corregir el ingreso de la cedula
        $('.submit_reg').click(function(){
          var x = $('.cedula_val').val();
          if (x.length<16 && x.length>0) {
            var y = x.substring(0,3)+"-"+x.substring(3,9)+"-"+x.substring(9);
            $('.cedula_val').val(y);
          }
        });
          //ajax para la busqueda por semana
          $(document).ready(function() {
            if ($('#js').text()=='cedula') {
              $('#asis').addClass('js');
              $('#js_vol').removeClass('js');
              $('#js_emp').removeClass('js');
              $('#js_vis').removeClass('js');
            }
            if ($('#js').text()=='visitas') {
              $('#js_vis').addClass('js');
              $('#asis').removeClass('js');
              $('#js_vol').removeClass('js');
              $('#js_emp').removeClass('js');
            }
            if ($('#js').text()=='voluntarios') {
              $('#js_vol').addClass('js');
              $('#asis').removeClass('js');
              $('#js_vis').removeClass('js');
              $('#js_emp').removeClass('js');
            }
            if ($('#js').text()=='trabajador') {
              $('#js_emp').addClass('js');
              $('#asis').removeClass('js');
              $('#js_vis').removeClass('js');
              $('#js_vol').removeClass('js');
            }

            $('.all_cmd').css('background-color','#7FC836');
            $("#years").change(function(){
              $('select[id=years]').val();
              $('#year').val($(this).val());
              $.ajax({
                url: "/ajax/semanas",
                data: {anio:$('#year').val(),mes:$('#month').val()},
                type: 'POST',
                dataType: 'html',
                success: function(html){
                  $('#weeks').html(html);
                }
              });
            });
            $("#months").change(function(){
              $('select[id=months]').val();
              $('#month').val($(this).val());
              $.ajax({
                url: "/ajax/semanas",
                data: {anio:$('#year').val(),mes:$('#month').val()},
                type: 'POST',
                dataType: 'html',
                success: function(html){
                  $('#weeks').html(html);
                }
              });
            });
            $("#weeks").change(function(){
              var w = $('select[id=weeks]').val();
              var i = w.substring(8,18);
              var f = w.substring(31);
              $('#f_inicio').val(i);
              $('#f_final').val(f);
            });
            // Instrucciones a ejecutar al terminar la carga
            $('#years').change();
            $('#months').change();
            $('#weeks').change();
          });
          $('#b_semana').click(function(){
            $('#weeks').change();
          });
          $('.act_cmd').click(function(){
            $('#act_adm').css('display','block');
            $('#inact_adm').css('display','none');
            $('#all_adm').css('display','none');
            $('.act_cmd').css('background-color','#7FC836');
            $('.inact_cmd').css('background-color','#448AFF');
            $('.all_cmd').css('background-color','#448AFF');
          })
          $('.inact_cmd').click(function(){
            $('#act_adm').css('display','none');
            $('#inact_adm').css('display','block');
            $('#all_adm').css('display','none');
            $('.inact_cmd').css('background-color','#7FC836');
            $('.act_cmd').css('background-color','#448AFF');
            $('.all_cmd').css('background-color','#448AFF');
          })
          $('.all_cmd').click(function(){
            $('#act_adm').css('display','none');
            $('#inact_adm').css('display','none');
            $('#all_adm').css('display','block');
            $('.all_cmd').css('background-color','#7FC836');
            $('.act_cmd').css('background-color','#448AFF');
            $('.inact_cmd').css('background-color','#448AFF');
          })

    }); // Fin de documento listo
})(jQuery); // fin de espacio de nombre jquery
