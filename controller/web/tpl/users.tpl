<div class="course-user-info">
  <h2>Users</h2>
  <button {{action collapseAll target="controller"}}>Collapse all</button>
  <button {{action selectAll target="controller"}}>Select all</button>
  Sort by: {{view Ember.Select contentBinding="App.sortOptions"}}
  <button {{action syncAll target="controller"}}>Sync all</button>
  <button {{action deleteSelected target="controller"}}>Delete</button>
  {{#each user in controller}}
    {{#with user}}
      {{view "App.UserView"}}
    {{/with}}
  {{/each}}
  {{view Ember.TextField valueBinding="newUserName"}} <button {{action addUser target="controller"}}>Add user</button>
</div>
