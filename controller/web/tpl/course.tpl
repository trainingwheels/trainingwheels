<div id="course-view">
  {{#if isLoaded}}
    <div id="course-info">
      <h1><a href="#" {{action showCourse course}}>{{title}}</a></h1>
      <button class="refresh-button ss-refresh"{{action refreshCourse target="controller"}}></button>
      <a class="course-lock">
        <div class="ss-lock"></div>
        <div class="course-lock-text">Course in session</div>
      </a>
    </div>

    <div id="course-instructor">
      {{#each user in controller.instructor}}
        {{#with user}}
          {{view "App.UserSummaryView"}}
        {{/with}}
      {{/each}}
    </div>

    <div id="course-users">
      <div id="course-users-top-tools">
          <button class="ss-rows"{{action collapseAll target="controller"}}></button>
          <button class="ss-users"{{action selectAll target="controller"}}></button>
          Sort by:{{view Ember.Select contentBinding="view.sortOptions"}}
        <div class="tools-right">
          <button class="ss-sync"{{action syncAll target="controller"}}></button>
          <button class="ss-trash"{{action deleteSelected target="controller"}}></button>
        </div>
      </div>
      <div id="course-users-list">

        {{#each user in controller.usersAbove}}
          {{#with user}}
            {{view "App.UserSummaryView"}}
          {{/with}}
        {{/each}}

        <div class="user-selected">
        {{#each user in controller.userSelected}}
          {{#with user}}
            {{view "App.UserView" controllerBinding="App.router.userController"}}
          {{/with}}
        {{/each}}
        </div>

        {{#each user in controller.usersBelow}}
          {{#with user}}
            {{view "App.UserSummaryView"}}
          {{/with}}
        {{/each}}
      </div>
      <div id="course-users-bottom-tools">
        {{view Ember.TextField valueBinding="newUserName"}} <button class="yellow-btn ss-icon ss-symbolicons-block"{{action addUser target="controller"}}>adduser</button>
      </div>
    </div>
  {{else}}
    <div class="tw-loading"></div>
  {{/if}}
</div>
