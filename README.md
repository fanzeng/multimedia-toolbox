
<!--
$ php --version
PHP 7.4.30 (cli) (built: Jun 27 2022 08:21:19) ( NTS )
Copyright (c) The PHP Group
Zend Engine v3.4.0, Copyright (c) Zend Technologies
    with Zend OPcache v7.4.30, Copyright (c), by Zend Technologies
$ php artisan --version
Laravel Framework 6.20.44
-->

# install filezilla
sudo apt-get install filezilla

# install php sqlite driver
sudo apt-get install php-sqlite3

# start server
cd server
php artisan serve

# start client
cd client
npx ng serve
npx ng serve --prod

# build server docker image
cd server
docker build -t multimedia-toolbox-server .

# test server docker image
docker run -it -p 8000:8000 multimedia-toolbox-server