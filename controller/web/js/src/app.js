/**
 * @fileoverview Training Wheels Ember.js application instantiation.
 */
define(['ember', 'jquery'], function(Ember, $) {
  var app = Ember.Application.create({LOG_TRANSITIONS: true});

  // Expose the app to the global namespace to make Ember happy.
  window.App = app;

  app.globalStrings = {
    confirmSync: 'Are you sure you want to sync resources to this user? This will overwrite any changes the user has made to their environment.',
    confirmSyncAll: 'Are you sure you want to sync resources to all users? This will overwrite any changes users have made to their environments.'
  };

  /**
   * Helper function to reload an array of models.
   *
   * @param {array} models
   *   An array of models to be reloaded.
   * @return {object} a jQuery promise object.
   */
  app.reloadModels = function(models) {
    var def = $.Deferred();
    var promises = [];

    models.forEach(function(model) {
      var p = $.Deferred();
      promises.push(p);
      model.on('didReload', function() {
        p.resolve();
      });
      model.on('becameError', function() {
        p.reject();
      });
      model.reload();
    });

    $.when.apply($, promises).then(
      function() {
        def.resolve();
      },
      function() {
        def.reject();
      }
    );

    return def.promise();
  };

  return app;
});
