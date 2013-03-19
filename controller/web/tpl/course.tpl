<div id="course-view">
  {{#if isLoaded}}
    <div id="course-info">
      <h1><a href="#" {{action "returnToCourse"}}>{{title}}</a></h1>
      <!-- Not yet implemented  -->
      <!--<button class="refresh-button ss-refresh"></button>
      <a class="course-lock">
        <div class="ss-lock"></div>
        <div class="course-lock-text">Course in session</div>
      </a> -->
    </div>

    <div id="course-instructor">
      {{#each user in controller.instructorSummary}}
        {{#with user}}
          {{view "App.UserSummaryView"}}
        {{/with}}
      {{/each}}

      <div class="user-selected">
        {{#each user in controller.instructorSelected}}
          {{#with user}}
            {{view "App.CourseUserView" controllerBinding="controller.controllers.courseUser"}}
          {{/with}}
        {{/each}}
      </div>
    </div>

    <div id="course-users">
      <div id="course-users-top-tools">
        <!-- Not yet implemented  -->
        <!-- <button class="ss-rows" action collapseAll target="controller" ></button>
        <button class="ss-users" action selectAll target="controller" ></button>
        Sort by: view Ember.Select contentBinding="view.sortOptions" -->
        <div class="tools-right">
          <button {{action "syncAll" target="view"}}>
            <div {{bindAttr class="view.css_class_syncing"}}>
              <span class="ss-sync"></span>
            </div>
          </button>
          <!-- <button class="ss-trash" action deleteSelected target="controller" ></button> -->
        </div>
      </div>
      <div id="course-users-list">

        {{#each user in controller.userSummariesAbove}}
          {{#with user}}
            {{view "App.UserSummaryView"}}
          {{/with}}
        {{/each}}

        <div class="user-selected">
          {{#each user in controller.userSelected}}
            {{#with user}}
              {{view "App.CourseUserView" controllerBinding="controller.controllers.courseUser"}}
            {{/with}}
          {{/each}}
        </div>

        {{#each user in controller.userSummariesBelow}}
          {{#with user}}
            {{view "App.UserSummaryView"}}
          {{/with}}
        {{/each}}
      </div>
      <div id="course-users-bottom-tools">
        {{view Ember.TextField placeholder="New user name" valueBinding="newUserName"}}
        <button class="yellow-btn ss-icon ss-symbolicons-block"{{action "addUser"}}>adduser</button>
      </div>
    </div>
  {{else}}
    <div class="tw-loading"></div>
  {{/if}}
</div>

<div id="push"></div>
