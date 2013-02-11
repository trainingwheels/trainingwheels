/**
 * @fileoverview User models, views, and controllers.
 */
define([
  'ember',
  'ember-data',
  'jquery',
  'alertify',
  'app'
], function(Ember, DS, $, alertify, app) {
  app.UserSummary = DS.Model.extend({
    course: DS.belongsTo('App.Course'),
    user_name: DS.attr('string'),
    password: DS.attr('string'),
    logged_in: DS.attr('boolean'),
    course_id: DS.attr('number'),
    resource_status: DS.attr('string'),
    is_student: function() {
      return this.get('user_name') !== 'instructor';
    }.property('user_name'),
    css_class_login_status: function() {
      return 'user-login-status ss-user ' + (this.get('logged_in') ? 'logged_in' : 'logged_out');
    }.property('logged_in'),
    css_class_resource_overview_status: function() {
      return 'resource-status ss-folder ' + this.get('resource_status');
    }.property('resource_status'),
    didCreate: function() {
      alertify.success('User "' + this.get('user_name') + '" created.');
    },
    becameError: function() {
      alertify.error('There was an error creating user "' + this.get('user_name') + '".');
    }
  });

  app.User = app.UserSummary.extend({
    resources: DS.hasMany('App.Resource')
  });

  app.UserSummaryController = Ember.ObjectController.extend();

  app.UserSummaryView = Ember.View.extend({
    templateName: 'user-summary',
  });

  app.UserController = Ember.ObjectController.extend({
    user_logged_in_class: 'user-logged-in',
    resources: [],

    bindResources: function(user_id) {
      var resources = app.Resource.filter(function (data) {
        if (data.get('user_id') == user_id) {
          return true;
        }
      });
      this.set('resources', resources);
    },

    copyPassword: function(password) {
      alertify.alert('<div id="selected-password">' + password + '</div>');
      setTimeout(function () { $('#selected-password').selectText(); }, 50);
    },

    /**
     * Helper function to reload user data from the server.
     */
    reloadUser: function(callback, errorCallback) {
      var models = [];
      models.push(this.get('model'));
      models.push(this.controllerFor('course').get('model'));

      var promise = app.reloadModels(models);
      $.when(promise).then(callback, errorCallback);
    },

    syncUser: function(user_name, callback) {
      var job = app.Job.createRecord({
        course_id: this.controllerFor('course').get('course_id'),
        type: 'resource',
        action: 'resourceSync',
        params: JSON.stringify({
          source_user: 'instructor',
          target_users: [ user_name ]
        })
      });
      job.store.commit();
      job.on('didCreate', function(record) {
        app.JobComplete(job, callback);
      });
      job.on('becameError', function(record) {
        app.JobError(callback);
      });
    },

    collapseUser: function() {
      var courseController = this.controllerFor('course');
      courseController.resetUsers();
      this.transitionToRoute('course');
    }
  });

  app.UserView = Ember.View.extend({
    templateName: 'user',
    syncing: false,

    css_class_syncing: function() {
      return 'sync-wrapper' + (this.get('syncing') ? ' syncing' : '');
    }.property('syncing'),

    syncUser: function(user_name) {
      var self = this;
      alertify.confirm(app.globalStrings.confirmSync, function syncConfirmed(e) {
        if (e) {
          self.set('syncing', true);

          self.controller.syncUser(user_name, function userSynced(err) {
            if (!err) {
              self.controller.reloadUser(
                function userReloaded() {
                  self.set('syncing', false);
                  alertify.success("Successfully synced resources from 'instructor' to '" + user_name + "'.");
                },
                function reloadError() {
                  self.set('syncing', false);
                  alertify.error("There was a problem syncing resources to '" + user_name + "'.");
                }
              );
            }
            else {
              self.set('syncing', false);
              alertify.error(err);
            }
          });
        }
      });
    }
  });
});
