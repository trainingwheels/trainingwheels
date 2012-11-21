//
// A simple relational model for Backbone.
// @see http://slashhashbang.com/2011/10/lightweight-relation-modeling-with-backbone/
//
(function() {
  function delegateModelEvents(from, to, eventKey) {
    from.bind('all', function(eventName) {
      var args = _.toArray(arguments);
      if (eventKey) {
        args[0] = eventKey + ':' + args[0];
      }
      to.trigger.apply(to, args);
    });
  }

  function getUpdateOp(model) {
    return (model instanceof Backbone.Collection) ? 'reset' : 'set';
  }

  Backbone.RelationalModel = Backbone.Model.extend({
    relations: {},
    set: function(attrs, options) {
      _.each(this.relations, function(constructor, key) {
        var relation = this[key];

        // set up relational model if it's not there yet
        if (!relation) {
          relation = this[key] = new constructor();

          // makes it so relation events are triggered out
          // e.g. 'add' on a relation called 'collection' would
          // trigger event 'collection:add' on this model
          delegateModelEvents(relation, this, key);
        }

        // check to see if incoming set will affect relation
        if (attrs[key]) {
          // perform update on relation model
          relation[ getUpdateOp(relation) ](attrs[key], options);

          // remove from attr hash, prevents duplication of data +
          // keeps models out of attributes, which should be only used for
          // dumb JSON attributes
          delete attrs[key];
        }
      }, this);

      return Backbone.Model.prototype.set.call(this, attrs, options);
    }
  });
})();
