/**
 * @fileoverview Course models, views, and controllers.
 */
define([
  'ember',
  'ember-data',
  'jquery',
  'alertify',
  'app'
], function(Ember, DS, $, alertify, app) {

  app.CourseSummary = DS.Model.extend({
    course_name: DS.attr('string'),
    description: DS.attr('string'),
    env_type: DS.attr('string'),
    title: DS.attr('string'),
    host: DS.attr('string'),
    user: DS.attr('string'),
    port: DS.attr('string')
  });

  app.Course = app.CourseSummary.extend({
    users: DS.hasMany('App.UserSummary'),
    // TODO: Replace with hasOne when PR https://github.com/emberjs/data/pull/475 gets in.
    instructor: DS.hasMany('App.UserSummary'),
    didLoad: function() {
      alertify.success('Course "' + this.get('title') + '" loaded.');
    }
  });

  app.CourseController = Ember.ObjectController.extend({
    course_id: 0,
    allUserSummaries: [],
    userSummariesAbove: [],
    userSummariesBelow: [],
    userSelected: [],
    instructor: [],
    instructorSummary: [],
    instructorSelected: [],
    userController: {},

    refreshCourse: function() {
      alertify.success('Refreshing the course');
    },

    addUser: function() {
      var newUserName = this.get('newUserName');
      this.set('newUserName', '');
      var course_id = this.get('course_id');
      var model = app.UserSummary.createRecord({user_name: newUserName, course_id: course_id, resource_status: "resource-missing"});
      model.store.commit();
      this.resetUsers();
      this.transitionToRoute('course');
    },

    selectUser: function(user_id) {
      var pieces = user_id.split('-');
      var user = this.get('allUserSummaries').findProperty('id', user_id);

      if (pieces[1] === 'instructor') {
        this.set('instructorSelected', this.get('instructor'));
        this.set('instructorSummary', []);

        this.set('userSummariesAbove', this.get('allUserSummaries'));
        this.set('userSummariesBelow', []);
        this.set('userSelected', []);
      }
      else {
        if (this.get('instructorSelected').get('length') > 0) {
          this.set('instructorSummary', this.get('instructor'));
          this.set('instructorSelected', []);
        }

        var index = this.get('allUserSummaries').indexOf(user);
        this.set('userSummariesAbove', this.get('allUserSummaries').slice(0, index));
        this.set('userSelected', this.get('allUserSummaries').slice(index, index + 1));
        this.set('userSummariesBelow', this.get('allUserSummaries').slice(index + 1, 10000));
      }
    },

    syncAll: function(callback) {
      var course_id = this.get('course_id');

      // Collect all of the students to have their resources synced.
      var users = app.UserSummary
        .filter(function (data) {
          if (data.get('course_id') == course_id && data.get('is_student')) {
            return true;
          }
        })
        .map(function(item) {
          return item.get('user_name');
        });

      // Create the sync job.
      var job = app.Job.createRecord({
        course_id: this.controllerFor('course').get('course_id'),
        type: 'resource',
        action: 'resourceSync',
        params: JSON.stringify({
          source_user: 'instructor',
          target_users: users
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

    /**
     * Helper function to reload user data from the server.
     *
     * @param {bool} instructor
     *   true if the instructor user should be reloaded.
     *   Defaults to true.
     */
    reloadUsers: function(instructor, callback, errorCallback) {
      var users;
      var course_id = this.get('course_id');

      if (typeof instructor === 'undefined') {
        instructor = true;
      }

      // Find the already loaded users so we can reload them.
      users = app.User.filter(function(data) {
        if (data.get('course_id') != course_id) {
          return false;
        }

        if (!instructor && data.get('user_name') === 'instructor') {
          return false;
        }

        return true;
      });

      var models = users.toArray();
      var model = this.get('model');
      models.push(model);
      var promise = app.reloadModels(models);
      $.when(promise).then(callback, errorCallback);
    },

    resetUsers: function() {
      var course_id = this.get('course_id');
      var users = app.UserSummary.filter(function (data) {
        if (data.get('course_id') == course_id && data.get('user_name') != 'instructor') {
          return true;
        }
      });
      this.set('allUserSummaries', users);
      this.set('userSummariesAbove', users);
      this.set('userSummariesBelow', []);
      this.set('userSelected', []);

      var instructor = app.UserSummary.filter(function (data) {
        if (data.get('user_name') == 'instructor' && data.get('course_id') == course_id) {
          return true;
        }
      });
      this.set('instructor', instructor);
      this.set('instructorSummary', instructor);
      this.set('instructorSelected', []);
    },

    // This collapses all the displayed users and returns to /courses/x
    returnToCourse: function() {
      this.resetUsers();
      this.transitionToRoute('course');
    }
  });

  app.CourseView = Ember.View.extend({
    templateName: 'course',
    sortOptions: ['name', 'id'],
    css_class_syncing: function() {
      return 'sync-wrapper' + (this.get('syncing') ? ' syncing' : '');
    }.property('syncing'),

    syncAll: function(user_name) {
      var self = this;
      alertify.confirm(app.globalStrings.confirmSyncAll, function confirmedSync(e) {
        if (e) {
          self.set('syncing', true);

          self.controller.syncAll(function usersSynced(err) {
            if (!err) {
              self.controller.reloadUsers(false,
                function usersReloaded() {
                  self.set('syncing', false);
                  alertify.success("Successfully synced resources from 'instructor' to all users.");
                },
                function reloadError() {
                  self.set('syncing', false);
                  alertify.error("There was a problem syncing resources to one or more users.");
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
