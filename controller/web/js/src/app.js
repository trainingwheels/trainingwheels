(function(win, $) {
  'use strict';

  win.App = Ember.Application.create();
  var App = win.App;

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

  App.CourseStore = Ember.Object.extend();
  App.CourseStore.reopenClass({
    allCourses: [],
    all: function() {
      $.ajax({
        url: '/rest/course',
        dataType: 'json',
        context: this,
        success: function(data) {
          // TODO: Don't delete and re-load here.
          this.allCourses.clear();
          data.forEach(function(course) {
            this.allCourses.addObject(App.CourseStore.create(course))
          }, this);
        }
      });
      return this.allCourses;
    },

    find: function(course_id) {
      var course = App.CourseStore.create({
        courseid: course_id
      });

      $.ajax({
        url: '/rest/course/' + course_id,
        dataType: 'json',
        context: course,
        success: function(data) {
          this.setProperties(data);
        }
      })

      return course;
    }
  });

  App.Router = Ember.Router.extend({
    enableLogging: true,

    goHome: Ember.Route.transitionTo('courses'),

    root: Ember.Route.extend({
      index: Ember.Route.extend({
        route: '/',
        redirectsTo: 'courses'
      }),
      courses: Ember.Route.extend({
        route: '/courses',

        showCourse: Ember.Route.transitionTo('course'),

        connectOutlets: function(router) {
          router.get('applicationController').connectOutlet('courses', App.CourseStore.all());
        }
      }),
      course: Ember.Route.extend({
        route: '/course/:course_id',

        connectOutlets: function(router, course) {
          router.get('applicationController').connectOutlet('course', App.CourseStore.find(1));
        },

        serialize: function(router, course) {
          return {
            course_id: course.get('courseid')
          }
        },

        deserialize: function(router, urlParams){
          return App.CourseStore.find(urlParams.course_id);
        }

      })
    })
  })

  App.initialize();

})(window, jQuery);
