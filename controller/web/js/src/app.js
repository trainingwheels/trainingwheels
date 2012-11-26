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

  App.OneCourseController = Ember.ObjectController.extend();
  App.OneCourseView = Ember.View.extend({
    templateName: 'course'
  });

  App.Course = Ember.Object.extend();
  App.Course.reopenClass({
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
            this.allCourses.addObject(App.Course.create(course))
          }, this);
        }
      });
      return this.allCourses;
    },

    findOne: function(course_id) {
      var course = App.Course.create({
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

      console.log(course);

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

        showCourse: Ember.Route.transitionTo('aCourse'),

        connectOutlets: function(router) {
          router.get('applicationController').connectOutlet('courses', App.Course.all());
        }
      }),
      aCourse: Ember.Route.extend({
        route: '/course/:course_id',

        connectOutlets: function(router, context) {
          router.get('applicationController').connectOutlet('oneCourse', App.Course.findOne(1));
        },

        serialize: function(router, context) {
          return {
            course_id: context.get('courseid')
          }
        },

        deserialize: function(router, urlParams){
          return App.Course.findOne(urlParams.course_id);
        }

      })
    })
  })

  App.initialize();

})(window, jQuery);
