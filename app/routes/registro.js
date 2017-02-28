import Ember from 'ember';
import moment from 'moment';

var registro;
var elementos = [
    'nombre',
    'cedula',
    'org',
    'area'
];

export default Ember.Route.extend({
    model() {
        var contenido = $.getJSON('dist/assets/ton.json');
        console.log();
        registro = 
        [{
            nombre  : 'Nestor Brenes',
            cedula  : 'xxxxxxxx',
            org     : 'FZT',
            area    : 'CI',
            entrada : '8:00 am'
        },
        {
            nombre  : 'Nestor Bonilla',
            cedula  : 'xxxxxxxx',
            org     : 'FZT',
            area    : 'CI',
            entrada : '8:00 am'
        },
        {
            nombre  : 'Alice',
            cedula  : 'xxxxxxxx',
            org     : 'FZT',
            area    : 'SP',
            entrada : '8:00 am'
        }];
        return registro;    
    },

    actions: {
        registrar_entrada: function(){
            var nombre  = $('#nombre').val();
            var cedula  = $('#cedula').val();
            var org     = $('#org').val();
            var area    = $('#area').val();
            var entrada = moment().format('LT');

            if ( !nombre || !cedula ) {
                alert('Llene los campos obligatorios');
                $('#nombre').css('border', '0.1rem solid red');
                $('#cedula').css('border', '0.1rem solid red');
            } else {
                var obj     = {
                    nombre  : nombre,
                    cedula  : cedula,
                    org     : org,
                    area    : area,
                    entrada : entrada
                }

                elementos.forEach(function(item) {
                    $('#' + item).css('border', '0.1rem solid #d1d1d1');
                    $('#' + item).val('');
                });
                
                registro.addObject(obj);
            }
        },
        registrar_entrada: function(event){
            var salida = moment().format('LT');
            console.log(this.get('id'));
        }
    }
});


