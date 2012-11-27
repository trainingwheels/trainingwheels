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
      plurals: {
        user: 'users',
        course_summary: 'course_summaries'
      },
      mappings: {
        users: 'App.User',
        courses: 'App.Course',
        course_summaries: 'App.CourseSummary'
      }
    })
  });

  App.User = DS.Model.extend({
    user_name: DS.attr('string'),
    password: DS.attr('string'),
    logged_in: DS.attr('boolean'),
  });

  App.Course = DS.Model.extend({
    course_name: DS.attr('string'),
    course_type: DS.attr('string'),
    description: DS.attr('string'),
    env_type: DS.attr('string'),
    repo: DS.attr('string'),
    title: DS.attr('string'),
    uri: DS.attr('string'),
    users: DS.hasMany('App.User')
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

  App.CourseController = Ember.ObjectController.extend();
  App.CourseView = Ember.View.extend({
    templateName: 'course'
  });

  App.UsersController = Ember.ArrayController.extend({
  });
  App.UsersView = Ember.View.extend({
    templateName: 'users'
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
      course: Ember.Route.extend({
        route: '/course/:course_id',

        connectOutlets: function(router, context) {
          var course = App.store.find(App.Course, 1);
          router.get('applicationController').connectOutlet('course', course);

          var courseController = router.get('courseController');
          // TODO: filter for just this course's users.
          courseController.connectOutlet('users', App.store.filter(App.User, function (data) { return true; }));
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
