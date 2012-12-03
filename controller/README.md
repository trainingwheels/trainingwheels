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

Building Ember
--------------

    rvm install 1.9.2 --with-openssl-dir=$rvm_path/usr
    rvm 1.9.2
    bundle exec install
    bundle rake build

Console application
-------------------

    cd cli
    ./tw

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

    curl http://training.wheels:8000/rest/courses -i && echo ''; echo ''

Get a course:

    curl http://training.wheels:8000/rest/courses/1 -i && echo ''; echo ''
