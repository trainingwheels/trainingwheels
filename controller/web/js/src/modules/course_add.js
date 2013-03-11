/**
 * @fileoverview The 'Add Course' form.
 */
define([
  'ember',
  'ember-data',
  'jquery',
  'alertify',
  'app'
], function(Ember, DS, $, alertify, app) {

  /**
   * Models.
   */
  app.CoursesAddModel = Ember.Object.extend({

    // Load the form build information from the backend. This contains
    // information about the bundles / plugins, as well as help text and
    // customized form fields.
    formBuildInfo: false,
    resetFormBuildInfo: function() {
      var self = this;
      self.set('formBuildInfo', false);
      $.ajax(
        '/rest/course_build',
        {
          success: function(data, textStatus, jqXHR) {
            if (jqXHR.status === 200) {
              self.set('formBuildInfo', data);

              self.set('plugins', data.plugins.map(function(plugin) {
                plugin.selected = false;
                plugin.vars = plugin.vars.map(function(variable) {
                  variable.input = variable.val;
                  return Ember.Object.create(variable);
                });
                return Ember.Object.create(plugin);
              }));

              self.set('bundles', data.bundles.map(function(bundle) {
                bundle.selected = false;
                return Ember.Object.create(bundle);
              }));

              self.set('allResources', data.resources.map(function(resource) {
                return Ember.Object.create(resource);
              }));
            }
            else {
              throw new Error('Unable to fetch course build information.');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            throw new Error('Unable to fetch course build information.');
          }
        }
      );
    },

    // Title, description and course name are all basic data.
    title: null,
    description: null,
    courseName: null,
    // All Resources that are available to be selected.
    allResources: [],
    // The array of selected resources, there may be more than one of a particular type.
    resources: [],
    // Only one bundle may be chosen at a time.
    bundles: [],
    // Plugins can either be selected or not, one cannot add multiple instances of a plugin.
    plugins: [],
    // More basic data.
    host: 'localhost',
    user: null,
    port: null,
    // Environment type. We do want to ultimately support multiple environments,
    // but right now, Ubuntu is the only option. Hide this from the user.
    envType: 'ubuntu',

    // Helpers.
    selectedPlugins: function() {
      return this.get('plugins').filterProperty('selected', true);
    }.property('plugins.@each.selected'),

    // Validation methods.
    titleErrors: [],
    titleValid: function() {
      this.set('titleErrors', []);
      if (this.get('title') === null) {
        return true;
      }
      if (this.get('title').length === 0) {
        this.get('titleErrors').push('The course title is required.');
        return false;
      }
      return true;
    }.property('title'),

    courseNameErrors: [],
    courseNameValid: function() {
      this.set('courseNameErrors', []);
      if (this.get('courseName') === null) {
        return true;
      }
      if (this.get('courseName').length === 0) {
        this.get('courseNameErrors').push('The course short name is required.');
      }
      // Course short names are limited to 11 characters because
      // of MySQL's 16 character user name limit. When we create
      // the mysql user it will be course_name + '_UNIX_UID' where
      // UNIX_UID is a four digit number (i.e. 1001).
      if (this.get('courseName').length > 11) {
        this.get('courseNameErrors').push('Course short names cannot be more than 11 characters long.');
      }
      // Ensure that the course name contains only letters and underscores.
      if (!this.get('courseName').match(/^\w+$/)) {
        this.get('courseNameErrors').push('Course short names can only contain letters, numbers, and underscores.');
      }
      if (this.get('courseNameErrors').length > 0) {
        return false;
      }
      return true;
    }.property('courseName'),

    // Helper property to disable submit functionality if the form is invalid.
    formInvalid: function() {
      if (!this.get('titleValid') || !this.get('courseNameValid')) {
        return true;
      }
      return false;
    }.property(
      'titleValid',
      'courseNameValid'
    ),

    css_class_title: function() {
      return 'field' + (this.get('titleValid') ? '' : ' invalid clearfix');
    }.property('titleValid'),
    css_class_short_name: function() {
      return 'field' + (this.get('courseNameValid') ? '' : ' invalid clearfix');
    }.property('courseNameValid')
  });

  /**
   * Controllers.
   */
  app.CoursesAddController = Ember.ObjectController.extend({
    // Helper property for adding a resource.
    resourceToAdd: null,

    saveCourse: function(view) {
      if (this.get('formInvalid')) {
        alertify.error('The course form contains invalid data. Double check your settings.');
        return;
      }
      var newCourse = {
        title: this.get('title'),
        description: this.get('description'),
        course_name: this.get('courseName'),
        course_type: this.get('courseType'),
        env_type: this.get('envType'),
        repo: this.get('repo'),
        host: this.get('host'),
        user: this.get('user'),
        pass: this.get('pass')
      };
      var model = app.CourseSummary.createRecord(newCourse);
      model.store.commit();
      this.transitionToRoute('courses');
    },

    cancelCourseAdd: function() {
      this.transitionToRoute('courses');
    },

    toggleBundle: function(bundle) {
      // Unselect all other bundles.
      this.get('bundles').forEach(function(item, index, enumerable) {
        if (item.get('key') !== bundle.get('key')) {
          item.set('selected', false);
        }
      });
      // Unselect all resources.
      this.set('resources', []);
      // Toggle the clicked bundle's state.
      bundle.set('selected', !bundle.get('selected'));

      // If a bundle is selected, then select all the plugins and resources that must
      // be included too.
      if (bundle.get('selected') === true) {
        var bundlePlugins = bundle.get('plugins');
        this.get('plugins').forEach(function(item, index, enumerable) {
          item.set('selected', bundlePlugins.someProperty('key', item.get('key')));
        });

        // All resources that are required by the bundle are added to the current
        // resources selection and default values set.
        var bundleResources = bundle.get('resources');
        var self = this;
        bundleResources.forEach(function(item, index, enumerable) {
          var resObj = Ember.Object.create(self.get('allResources').findProperty('key', item.type));
          self.get('resources').pushObject(resObj);
          resObj.set('title', item.title);
        });
      }
    },

    togglePlugin: function(plugin) {
      plugin.set('selected', !plugin.get('selected'));
    },

    addResource: function() {
      if (this.get('resourceToAdd') === null) {
        alertify.error('Please select a resource to add.');
      }
      var newRes = this.get('allResources').findProperty('key', this.get('resourceToAdd').get('key'));
      var resObj = Ember.Object.create(newRes);
      resObj.set('title', 'New resource');
      this.get('resources').pushObject(resObj);
    }
  });

  /**
   * Views.
   */
  app.PluginConfigureView = Ember.View.extend({
    templateName: 'plugin-configure'
  });

  app.ResourceConfigureView = Ember.View.extend({
    templateName: 'resource-configure'
  });

  app.CoursesAddView = Ember.View.extend({
    templateName: 'course-form',

    click: function(event) {
      $el = $(event.target);
      // Click events on the top navigation steps are handled here.
      if ($el.hasClass('course-form-nav-item')) {
        this.courseFormStepSelect($el.attr('data-nav-step'));
      }
    },

    courseFormSelect: function($nextSection, $nextNav) {
      var $activeSection = $('.course-section.active');
      var $activeNav = $('.course-form-nav-item.active');
      $activeSection.removeClass('active');
      $nextSection.addClass('active');
      $activeNav.removeClass('active');
      $nextNav.addClass('active');
    },

    courseFormStepSelect: function(step) {
      this.courseFormSelect($('#course-form-' + step), $('#course-form-nav-' + step));
    },

    courseFormNext: function() {
      this.courseFormSelect($('.course-section.active').next(), $('.course-form-nav-item.active').next());
    },

    courseFormPrevious: function() {
      this.courseFormSelect($('.course-section.active').prev(), $('.course-form-nav-item.active').prev());
    }
  });
});
