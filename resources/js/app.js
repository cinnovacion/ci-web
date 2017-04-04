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

    }); // Fin de documento listo
<<<<<<< HEAD
})(jQuery); // fin de espacio de nombre jquery
=======
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

>>>>>>> 480dde0bb4040d43f6bc442bf079904e263ecba2
