# Install php7.2
sudo LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php7.2

# Install dependencies
sudo apt install php7.2-xml
sudo apt install php7.2-curl

# Create the bin directory
cd server
mkdir bin

# Setup composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --install-dir=bin --filename=composer
php -r "unlink('composer-setup.php');"

# Install
./bin/composer install
# install sqlite
sudo apt-get install php7.2-sqlite3

# install telescope
php artisan telescope:install

# init DB
php artisan migrate:fresh

# create local copy of .env
# cp .env.example .env

# Install ffmpeg
sudo apt install ffmpeg