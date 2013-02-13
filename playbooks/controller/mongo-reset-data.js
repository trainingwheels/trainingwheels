//
// Remove all Mongo data and insert some sample data.
//
// We are not using Mongo and Nodejs plugins in the sample right now. They work,
// but cause issues because the controller uses Mongo too, and the playbook
// restarts it, throwing an exception. They should be used only on SSH connections.

db.course.remove()
db.course.insert({
    "id": 1,
    "course_name": "mycourse",
    "course_type": "drupal",
    "description": "This is a sample course.",
    "env_type": "ubuntu",
    "repo": "https://github.com/fourkitchens/trainingwheels-drupal-files-example.git",
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
      "ApacheHTTPD": {},
      "VSFTPd": {},
      "Drupal": {},
      "PHP": {
        "apc_shm_size": "89M"
      },
    },
    "resources": {
      "drupal_files": {
        "type": "GitFilesResource",
        "plugin": "GitFiles",
        "title": "Code",
        "default_branch": "master",
        "subdir": "mycourse",
        "repo_url": "https://github.com/fourkitchens/trainingwheels-drupal-files-example.git"
      },
      "drupal_db": {
        "type": "MySQLDatabaseResource",
        "plugin": "MySQL",
        "title": "Database",
        "dump_path": "database.sql.gz",
      }
    }
})

db.counters.remove()
db.counters.insert({ "_id": "course_id", "seq": 1 })
