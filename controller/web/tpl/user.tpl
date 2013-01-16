<div class="user-full">
  <div class="user-info">
    <a class="ss-icon" href="#" {{action "collapseUser"}}>navigatedown</a>
    <h3>
      <a class="user-name" href="#" {{action "collapseUser"}}>{{user.user_name}}</a>
    </h3>
    <div class="indicators-summary-users">
      <div {{bindAttr class="user.css_class_login_status"}}></div>
      <div {{bindAttr class="user.css_class_resource_overview_status"}}></div>
    </div>
  </div>
  <div class="user-tools">
    <a href="#" {{action "copyPassword" user.password}}>copy password</a>
    <button {{action "syncUser" user.user_name target="view"}}><span class="ss-sync"></span></button>
    <!-- Not yet implemented  -->
    <!-- <button class="ss-trash"{{action deleteUser target="controller"}}></button> -->
  </div>
  <div class="user-resources">
    {{#each resource in controller.resources}}
      {{#with resource}}
        {{view "App.ResourceView"}}
      {{/with}}
    {{/each}}
  </div>
</div>
