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
    // Helper property to relay status of save back to the controller.
    status: null,

    // Load the form build information from the backend. This contains
    // information about the bundles / plugins, as well as help text and
    // customized form fields.
    formBuildInfo: false,
    resetFormBuildInfo: function() {
      var self = this;
      self.set('formBuildInfo', false);
      var request = $.ajax('/rest/course_build');

      request.done(function(data, textStatus, jqXHR) {
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
      });

      request.fail(function(jqXHR, textStatus, errorThrown) {
        throw new Error('Unable to fetch course build information.');
      });
    },

    // Commit the new course to the backend.
    commitCourse: function() {
      var self = this;
      var newCourse = {
        title: this.get('title'),
        description: this.get('description'),
        course_name: this.get('courseName'),
        env_type: this.get('envType'),
        host: this.get('host'),
        user: this.get('user'),
        port: this.get('port'),
        plugins: {},
        resources: {}
      };

      this.get('selectedPlugins').forEach(function(item) {
        var pluginConfig = {};
        item.get('vars').forEach(function(item) {
          if (item.get('input') !== item.get('val')) {
            pluginConfig[item.get('key')] = item.get('input');
          }
        });
        newCourse.plugins[item.get('key')] = pluginConfig;
      });

      this.get('resources').forEach(function(item) {
        var resourceConfig = {
          title: item.get('title'),
          type: item.get('type'),
          plugin: item.get('plugin')
        };
        item.get('vars').forEach(function(item) {
          if (item.get('input') !== item.get('val')) {
            resourceConfig[item.get('key')] = item.get('input');
          }
        });
        newCourse.resources[item.get('key')] = resourceConfig;
      });

      var request = $.ajax({
        type: 'POST',
        url: '/rest/courses',
        data: JSON.stringify({
          course: newCourse
        }),
        contentType : 'application/json',
        dataType: 'json'
      });

      request.done(function(data, textStatus, jqXHR) {
        if (jqXHR.status === 201) {
          self.set('status', 'saveSuccess');
        }
        else {
          self.set('status', 'saveFailed');
        }
      });

      request.fail(function(data, textStatus, jqXHR) {
        self.set('status', 'saveFailed');
      });
    },

    // Title, description and course name are all basic data.
    title: '',
    description: '',
    courseName: '',
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
    user: '',
    port: 22,
    // Environment type. We do want to ultimately support multiple environments,
    // but right now, Ubuntu is the only option. Hide this from the user.
    envType: 'ubuntu',

    // Helpers.
    selectedPlugins: function() {
      return this.get('plugins').filterProperty('selected', true);
    }.property('plugins.@each.selected'),

    pushResource: function(resObj, options) {
      var newResources = this.get('resources');
      newResources.pushObject(resObj);
      this.set('resources', newResources);
      resObj.set('title', options.title);
      resObj.set('key', options.key);

      // Turn the vars into Ember Objects so we can bind them.
      resObj.set('vars', resObj.get('vars').map(function(var_item) {
        var_item.input = var_item.val;
        return Ember.Object.create(var_item);
      }));
    },

    // Validation methods.
    titleErrors: [],
    titleValid: function() {
      this.set('titleErrors', []);
      if (this.get('title').length === 0) {
        this.get('titleErrors').push('The course title is required.');
        return false;
      }
      return true;
    }.property('title'),

    courseNameErrors: [],
    courseNameValid: function() {
      this.set('courseNameErrors', []);
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

    pluginsValid: function() {
      return this.get('selectedPlugins').length > 0;
    }.property('selectedPlugins.@each'),

    resourcesValid: function() {
      return this.get('resources').length > 0;
    }.property('resources.@each'),

    // Helper property to disable submit functionality if the form is invalid.
    formInvalid: function() {
      if (!this.get('titleValid') ||
          !this.get('courseNameValid') ||
          !this.get('resourcesValid') ||
          !this.get('pluginsValid')) {
        return true;
      }
      return false;
    }.property('titleValid', 'courseNameValid', 'resourcesValid', 'pluginsValid')
  });

  /**
   * Controllers.
   */
  app.CoursesAddController = Ember.ObjectController.extend({
    // Helper property for adding a resource.
    resourceToAdd: null,

    saveCourse: function() {
      this.content.commitCourse();
    },

    afterSave: function() {
      switch (this.get('status')) {
        case 'saveSuccess':
          alertify.success('Course "' + this.get('title') + '" created.');
          this.transitionToRoute('courses');
          break;
        case 'saveFailed':
          alertify.error('There was an error creating course "' + this.get('title') + '".');
          break;
      }
    }.observes('status'),

    cancelCourseAdd: function() {
      this.transitionToRoute('courses');
    },

    toggleBundle: function(bundle) {
      // Unselect all other bundles.
      this.get('bundles').forEach(function(item) {
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
        this.get('plugins').forEach(function(item) {
          item.set('selected', bundlePlugins.someProperty('key', item.get('key')));
        });

        // All resources that are required by the bundle are added to the current
        // resources selection and default values set.
        var bundleResources = bundle.get('resources');
        var self = this;
        bundleResources.forEach(function(item) {
          var newRes = Ember.Object.create(self.get('allResources').findProperty('type', item.type));
          self.content.pushResource(newRes, {title: item.title, key: item.key});
        });
      }
    },

    togglePlugin: function(plugin) {
      plugin.set('selected', !plugin.get('selected'));
    },

    addSelectedResource: function() {
      if (this.get('resourceToAdd') === null) {
        alertify.error('Please select a resource type to add.');
      }
      var newRes = Ember.Object.create(this.get('allResources').findProperty('type', this.get('resourceToAdd').get('type')));
      this.content.pushResource(newRes, {title: 'New resource', key: 'new_resource'});
    },

    // This is a tricky bit of interaction. When the user first opens the form, we
    // don't want to show them glaring errors because they haven't input anything yet.
    // So we hide the error condition until the user has at least input a character
    // into the textfield, at which point we reveal any problems. See the 'input' event
    // handler on CoursesAddView. This way the validation computed properties give us a
    // reliable true/false, so that we can use this to prevent the user from submitting
    // until they complete all required fields.
    hideCourseNameErrors: true,
    hideTitleErrors: true,
    css_class_title: function() {
      return 'field' + (this.get('titleValid') || this.get('hideTitleErrors') ? '' : ' invalid clearfix');
    }.property('titleValid', 'hideTitleErrors'),
    css_class_course_name: function() {
      return 'field' + (this.get('courseNameValid') || this.get('hideCourseNameErrors') ? '' : ' invalid clearfix');
    }.property('courseNameValid', 'hideCourseNameErrors'),

    resetValidation: function() {
      this.set('hideCourseNameErrors', true);
      this.set('hideTitleErrors', true);
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

    // The handlebars action helper does not accept static text arguments, nor
    // does it send the event object. Using 'click' is the best practice for this
    // kind of interaction.
    click: function(event) {
      $el = $(event.target);
      // Click events on the top navigation steps are handled here.
      if ($el.hasClass('course-form-nav-item')) {
        this.courseFormStepSelect($el.attr('data-nav-step'));
      }
    },

    // Once a user has entered text into an input field, we begin showing any
    // validation errors on the field.
    input: function(event) {
      $elid = $(event.target).attr('id');
      switch ($elid) {
        case 'course-title-textfield':
          this.set('controller.hideTitleErrors', false);
          break;
        case 'course-name-textfield':
          this.set('controller.hideCourseNameErrors', false);
          break;
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
