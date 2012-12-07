<div class="user-full">
  <div class="user-info">
    <a class="ss-icon" href="#" {{action showUser user}}>directdown</a>
    <h3>
      <a class="user-name" href="#" {{action showUser user}}>{{user.user_name}}</a>
    </h3>
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
