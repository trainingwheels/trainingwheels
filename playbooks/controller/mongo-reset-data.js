
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
    "plugin_ids": [1,2,3,4,5,6,7]
})
db.plugin.remove()
db.plugin.insert({
  "id": 1,
  "type": "Core",
  "title": "Training Wheels Core",
})
db.plugin.insert({
  "id": 2,
  "type": "MySQL",
  "title": "Database",
  "key": "drupal_db",
  "dump_path": "database.sql.gz",
  "mysql_root_password": "tplqomnscy323e"
})
db.plugin.insert({
  "id": 3,
  "type": "GitFiles",
  "title": "Files",
  "key": "drupal_files",
  "repo_url": "https://github.com/fourkitchens/trainingwheels-drupal-files-example.git"
})
db.plugin.insert({
  "id": 4,
  "type": "ApacheHTTPd",
  "title": "Webroot",
})
db.plugin.insert({
  "id": 5,
  "type": "VSFTPd",
  "title": "FTP Server",
})
db.plugin.insert({
  "id": 6,
  "type": "Drupal",
  "title": "Drupal",
})
db.plugin.insert({
  "id": 7,
  "type": "PHP",
  "title": "PHP",
  "apc_shm_size": "89M"
})
db.plugin.insert({
  "id": 8,
  "type": "MongoDB",
  "title": "MongoDB",
})
db.plugin.insert({
  "id": 9,
  "type": "Nodejs",
  "title": "Nodejs",
})
db.counters.remove()
db.counters.insert({ "_id": "course_id", "seq": 1 })
