<div class="user">
  <div class="arrow-image down"></div>
  <h4><a {{action showUser user}}>{{user_name}}</a></h4>
  {{id}}
  {{password}}
  {{view Ember.TextField valueBinding="user_name"}}
  <div {{bindAttr class="view.css_class_login_status logged_in"}}></div>

  <div class="user-resource-status resources-created"></div>
</div>
