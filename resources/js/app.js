(function($){
    $(function(){

    	// Funcionalidad de las pestañas
    	$('#trabajadores a').on('click', function() {
    		$(this).addClass('active');
    		$('#voluntarios a').removeClass('active');

    		$('#tab_vol').hide();
    		$('#tab_tbj').toggle();
    	});

    	$('#voluntarios a').on('click', function() {
    		$(this).addClass('active');
    		$('#trabajadores a').removeClass('active');

    		$('#tab_tbj').hide();
    		$('#tab_vol').toggle();
    	});

    }); // Fin de documento listo
})(jQuery); // fin de espacio de nombre jquery