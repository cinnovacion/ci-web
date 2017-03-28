# ASSCIC

Sistema de Control de Asistencia del Centro de Innovacion - Fundacion Zamora Teran

## Instalar PHP y Mariadb

> Fedora

``` bashscript
$ su -c "dnf -y install php mariadb-server"
```

> Ubuntu / Debian

``` bashscript
$ sudo apt-get install php mariadb
```

> Mageia

``` bashscript
$ su -c "dnf -y install php php-phar mariadb"
```

## Instalar composer

``` bashscript
$ php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
$ php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
$ php composer-setup.php
$ php -r "unlink('composer-setup.php');"
```

> Fedora

``` bashscript
$ su -c "mv composer.phar /usr/local/bin/composer"
```

> Ubuntu / Debian

``` bashscript
$ sudo mv composer.phar /usr/local/bin/composer
```

## Configuara Apache

## Clonar el repo

``` bashscript
$ git clone https://github.com/cinnovacion/civ.git
```

## Instalar slim-framework

``` bashscript
$ cd civ
$ composer install
```
