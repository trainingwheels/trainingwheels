<div class="user user-complete">
  <div class="arrow-image arrow-down"></div>
  <h4><a {{action showUser user}}>{{user.user_name}}</a></h4>
  {{user.id}}
  {{user.password}}
  <div {{bindAttr class="view.css_class_login_status logged_in"}}></div>

  <div class="user-resource-status resources-created"></div>

  {{#each resource in controller.resources}}
    {{#with resource}}
      {{view "App.ResourceView"}}
    {{/with}}
  {{/each}}
</div>
