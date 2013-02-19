//
// Remove all Mongo data and insert some sample data.
//
// We are not using Mongo plugin in the sample right now. It works,
// but cause issues because the controller uses Mongo too, and the playbook
// restarts it, throwing an exception. It should be used only on SSH connections.

var plugins = {
  "Core": {},
  "MySQL": {
    "mysql_root_password": "tplqomnscy323e"
  },
  "GitFiles": {},
  "ApacheHTTPD": {
    "landing_repo_url" : "https://github.com/trainingwheels/sample-landing-page.git"
  },
  "VSFTPd": {},
  "Drupal": {},
  "PHP": {
    "apc_shm_size": "89M"
  },
  "Nodejs" : {},
  "Supervisor": {},
  "Cloud9IDE": {}
};
var resources = {
  "drupal_files": {
    "type": "GitFilesResource",
    "plugin": "GitFiles",
    "title": "Drupal Code",
    "default_branch": "master",
    "subdir": "",
    "repo_url": "https://github.com/fourkitchens/trainingwheels-drupal-files-example.git"
  },
  "drupal_db": {
    "type": "MySQLDatabaseResource",
    "plugin": "MySQL",
    "title": "Drupal Database",
    "dump_path": "database.sql.gz",
  },
  "drupal_ide": {
    "type": "Cloud9IDEResource",
    "plugin": "Cloud9IDE",
    "title": "Cloud9 IDE"
  }
};

db.course.remove();
db.course.insert({
  "id": 1,
  "course_name": "mycourse",
  "description": "This is a sample course running locally on the same server as the controller.",
  "env_type": "ubuntu",
  "title": "Sample Course",
  "host": "localhost",
  "user": "",
  "port": 22,
  "plugins": plugins,
  "resources": resources
});
db.course.insert({
  "id": 2,
  "course_name": "sshcourse",
  "description": "This is a sample remote course running on a separate virtual server.",
  "env_type": "ubuntu",
  "title": "Sample Remote Course",
  "host": "remote.classroom",
  "user": "ubuntu",
  "port": 22,
  "plugins": plugins,
  "resources": resources
});

db.counters.remove();
db.counters.insert({"_id": "course_id", "seq": 2});
