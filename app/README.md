Install Composer
----------------

To get Composer on your system:

    curl -s https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer

Building the app:

    cd trainingwheels/app
    composer install

TODO
----

* Re-implement caching in CachedObject.
* Re-implement drush using Console component.
* Remove the custom logger, replace with the Silex one.


Manual Testing REST API
-----------------------

Using CURL, drop the -i and pipe to python -mjson.tool to format the JSON response, e.g.:

    curl http://training.wheels:8888/tw/rest/user/1-instructor | python -mjson.tool

Retrieve a user:

    curl http://training.wheels:8888/tw/rest/user/1-instructor -i && echo ''; echo ''

Create user:

    curl http://training.wheels:8888/tw/rest/user -d '{"courseid":"1","user_name":"carol"}' -H 'Content-Type: application/json' -i && echo ''; echo ''

