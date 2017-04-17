(function($){
    $(function(){

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
    }); // Fin de documento listo
})(jQuery); // fin de espacio de nombre jquery
