<div class="course-user-info">
  <h2>Users</h2>
  <button {{action addUser target="controller"}}>Add user</button>
  {{#each user in controller}}
    {{#with user}}
      {{view "App.UserView"}}
    {{/with}}
  {{/each}}
</div>
