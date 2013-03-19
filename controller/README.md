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

Ember needs Ruby to build. The Training Wheels Vagrant virtual machine already has 2 versions of Ruby on it - one that comes prepackaged with the virtual image in /opt, and one that is installed through apt for Compass and Aurora, the standard Ubuntu packages. However, we need the latest for Ember, so we use rvm, which installs the latest Ruby for just your user in `~/.rvm`:

    curl -#L https://get.rvm.io | bash -s stable --ruby

Make sure running `ruby -v` give you the latest stable (2.0.0 right now). You will need to add the following line in `.bash_profile` to get your bash settings back after rvm has installed:

    source ~/.profile

Install Bundler:

    gem install bundler

You can checkout the repositories, or move an Ember.js checkout into the shared Training Wheels folder, or make another shared folder definition in VagrantFile, then do the following:

    bundle

To build the .js, run:

    bundle exec rake dist

To run tests, run `rackup`, then visit: http://training.wheels:9292/?package=PACKAGE_NAME. Replace PACKAGE_NAME with the name of the package you want to run. For example:

Checkout the README.md for Ember.js for reference.

Console application
-------------------

    cd cli
    ./tw

Working with Mongo
------------------

To access database CLI, use `tw mongo:cli` or `tw m:c` on the command line.

Some command examples:

    show collections
    db.course.find().pretty()
    db.counters.find()

To reset the backend data in Vagrant environment, use:

    mongo -utrainingwheels -ptrainingwheelsApp trainingwheels /var/trainingwheels/mongo-reset-data.js

Working with Web Inspector and Ember.js
---------------------------------------

To view data store:

    App.Course.filter(function(data) { return true; } ).objectAt(0).serialize()

To figure out what class an object is, use toString():

    this.toString()

In general, it's useful to switch off caching in your web inspector settings in Chrome.

Manual Testing REST API
-----------------------

Using CURL, drop the -i and pipe to python -mjson.tool to format the JSON response, e.g.:

    curl http://training.wheels:8000/rest/user/1-instructor | python -mjson.tool

Users
=====

Retrieve a user:

    curl http://training.wheels:8000/rest/users/1-instructor -H "Accept: application/json" -i && echo ''; echo ''

Create user:

    curl http://training.wheels:8000/rest/user_summaries -d '{"user_summary": {"course_id":"1","user_name":"instructor"}}' -H 'Content-Type: application/json' -i && echo ''; echo ''

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

Get the course build information:

    curl http://training.wheels:8000/rest/course_build | python -mjson.tool
