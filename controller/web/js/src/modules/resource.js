/**
 * @fileoverview Resource models, views, and controllers.
 */
define(['ember', 'ember-data', 'jquery', 'app'], function(Ember, DS, $, app) {
  app.Resource = DS.Model.extend({
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
    }.property('status')
  });

  app.ResourceController = Ember.ObjectController.extend();

  app.ResourceView = Ember.View.extend({
    templateName: 'resource'
  });
});
