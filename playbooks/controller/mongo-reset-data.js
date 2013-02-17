//
// Remove all Mongo data and insert some sample data.
//
// We are not using Mongo plugin in the sample right now. It works,
// but cause issues because the controller uses Mongo too, and the playbook
// restarts it, throwing an exception. It should be used only on SSH connections.

db.course.remove()
db.course.insert({
    "id": 1,
    "course_name": "mycourse",
    "description": "This is a sample course.",
    "env_type": "ubuntu",
    "title": "Sample Course",
    "host": "localhost",
    "user": "",
    "pass": "",
    "plugins": {
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
    },
    "resources": {
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
    }
})

db.counters.remove()
db.counters.insert({ "_id": "course_id", "seq": 1 })
