<div class="course-user-info">
  {{#with instructor}}
  {{user_name}}
  {{/with}}
  <h2>Users</h2>
  {{#each user in controller}}
    {{#with user}}
      <div class="user">
        <a href="#" {{action "showUser" user target="App.usersController"}}><h2>{{user_name}}</h2></a>
        <div>Password: {{password}}</div>
        <div>Logged in: {{#if logged_in}}yes{{else}}no{{/if}}</div>
        <div class="resources">
          <h3>Resources</h3>

          <!-- {{#each res in resources}}
            {{#with res}}
              <div class="resource">
                <h4>{{title}}</h4>
                <div>Type: {{type}}</div>
                <div>Key: {{key}}</div>
                {{#if exists}}
                <div>Exists: yes</div>
                  <div class="resource-attribs">
                    {{#key_value attribs}}
                      <div>{{key}}: {{value}}</div>
                    {{/key_value}}
                  </div>
                {{else}}
                  <div>Exists: no</div>
                {{/if}}
              </div>
            {{/with}}
          {{/each}} -->
        </div>
      </div>
    {{/with}}
  {{/each}}
</div>
