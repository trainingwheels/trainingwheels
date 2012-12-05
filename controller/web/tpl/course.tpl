<div id="course-view">
  {{#if isLoaded}}
    <div id="course-info">
      <h1><a href="#" {{action showCourse course}}>{{title}}</a></h1>
      <button class="refresh-button ss-refresh"{{action refreshCourse target="controller"}}></button>
      <div class="course-lock">
        <button class="ss-lock"></button>
        <div class="course-lock-text">Course in session</div>
      </div>
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
        <button class="ss-sync"{{action syncAll target="controller"}}></button>
        <button class="ss-trash"{{action deleteSelected target="controller"}}></button>
      </div>
      <div id="course-users-list">
        {{#each user in controller.usersAbove}}
          {{#with user}}
            {{view "App.UserSummaryView"}}
          {{/with}}
        {{/each}}

        {{#each user in controller.userSelected}}
          {{#with user}}
            {{view "App.UserView" controllerBinding="App.router.userController"}}
          {{/with}}
        {{/each}}

        {{#each user in controller.usersBelow}}
          {{#with user}}
            {{view "App.UserSummaryView"}}
          {{/with}}
        {{/each}}
      </div>
      <div id="course-users-bottom-tools">
        {{view Ember.TextField valueBinding="newUserName"}} <button {{action addUser target="controller"}}>Add user</button>
      </div>
    </div>
  {{else}}
    <div class="tw-loading"></div>
  {{/if}}
</div>
