
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
    "plugin_ids": []
})

db.counters.remove()
db.counters.insert({ "_id": "course_id", "seq": 1 })

db.plugin.remove()
db.plugin.insert({
  "type": "Core",
  "title": "Training Wheels Core",
})
db.plugin.insert({
  "type": "MySQL",
  "title": "Database",
  "key": "drupal_db",
  "dump_path": "database.sql.gz",
  "mysql_root_password": "tplqomnscy323e"
})
db.plugin.insert({
  "type": "GitFiles",
  "title": "Files",
  "key": "drupal_files",
  "repo_url": "https://github.com/fourkitchens/trainingwheels-drupal-files-example.git"
})
db.plugin.insert({
  "type": "ApacheHTTPd",
  "title": "Webroot",
})
db.plugin.insert({
  "type": "VSFTPd",
  "title": "FTP Server",
})
db.plugin.insert({
  "type": "Drupal",
  "title": "Drupal",
})
db.plugin.insert({
  "type": "PHP",
  "title": "PHP",
  "apc_shm_size": "89M"
})
// Not using Mongo and Nodejs right now. They work, but cause issues because
// the controller uses Mongo too, and the playbook restarts it, throwing an
// exception.
// db.plugin.insert({
//   "type": "MongoDB",
//   "title": "MongoDB",
// })
// db.plugin.insert({
//   "type": "Nodejs",
//   "title": "Nodejs",
// })

// Associate all plugins with the course.
var allPlugins = db.plugin.find({}, { _id: 1})
do {
  var plugin = allPlugins.next()
  var id = plugin._id
  db.course.update({id: 1}, {$push: { plugin_ids: id}})
}
while (allPlugins.hasNext())

