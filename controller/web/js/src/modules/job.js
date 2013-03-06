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

  app.JobComplete = function(job, callback) {
    // Artificially defer the delete callback so ember can
    // finish updating the model before we remove it.
    setTimeout(function() {
      job.deleteRecord();
      job.store.commit();
    }, 1);
    callback(null);
  };

  app.JobError = function(callback) {
    callback('Job could not be executed.');
  };
});
