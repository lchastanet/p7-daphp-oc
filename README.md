[![Codacy Badge](https://app.codacy.com/project/badge/Grade/e59616c3cafc4b7fb6aa5ab76a4cd642)](https://www.codacy.com/manual/Fr0x13/p7-daphp-oc/dashboard?utm_source=github.com&utm_medium=referral&utm_content=friexo/p7-daphp-oc&utm_campaign=Badge_Grade)
[![Maintainability](https://api.codeclimate.com/v1/badges/c7e8de34824adb29ef3c/maintainability)](https://codeclimate.com/github/friexo/p7-daphp-oc/maintainability)

# **p7-daphp-oc**

This project has been carried out with the aim of passing a diploma on the [OpenClassrooms.com](https://openclassrooms.com/) learning platform.
To install it you need to have [composer](https://getcomposer.org/) installed.

Then run

```shell
$ composer install
```

Then generate the SSH keys for the JWT generation:

```shell
$ mkdir -p config/jwt
$ openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
$ openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```



Then create or modify the **.env** file to connect to your mysql installation and setup the mail server

```shell
APP_ENV=dev
APP_SECRET=37475f44748fe184be7865821e6828d6
DATABASE_URL=mysql://username:password@127.0.0.1:3306/db_name
JWT_PASSPHRASE=#your awesome passphrase
```

Then run the following command to create database

```shell
php bin/console doctrine:database:create
```

Then load the initial dataset into database

```
php bin/console doctrine:fixtures:load
```

And finally run the dev server to test app

- if you have installed the symfony cli tool :

```shell
symfony serve
```

- if you don't have installed the symfony cli tool :

```shell
php -S localhost:8000 -t public
```
