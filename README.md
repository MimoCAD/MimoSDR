# MimoSDR
MimoCAD's MimoSDR powered by Trunk-Recorder and SQLite

# Support
Help can be found on the Trunk-Recorder Discord Server under the community projects and MimoSDR channel.

[Trunk-Recorder Discord -> Community Projects -> MimoSDR](https://discord.gg/FKfaqtvy8f)

## Client Side
Your Trunk-Recorder box requires PHP 8.0 or better to run this script, you can install that via the following commands:

```SH
apt -y install build-essential pkg-config openssl libssl-dev libxml2-dev libonig-dev sqlite3 libsqlite3-dev libcurl4-openssl-dev zlib1g-dev libpng-dev autoconf bison re2c

wget https://www.php.net/distributions/php-8.1.12.tar.xz
tar xf php-8.1.12.tar.xz
cd php-8.1.12
./configure --enable-fpm --enable-pcntl --enable-calendar --enable-mbstring --with-zlib --with-openssl --with-libxml --enable-soap
make -j
make install
```

The `MimoUpload.sh` script must be placed in the root directory where Trunk-Recorder is ran from.

Your config.json must have a `uploadScript` defined as the `"./MimoUpload.sh"`.

[SNIP]
```JSON
    "systems": [{
        "uploadScript":             "./MimoUpload.sh",
```
[SNIP]

In `MimoUpload.sh` edit the file to point to where your Upload.php script is located on the web.

```PHP
echo file_get_contents('<PointToWhereYourHTTPServerIs>/upload.php', false, $context);
```

The upload.php file in question is what is find in this repo under `Server/www/upload.php`.

## Server Side
Your server requires PHP 8.0 or better to use it. The `Server/www/` should be the web root. Actual setup of the web server is out of the scope of this docuemnt and project.

You must have SQLite installed and enabled PHP (both are true by default with PHP 8.x). You can then execute a simple setup script in your webroot such as this to populate the SQLite database.

```PHP
<?php
namespace MimoSDR;
require('../bootstrap.php');
$MimoSDR = new Database('../MimoSDR.db');
$MimoSDR->query(Audio::SQL_CREATE);

```

That will create the `MimoSDR.db` in the directory above where the file is located. This should ideally executed be web root, and only used once then deleted. The database file will therefore be above the www directory next to the `autoload.php` and `bootstrap.php` files.

# PHPLiteAdmin

Lastly, in order to admin the database, you may want to use [PHPLiteAdmin](https://github.com/Dygear/PHPLiteAdmin).