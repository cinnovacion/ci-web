(function($){
    $(function(){
        // posicion de formulacio de asistencia
        var login_alto = $('.login').height();
        var pantalla_alto = $(window).height();
        var nav = $('nav').height();
        var margen = ((pantalla_alto / 2) - (login_alto / 2)) - nav;
        $('.login').css('margin-top', margen);

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

        //Agregar Área
        $('#btn_agr_area').click(function(){
            $('#modal_agr_area').css('display', 'block');
        });
        $('.close').click(function(){
           $('#modal_agr_area').css('display', 'none');
        });
        $(window).click(function(event){
            if (event.target.id == $('#modal_agr_area').attr('id')) {
                $('#modal_agr_area').css('display', 'none');
            }
        });

        //Agregar Actividad
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
                success: function(result){
                    if(result != ' ') {
                        var html = $('.container').html();
                        var persona = result;
                        var validar = '<div class="column column-50 column-offset-25 alto"><div class="centrar"><h3 style="margin-top:20px; color:white;">Tu eres '+persona+'?</h3><button style="background-color:#7fc836;">Si</button><button style="background-color:#7fc836;">No</button></div></div></div>';
                        $('body').css('background-color', '#448AFF');

                        $('.container').html(validar);
                    }
                    else {
                        $('#cedula').css('border-color', 'red');
                        $('.alert').css('display', 'block');
                    }
                }
            });
        });

    }); // Fin de documento listo
})(jQuery); // fin de espacio de nombre jquery
