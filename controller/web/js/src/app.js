(function(win, $) {
  'use strict';

  // Shorthand for logging easily using cl('message');
  var cl = console.log.bind(console);

  win.App = Ember.Application.create();
  var App = win.App;

  App.sortOptions = ['name', 'id'];

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
        instructor: 'App.User',
        courses: 'App.Course',
        course_summaries: 'App.CourseSummary'
      }
    })
  });

  App.User = DS.Model.extend({
    user_name: DS.attr('string'),
    password: DS.attr('string'),
    logged_in: DS.attr('boolean'),
    course_id: DS.attr('number')
  });

  App.Course = DS.Model.extend({
    course_name: DS.attr('string'),
    course_type: DS.attr('string'),
    description: DS.attr('string'),
    env_type: DS.attr('string'),
    repo: DS.attr('string'),
    title: DS.attr('string'),
    uri: DS.attr('string'),
    users: DS.hasMany('App.User'),
    // TODO: Replace with hasOne when PR https://github.com/emberjs/data/pull/475 gets in.
    instructor: DS.hasMany('App.User')
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

  App.CourseController = Ember.ObjectController.extend({
    refreshCourse: function() {
      alertify.success('Refreshing the course');
    }
  });
  App.CourseView = Ember.View.extend({
    templateName: 'course'
  });

  App.UsersController = Ember.ArrayController.extend({
    addUser: function() {
      App.store.createRecord(App.User, {user_name: "newuser", course_id: 1});
      alertify.success('Adding a user');
    }
  });
  App.UsersView = Ember.View.extend({
    templateName: 'users',
  });

  App.InstructorController = Ember.ArrayController.extend();
  App.InstructorView = Ember.View.extend({
    templateName: 'instructor',
  });

  App.UserController = Ember.ObjectController.extend({
    user_logged_in_class: 'user-logged-in'
  });
  App.UserView = Ember.View.extend({
    templateName: 'user',
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
          // will auto-fill the users, so we can just use .filter() below
          // which doesn't trigger a request.
          var course = App.store.find(App.Course, course_id);
          router.get('applicationController').connectOutlet('course', course);

          // Pull the non-instructor users and connect them to the outlet. Remember that
          // this data store is not materialized right here, the requests are async and
          // once the data enters the store, the front end is updated. Try this in the
          // console if you want to look at the data:
          // App.store.filter(App.User, function(data) { return true; } ).objectAt(0).toData()
          var courseController = router.get('courseController');
          var users = App.store.filter(App.User, function (data) {
            if (data.get('course_id') == course_id && data.get('user_name') != 'instructor') {
              return true;
            }
          });
          courseController.connectOutlet({
            outletName: 'usersOutlet',
            name: 'users',
            context: users
          });

          var instructor = App.store.filter(App.User, function (data) {
            if (data.get('user_name') == 'instructor' && data.get('course_id') == course_id) {
              return true;
            }
          });
          courseController.connectOutlet({
            outletName: 'instructorOutlet',
            name: 'instructor',
            context: instructor
          });
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
