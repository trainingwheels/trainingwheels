<div class="user-full">
  <div class="user-info">
    <a class="ss-icon" href="#" {{action showUser user}}>navigatedown</a>
    <h3>
      <a class="user-name" href="#" {{action showUser user}}>{{user.user_name}}</a>
    </h3>
    <div class="indicators-summary-users">
      <div class="ss-user"{{bindAttr class="view.css_class_login_status logged_in"}}></div>
      <div class="ss-folder"{{bindAttr class="view.css_class_resources_status resources_status"}}></div>
    </div>
  </div>
  <div class="user-tools">
    <a href="#" {{action copyPassword user.password target="controller"}}>copy password</a>
    <button class="ss-sync"{{action syncUser target="controller"}}></button>
    <button class="ss-trash"{{action deleteUser target="controller"}}></button>
  </div>
  <div class="user-resources">
    {{#each resource in controller.resources}}
      {{#with resource}}
        {{view "App.ResourceView"}}
      {{/with}}
    {{/each}}
  </div>
</div>
