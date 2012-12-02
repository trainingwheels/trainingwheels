(function(win, $) {
  'use strict';

  // Shorthand for logging easily using cl('message');
  var cl = console.log.bind(console);

  win.App = Ember.Application.create();
  var App = win.App;

  ////
  // Ember Data
  //
  App.store = DS.Store.create({
    revision: 8,
    adapter: DS.RESTAdapter.create({
      bulkCommit: false,
      namespace: 'rest',
      // Plurals are used when formatting the URLs, so if you have a
      // App.CourseSummary, and you attempt to populate it using findAll(),
      // the actual request will be GET /rest/course_summaries
      plurals: {
        course_summary: 'course_summaries',
      },
      // Only required on the main key for the request, so /rest/courses/1
      // shoud return { courses: [{}] }, the first course in an array with
      // the key mapping here to the type.
      mappings: {
        courses: 'App.Course',
      }
    })
  });

  App.CourseSummary = DS.Model.extend({
    course_name: DS.attr('string'),
    course_type: DS.attr('string'),
    description: DS.attr('string'),
    env_type: DS.attr('string'),
    repo: DS.attr('string'),
    title: DS.attr('string'),
    uri: DS.attr('string')
  });

  App.Course = App.CourseSummary.extend({
    users: DS.hasMany('App.UserSummary'),
    // TODO: Replace with hasOne when PR https://github.com/emberjs/data/pull/475 gets in.
    instructor: DS.hasMany('App.UserSummary'),
    didLoad: function() {
      alertify.success('Course "' + this.get('title') + '" loaded.');
    }
  });

  App.UserSummary = DS.Model.extend({
    user_name: DS.attr('string'),
    password: DS.attr('string'),
    logged_in: DS.attr('boolean'),
    course_id: DS.attr('number')
  });

  App.User = App.UserSummary.extend({
    resources: DS.hasMany('App.Resource')
  });

  App.Resource = DS.Model.extend({
    key: DS.attr('string'),
    title: DS.attr('string'),
    exists: DS.attr('boolean'),
    type: DS.attr('string'),
  })

  ////
  // Controllers & Views
  //
  App.ApplicationController = Ember.Controller.extend();
  App.ApplicationView = Ember.View.extend({
    templateName: 'application'
  });

  App.CoursesController = Ember.ArrayController.extend();
  App.CoursesView = Ember.View.extend({
    templateName: 'courses'
  });

  App.CourseController = Ember.ObjectController.extend({
    refreshCourse: function() {
      alertify.success('Refreshing the course');
    },
    addUser: function() {
      App.store.createRecord(App.UserSummary, {user_name: "newuser", course_id: 1});
      alertify.success('Adding a user');
    }
  });
  App.CourseView = Ember.View.extend({
    templateName: 'course',
    sortOptions: ['name', 'id']
  });

  App.UserSummaryController = Ember.ObjectController.extend({
    user_logged_in_class: 'user-logged-in'
  });
  App.UserSummaryView = Ember.View.extend({
    templateName: 'user-summary',
    css_class_login_status: 'user-login-status'
  });

  ////
  // Router
  //
  App.Router = Ember.Router.extend({
    enableLogging: true,

    goHome: Ember.Route.transitionTo('courses'),

    root: Ember.Route.extend({

      // Going to the home page currently redirects you to the list of courses.
      index: Ember.Route.extend({
        route: '/',
        redirectsTo: 'courses'
      }),

      // List of the courses in the system.
      courses: Ember.Route.extend({
        route: '/courses',

        showCourse: Ember.Route.transitionTo('course'),

        connectOutlets: function(router) {
          router.get('applicationController').connectOutlet('courses', App.store.findAll(App.CourseSummary));
        }
      }),

      // Page showing a single course details and all the users.
      course: Ember.Route.extend({
        route: '/course/:course_id',

        connectOutlets: function(router, context) {
          var course_id = context.id;
          // When we .find() a course, the hasMany relationships in the store
          // will auto-fill the users summaries, so we can just use .filter() below
          // when we load the users, which doesn't trigger another request.
          var course = App.store.find(App.Course, course_id);
          router.get('applicationController').connectOutlet('course', course);

          // Remember that this data store is not materialized right here, the requests
          // are async and once the data enters the store, the front end is updated. Try
          // this in the console if you want to look at the data:
          // App.store.filter(App.User, function(data) { return true; } ).objectAt(0).toData()
          var courseController = router.get('courseController');
          var users = App.store.filter(App.UserSummary, function (data) {
            if (data.get('course_id') == course_id && data.get('user_name') != 'instructor') {
              return true;
            }
          });
          courseController.users = users;

          var instructor = App.store.filter(App.UserSummary, function (data) {
            if (data.get('user_name') == 'instructor' && data.get('course_id') == course_id) {
              return true;
            }
          });
          courseController.instructor = instructor;
        },

        serialize: function(router, course) {
          return {
            course_id: course.get('id')
          }
        },

        deserialize: function(router, urlParams) {
          return App.store.find(App.Course, urlParams.course_id);
        }

      })
    })
  })

  App.initialize();

})(window, jQuery);
