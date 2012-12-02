<div class="course-view">
  {{#if title}}
    <div class="course-info">
      <h2>{{title}}</h2>
      <div>
        {{{description}}}
      </div>
      <h2>Machine name</h2>
      <div>
        {{course_name}}
      </div>
    </div>
    <button {{action refreshCourse target="controller"}}>Refresh</button>
    <div class="course-instructor">
      <h2>Instructor</h2>
      {{#each user in controller.instructor}}
        {{#with user}}
          {{view "App.UserSummaryView"}}
        {{/with}}
      {{/each}}
    </div>

    <div class="course-user-info">
      <h2>Users</h2>
      <button {{action collapseAll target="controller"}}>Collapse all</button>
      <button {{action selectAll target="controller"}}>Select all</button>
      Sort by: {{view Ember.Select contentBinding="view.sortOptions"}}
      <button {{action syncAll target="controller"}}>Sync all</button>
      <button {{action deleteSelected target="controller"}}>Delete</button>
      {{#each user in controller.users}}
        {{#with user}}
          {{view "App.UserSummaryView"}}
        {{/with}}
      {{/each}}
      {{view Ember.TextField valueBinding="newUserName"}} <button {{action addUser target="controller"}}>Add user</button>
    </div>
  {{/if}}
</div>
