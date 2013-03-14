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

  return app;
});
