define(['ember-shim', 'ember-data', 'jquery'], function(Ember, DS, $) {
  var Resource = {};

  Resource.Resource = DS.Model.extend({
    key: DS.attr('string'),
    title: DS.attr('string'),
    exists: DS.attr('boolean'),
    status: DS.attr('string'),
    type: DS.attr('string'),
    user_id: DS.attr('string'),
    attribs: DS.attr('string'),
    attribsArray: function() {
      return $.parseJSON(this.get('attribs'));
    }.property('attribs'),
    css_class_resource_status: function() {
      return 'resource-status ss-folder ' + this.get('status');
    }.property('status'),
  });

  Resource.ResourceController = Ember.ObjectController.extend();

  Resource.ResourceView = Ember.View.extend({
    templateName: 'resource',
  });

  return Resource;
});
