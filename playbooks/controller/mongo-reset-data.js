
// Remove all Mongo data and insert some sample data.

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
    "plugin_ids": [1,2]
})
db.plugin.remove()
db.plugin.insert({
  "id": 1,
  "type": "MySQL",
  "title": "Database",
  "key": "drupal_db",
  "dump_path": "database.sql.gz",
  "mysql_root_password": "tplqomnscy323e"
})
db.plugin.insert({
  "id": 2,
  "type": "GitFiles",
  "title": "Files",
  "key": "drupal_files",
  "repo_url": "https://github.com/fourkitchens/trainingwheels-drupal-files-example.git"
})
db.counters.remove()
db.counters.insert({ "_id": "course_id", "seq": 1 })
