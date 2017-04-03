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
//MODAL LOGIN
var modal = document.getElementById('modal_login');
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
