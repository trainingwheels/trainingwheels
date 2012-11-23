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
    find: function() {
      $.ajax({
        url: 'https://api.github.com/repos/emberjs/ember.js/contributors',
        dataType: 'jsonp',
        context: this,
        success: function(response) {
          response.data.forEach(function(course) {
            this.allCourses.addObject(App.Course.create(course))
          }, this)
        }
      });
      return this.allCourses;
    },

    findOne: function(course_id) {
      var course = App.Course.create({
        login: course_id
      });

      $.ajax({
        url: 'https://api.github.com/repos/emberjs/ember.js/contributors',
        dataType: 'jsonp',
        context: course,
        success: function(response) {
          this.setProperties(response.data.findProperty('login', course_id));
        }
      })

      return course;
    }
  });

  App.Router = Ember.Router.extend({
    enableLogging: true,
    root: Ember.Route.extend({
      index: Ember.Route.extend({
        route: '/',
        redirectsTo: 'courses'
      }),
      courses: Ember.Route.extend({
        route: '/courses',

        showCourse: Ember.Route.transitionTo('aCourse'),

        connectOutlets: function(router) {
          router.get('applicationController').connectOutlet('courses', App.Course.find());
        }
      }),
      aCourse: Ember.Route.extend({
        route: '/course/:course_id',

        goHome: Ember.Route.transitionTo('courses'),

        connectOutlets: function(router, context) {
          router.get('applicationController').connectOutlet('oneCourse', context);
        },

        serialize: function(router, context) {
          return {
            course_id: context.get('login')
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
