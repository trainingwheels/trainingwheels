<div class="user-summary">
  <div class="user-info">
    <a class="ss-icon" href="#" {{action showUser user}}>navigateright</a>
    <h3><a class="user-name" href="#" {{action showUser user}}>{{user_name}}</a></h3>
    <div class="indicators-summary-users">
      <div {{bindAttr class="css_class_login_status"}}></div>
      <div {{bindAttr class="css_class_resource_overview_status"}}></div>
    </div>
  </div>
</div>
