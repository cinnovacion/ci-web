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


//radio-button motivos de visitas
function mostrarReferencia(){
if (document.registro.tipo_visita[0].checked == true) {
document.getElementById('motivo_interno').style.display='block';
document.getElementById('motivo_externo').style.display='none';
} else {
    if (document.registro.tipo_visita[1].checked == true) {
document.getElementById('motivo_interno').style.display='none';
document.getElementById('motivo_externo').style.display='block';
}
}
}
function reset(){
    document.getElementById('registro').reset();
}