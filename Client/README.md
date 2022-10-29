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

The upload.php file inquestion is what is find in this repo under `Server/www/upload.php`.