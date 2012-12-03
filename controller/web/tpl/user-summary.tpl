<div class="user user-summary" {{action showUser user}}>
  <div class="arrow-image arrow-down"></div>
  <h4>{{user_name}}</h4>
  {{id}}
  {{password}}
  {{view Ember.TextField valueBinding="user_name"}}
  <div {{bindAttr class="view.css_class_login_status logged_in"}}></div>

  <div class="user-resource-status resources-created"></div>
</div>
