<div class="user-summary">
  <div class="user-info">
    <a class="ss-icon" href="#" {{action showUser user}}>directright</a>
    <h3><a class="user-name" href="#" {{action showUser user}}>{{user_name}}</a></h3>
    <div class="indicators-summary-users">
      <div class="ss-user"{{bindAttr class="view.css_class_login_status logged_in"}}></div>
      <div class="ss-folder" {{bindAttr class="view.css_class_resources_status resources_status"}}></div>
    </div>
  </div>
</div>
