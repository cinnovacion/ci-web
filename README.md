# ASSCIC

### Sistema de Control de Asistencia del Centro de Innovacion - Fundacion Zamora Teran.

## Instalar PHP y Mariadb

Dependencias del sistema operativo para el funcionamiento del framework.

> Fedora

``` bashscript
$ su -c "dnf -y install php php-pdo php-pdo_mysql mariadb-server"
```

> Ubuntu / Debian

``` bashscript
$ sudo apt-get install php php7.0-mysql mariadb-server
```

> Mageia

``` bashscript
$ su -c "dnf -y install php php-phar mariadb"
```

## Instalar composer

Instalacion del manejador de dependencias de PHP.

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

> Mageia

``` bashscript
$ su -c "mv composer.phar /usr/local/bin/composer"
```

## Clonar el repo

Descargar el contenido del sistema.

``` bashscript
$ git clone https://github.com/cinnovacion/civ.git
```

## Instalar slim-framework

Descargar las dependencias del framework.

``` bashscript
$ cd civ
$ composer install
```
## Configurar la Base de Datos

1. Iniciar el servidor de bases de datos.

``` bashscript
$ systemctl start mariadb
```

2. Configuraciones generales.

``` bashscript
$ mysql_secure_installation
```

3. Crear y restaurar la base de datos.

``` bashscript
$ mysql -u root -p
> CREATE DATABASE asscic;
> QUIT
$ mysql -u root -p asscic < resources/db/asscic_db.sql
```

## Iniciar el servidor

Inciar el servidor web local.

> Fedora

``` bashscript
$ su -c "php -S 127.0.0.1:80"
```

> Ubuntu / Debian

``` bashscript
$ sudo php -S 127.0.0.1:80
```

> Mageia

``` bashscript
$ su -c "php -S 127.0.0.1:80"
```

Ahora el sistema es accesible desde [http://localhost](http://127.0.0.1).
