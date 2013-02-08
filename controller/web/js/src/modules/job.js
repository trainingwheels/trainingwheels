define(['ember-shim', 'ember-data'], function(Ember, DS) {
  var Job = {};

  Job.Job = DS.Model.extend({
    course_id: DS.attr('number'),
    type: DS.attr('string'),
    action: DS.attr('string'),
    params: DS.attr('string'),
  });

  Job.JobComplete = function(job, callback) {
    // Artificially defer the delete callback so ember can
    // finish updating the model before we remove it.
    setTimeout(function() {
      job.deleteRecord();
      job.store.commit();
    }, 1);
    callback(null);
  };

  Job.JobError = function(callback) {
    callback('Job could not be executed.');
  };

  return Job;
});
