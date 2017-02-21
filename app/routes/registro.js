import Ember from 'ember';

export default Ember.Route.extend({
	model() {
    return [{
      nombre: 'Nestor Brenes',
      cedula: 'xxxxxxxx',
      org: 'FZT',
      area: 'CI'
  	}];
}
    
});
