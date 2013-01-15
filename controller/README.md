Install Composer
----------------

To get Composer on your system:

    curl -s https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer

Building the app:

    cd trainingwheels/controller
    composer install

TODO
----

* Re-implement caching in CachedObject.

Building Ember.js and Ember Data
--------------------------------

Prepare your Ubuntu 12.04 system:

    aptitude install ruby1.9.1 ruby1.9.1-dev
    gem install rake
    gem install bundler

Checkout the repositories, then do the following in the root of each:

    bundle install
    bundle exec rake dist

Console application
-------------------

    cd cli
    ./tw

Working with Mongo
------------------

To access database CLI, use `mongo trainingwheels` on the command line.

Some command examples:

    show collections
    db.course.find()
    db.counters.find()

To reset the backend data, use:

    cd trainingwheels/playbooks/controller
    mongo trainingwheels sample-data-mongo.js

Working with web inspector and Ember.js
---------------------------------------

To view data store:

    App.Course.filter(function(data) { return true; } ).objectAt(0).serialize()

To figure out what class an object is, use toString():

    this.toString()

Manual Testing REST API
-----------------------

Using CURL, drop the -i and pipe to python -mjson.tool to format the JSON response, e.g.:

    curl http://training.wheels:8000/rest/user/1-instructor | python -mjson.tool

Users
=====

Retrieve a user:

    curl http://training.wheels:8000/rest/users/1-instructor -H "Accept: application/json" -i && echo ''; echo ''

Create user:

    curl http://training.wheels:8000/rest/user -d '{"courseid":"1","user_name":"instructor"}' -H 'Content-Type: application/json' -i && echo ''; echo ''

Delete user:

    curl http://training.wheels:8000/rest/user/1-instructor -H "Accept: application/json" -X DELETE -i && echo ''; echo ''

Sync a user (e.g. of using a PUT):

    curl http://training.wheels:8000/rest/user/1-bob -X PUT -d '{"action":"resources-sync","sync_from":"instructor","target_resources":"*"}' -H 'Content-Type: application/json' -i && echo ''; echo ''

Courses
=======

Index of courses:

    curl http://training.wheels:8000/rest/course_summaries -i && echo ''; echo ''

Get a course:

    curl http://training.wheels:8000/rest/courses/1 -i && echo ''; echo ''
