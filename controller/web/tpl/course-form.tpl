<div id="course-form">
  {{#if formBuildInfo}}
    <ul id="course-form-nav" class="clearfix">
      <li id="course-form-nav-info" class="course-form-nav-item active" data-nav-step="info">Course Info</li>
      <li id="course-form-nav-add-plugins" class="course-form-nav-item" data-nav-step="add-plugins">Add Plug-ins</li>
      <li id="course-form-nav-config-plugins" class="course-form-nav-item" data-nav-step="config-plugins">Configure Plug-ins</li>
      <li id="course-form-nav-add-resources" class="course-form-nav-item" data-nav-step="add-resources">Add Resources</li>
      <li id="course-form-nav-connection" class="course-form-nav-item last" data-nav-step="connection">Connection</li>
    </ul>
    <form class="clearfix">
      <div id="course-form-info" class="course-section active">
        <div id="course-form-info-title" class="course-form-title">Course Info</div>

        <div class="course-fields">
          <div {{bindAttr class="css_class_title"}}>
            Title
            {{view Ember.TextField placeholder="Course title" size="30" valueBinding="title"}}
            <ul id="title-errors" class="errors">
              {{#each error in titleErrors}}
              <li class="error">{{error}}</li>
              {{/each}}
            </ul>
          </div>
          <div class="field"><span>Description</span> {{view Ember.TextArea placeholder="Describe course" rows="5" cols="30" valueBinding="description"}}</div>
          <div {{bindAttr class="css_class_short_name"}}>
            Short name
            {{view Ember.TextField required="required" placeholder="Contains only letters and underscores" size="30" valueBinding="courseName"}}
            <ul id="short-name-errors" class="errors">
              {{#each error in courseNameErrors}}
              <li class="error">{{error}}</li>
              {{/each}}
            </ul>
          </div>
        </div>
        <div class="course-tools clearfix">
          <button class="next" type="submit" value="Next" {{action "courseFormNext" target="view"}}>Add Plug-ins &raquo;</button>
        </div>
      </div>

      <div id="course-form-add-plugins" class="course-section">
        <div id="course-form-add-plugins-title" class="course-form-title">Add Plug-ins</div>
        <div id="plugin-bundles">
          <h3>Bundles</h3>
          <ul id="bundles-list">
            {{#each bundle in bundles}}
             <li {{bindAttr class="bundle.selected:selected :bundle"}} {{action "toggleBundle" bundle}}>{{bundle.title}}</li>
            {{/each}}
          </ul>
        </div>
        <div id="plugin-plugins">
          <h3>Plugins</h3>
          <ul id="plugins-list">
            {{#each plugin in plugins}}
             <li {{bindAttr class="plugin.selected:selected :plugin"}} {{action "togglePlugin" plugin}}>{{plugin.key}}</li>
            {{/each}}
          </ul>
        </div>
        <div class="course-tools clearfix">
          <button class="previous" type="submit" value="Next" {{action "courseFormPrevious" target="view"}}>&laquo; Course Info</button>
          <button class="next" type="submit" value="Next" {{action "courseFormNext" target="view"}}>Configure Plug-ins &raquo;</button>
        </div>
      </div>

      <div id="course-form-config-plugins" class="course-section">
        <div id="course-form-config-plugins-title" class="course-form-title">Configure Plug-ins</div>
        <div id="configure-plugins">
          {{#each plugin in selectedPlugins}}
            {{#with plugin}}
              {{view App.PluginConfigureView}}
            {{/with}}
          {{/each}}
        </div>
        <div class="course-tools clearfix">
          <button class="previous" type="submit" value="Next" {{action "courseFormPrevious" target="view"}}>&laquo; Add Plug-ins</button>
          <button class="next" type="submit" value="Next" {{action "courseFormNext" target="view"}}>Add Resources &raquo;</button>
        </div>
      </div>

      <div id="course-form-add-resources" class="course-section">
        <div id="course-form-add-resources-title" class="course-form-title">Add Resources</div>
        <div id="configure-resources">
          {{#each resource in resources}}
            {{#with resource}}
              {{view App.ResourceConfigureView}}
            {{/with}}
          {{/each}}
        </div>
        <div id="addmore-resources">
          {{view Ember.Select
            contentBinding="content.allResources"
            optionLabelPath="content.key"
            optionValuePath="content.key"
            prompt="Add resource:"
            selectionBinding="resourceToAdd"
          }}
          <button class="yellow-btn ss-icon ss-symbolicons-block"{{action "addResource"}}>adddatabase</button>
        </div>
        <div class="course-tools clearfix">
          <button class="previous" type="submit" value="Next" {{action "courseFormPrevious" target="view"}}>&laquo; Configure Plug-ins</button>
          <button class="next" type="submit" value="Next" {{action "courseFormNext" target="view"}}>Connection &raquo;</button>
        </div>
      </div>

      <div id="course-form-connection" class="course-section">
        <div id="course-form-connection-title" class="course-form-title">Connection</div>
        <div id="connection">
          <div class="field">
            Host name
            {{view Ember.TextField required="required" size="30" valueBinding="host"}}
          </div>
          <div class="field">
            User name
            {{view Ember.TextField required="required" size="30" valueBinding="user"}}
          </div>
          <div class="field">
            Port number
            {{view Ember.TextField required="required" size="30" valueBinding="port"}}
          </div>
        </div>
        <div class="course-tools">
          <button class="previous" type="submit" value="Next" {{action "courseFormPrevious" target="view"}}>&laquo; Add Resources</button>
          <button class="submit" type="submit" value="Save" {{bindAttr disabled="formInvalid"}} {{action "saveCourse"}}>Create course</button>
        </div>
      </div>

    </form>
    <div class="course-tools">
      <hr>
      <button class="cancel" type="submit" value="Cancel" {{action "cancelCourseAdd"}}>View all courses</button>
    </div>
  {{else}}
    <div class="tw-loading"></div>
  {{/if}}
</div>
