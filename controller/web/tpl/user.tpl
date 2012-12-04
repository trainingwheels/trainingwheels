<div class="user-full">
  <div class="user-info">
    <div class="user-expanded-icon"></div>
    <h3>{{user.user_name}}</h3>
    <div {{bindAttr class="view.css_class_login_status logged_in"}}></div>
    <div {{bindAttr class="view.css_class_resources_status resources_status"}}></div>
  </div>
  <div class="user-tools">
    <a href="#" {{action copyPassword user.password target="controller"}}>copy password</a>
    <button {{action syncUser target="controller"}}>Sync user</button>
    <button {{action deleteUser target="controller"}}>Delete user</button>
  </div>
  <div class="user-resources">
    {{#each resource in controller.resources}}
      {{#with resource}}
        {{view "App.ResourceView"}}
      {{/with}}
    {{/each}}
  </div>
</div>
