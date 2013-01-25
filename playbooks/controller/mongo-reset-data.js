
// Remove all Mongo data and insert some sample data.

db.course.remove()
db.course.insert({ "id": 1, "course_name": "mycourse", "course_type": "drupal", "description": "This is a sample course.", "env_type": "ubuntu", "repo": "https://github.com/fourkitchens/trainingwheels-drupal-files-example.git", "title": "Sample Course", "host": "localhost", "user": "", "pass": ""})
db.counters.remove()
db.counters.insert({ "_id": "course_id", "seq": 1 })
