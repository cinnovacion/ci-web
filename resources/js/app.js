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
        $('#f_step').click(function(event){
            event.preventDefault();
            $.ajax({
                url: "/ajax/f_step",
                data: { id : $('#idpersona').val() },
                type: 'POST',
                dataType: 'html',
                success: function(html){
                        $('body').css('background-color', '#448AFF');
                        $('.container').html(html);
                }
            });
        });
    	  $( "#datepicker" ).datepicker($.datepicker.regional[ "es" ]);
        $( "#accordion" ).accordion({
          collapsible: true,
          active: false,
          heightStyle: "fill"
        });
    }); // Fin de documento listo
})(jQuery); // fin de espacio de nombre jquery
