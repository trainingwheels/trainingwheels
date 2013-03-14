/**
 * @fileoverview Job models and helper functions.
 */
define(['ember-data', 'app'], function(DS, app) {
  app.Job = DS.Model.extend({
    course_id: DS.attr('number'),
    type: DS.attr('string'),
    action: DS.attr('string'),
    params: DS.attr('string')
  });
});
