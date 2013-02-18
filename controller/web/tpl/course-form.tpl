<div id="course-form">
  <ul id="course-form-nav" class="clearfix">
    <li id="course-form-nav-info" class="course-form-nav-item active">Course Info</li>
    <li id="course-form-nav-add-plugins" class="course-form-nav-item">Add Plug-ins</li>
    <li id="course-form-nav-config-plugins" class="course-form-nav-item">Configure Plug-ins</li>
    <li id="course-form-nav-add-resources" class="course-form-nav-item">Add Resources</li>
    <li id="course-form-nav-connection" class="course-form-nav-item last">Connection</li>
  </ul>
  <form class="clearfix">
    <div id="course-form-info" class="course-section active">
      <div id="course-form-info-title" class="course-form-title">Course Info</div>

      <div class="course-fields">
        <div {{bindAttr class="css_class_title"}}>
          Title
          {{view Ember.TextField placeholder="Course title" size="30" viewName="titleTextField" valueBinding="title"}}
          <ul id="title-errors" class="errors">
            {{#each error in controller.titleErrors}}
            <li class="error">{{error}}</li>
            {{/each}}
          </ul>
        </div>
        <div class="field"><span>Description</span> {{view Ember.TextArea placeholder="Describe course" rows="5" cols="30" viewName="descriptionTextField" valueBinding="description"}}</div>
        <div {{bindAttr class="css_class_short_name"}}>
          Short name
          {{view Ember.TextField required="required" placeholder="Contains only letters and underscores" size="30" valueBinding="courseName"}}
          <ul id="short-name-errors" class="errors">
            {{#each error in controller.courseNameErrors}}
            <li class="error">{{error}}</li>
            {{/each}}
          </ul>
        </div>
        <div class="field">Type        {{view Ember.TextField placeholder="drupal" size="30" viewName="typeTextField" valueBinding="courseType"}}</div>
        <div class="field">Environment {{view Ember.TextField value="ubuntu" size="30" viewName="environmentTextField" valueBinding="envType"}}</div>
      </div>
      <div class="course-tools clearfix">
        <button class="next" type="submit" value="Next" {{action "courseFormNext" target="view"}}>Add Plug-ins &raquo;</button>
      </div>
    </div>

    <div id="course-form-add-plugins" class="course-section">
      <div id="course-form-add-plugins-title" class="course-form-title">Add Plug-ins</div>
      <div id="plugin-bundles">
        <h3>Bundles</h3>
      </div>
      <div id="plugin-plugins">
        <h3>A la carte</h3>
        <ul id="plugins-list">
        </ul>
      </div>
      <div class="course-tools clearfix">
        <button class="previous" type="submit" value="Next" {{action "courseFormPrevious" target="view"}}>&laquo; Course Info</button>
        <button class="next" type="submit" value="Next" {{action "courseFormNext" target="view"}}>Configure Plug-ins &raquo;</button>
      </div>
    </div>

    <div id="course-form-config-plugins" class="course-section">
      <div id="course-form-config-plugins-title" class="course-form-title">Configure Plug-ins</div>
      <div class="course-tools clearfix">
        <button class="previous" type="submit" value="Next" {{action "courseFormPrevious" target="view"}}>&laquo; Add Plug-ins</button>
        <button class="next" type="submit" value="Next" {{action "courseFormNext" target="view"}}>Add Resources &raquo;</button>
      </div>
    </div>

    <div id="course-form-resources" class="course-section">
      <div id="course-form-resources-title" class="course-form-title">Add Resources</div>
      <div class="course-tools clearfix">
        <button class="previous" type="submit" value="Next" {{action "courseFormPrevious" target="view"}}>&laquo; Configure Plug-ins</button>
        <button class="next" type="submit" value="Next" {{action "courseFormNext" target="view"}}>Connection &raquo;</button>
      </div>
    </div>

    <div id="course-form-connection" class="course-section">
      <div id="course-form-connection-title" class="course-form-title">Connection</div>
      <div class="course-tools">
        <button class="previous" type="submit" value="Next" {{action "courseFormPrevious" target="view"}}>&laquo; Add Resources</button>
        <button class="submit" type="submit" value="Save" {{bindAttr disabled="form_is_invalid"}} {{action "saveCourse" view}}>Create course</button>
      </div>
    </div>

  </form>
  <div class="course-tools">
    <hr>
    <button class="cancel" type="submit" value="Cancel" {{action "cancelCourseAdd"}}>View all courses</button>
  </div>
</div>
