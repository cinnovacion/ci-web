import Ember from 'ember';
var reg;

export default Ember.Route.extend({
    model() {
       reg = [{
        nombre: 'Nestor Brenes',
        cedula: 'xxxxxxxx',
        org: 'FZT',
        area: 'CI'},
        {
        nombre: 'Nestor Bonilla',
        cedula: 'xxxxxxxx',
        org: 'FZT',
        area: 'CI'},
        {
        nombre: 'Alice',
        cedula: 'xxxxxxxx',
        org: 'FZT',
        area: 'SP'
    }];

    return reg;    
},

    actions: {
        click: function(){
            var name = $('#name').val();
            var ced = $('#ced').val();
            var org = $('#org').val();
            var area = $('#area').val();
            var obj = {
                nombre : name,
                cedula : ced,
                org : org,
                area : area
            }
            reg.addObject(obj);
            console.log(obj);
      }
    }
});


