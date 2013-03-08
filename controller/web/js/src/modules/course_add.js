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
   * Model.
   */
  app.CoursesAddModel = Ember.Object.extend({

    // Load the form build information from the backend. This contains
    // information about the bundles / plugins, as well as help text and
    // customized form fields.
    formBuildInfo: false,
    resetFormBuildInfo: function() {
      var self = this;
      self.set('formBuildInfo', false);
      self.set('selectedBundle', null);
      self.set('selectedPlugins', []),
      $.ajax(
        '/rest/course_build',
        {
          success: function(data, textStatus, jqXHR) {
            if (jqXHR.status === 200) {
              self.set('formBuildInfo', data);
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

    title: null,
    description: null,
    courseName: null,
    resources: [],
    selectedBundle: null,
    selectedPlugins: [],
    host: 'localhost',
    user: null,
    pass: null,
    // Environment type. We do want to ultimately support multiple environments,
    // but right now, Ubuntu is the only option. Hide this from the user.
    envType: 'ubuntu',

    bundlesList: function() {
      var self = this;
      return this.get('formBuildInfo').bundles.map(function(bundle) {
        bundle.selected = self.get('selectedBundle') !== null && (self.get('selectedBundle') === bundle.key);
        return Ember.Object.create(bundle);
      });
    }.property('selectedBundle'),

    pluginsList: function() {
      var self = this;
      return this.get('formBuildInfo').plugins.map(function(plugin) {
        plugin.selected = self.get('selectedPlugins') !== null && (self.get('selectedPlugins').someProperty('key', plugin.key));
        return Ember.Object.create(plugin);
      });
    }.property('selectedPlugins'),

    titleErrors: [],
    courseNameErrors: [],

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
   * Controller.
   */
  app.CoursesAddController = Ember.ObjectController.extend({
    /**
     * Confirms the form is valid and if so submits, creating a new course.
     */
    saveCourse: function(view) {
      // Prevent saving the course if the form is invalid.
      this.validateTitle();
      this.validateShortName();
      if (this.get('form_is_invalid')) {
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
      if (this.get('selectedBundle') === bundle.get('key')) {
        this.set('selectedBundle', null);
        this.set('selectedPlugins', []);
      }
      else {
        this.set('selectedBundle', bundle.get('key'));
        this.set('selectedPlugins', bundle.get('plugins'));
      }
    },

    togglePlugin: function(plugin) {
      var selected = this.get('selectedPlugins').findProperty('key', plugin.get('key'));
      if (selected) {
        // The clicked plugin is selected, so unselect.
        this.set('selectedPlugins', this.get('selectedPlugins').filter(function(item, index, enumerable) {
          if (item.key !== plugin.get('key')) {
            return true;
          }
        }));
      }
      else {
        var newSelection = this.get('selectedPlugins').toArray();
        newSelection.push({ key: plugin.get('key')});
        this.set('selectedPlugins', newSelection);
      }
    }
  });

  /**
   * View.
   */
  app.CoursesAddView = Ember.View.extend({
    templateName: 'course-form',

    courseFormNext: function() {
      var $activeSection = $('.course-section.active');
      var $nextSection = $activeSection.next();
      var $activeNav = $('.course-form-nav-item.active');
      var $nextNav = $activeNav.next();
      $activeSection.removeClass('active');
      $nextSection.addClass('active');
      $activeNav.removeClass('active');
      $nextNav.addClass('active');
    },

    courseFormPrevious: function() {
      var $activeSection = $('.course-section.active');
      var $nextSection = $activeSection.prev();
      var $activeNav = $('.course-form-nav-item.active');
      var $nextNav = $activeNav.prev();
      $activeSection.removeClass('active');
      $nextSection.addClass('active');
      $activeNav.removeClass('active');
      $nextNav.addClass('active');
    }
  });
});
