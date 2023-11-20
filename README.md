### installation

##### First step

 `composer install` 

##### Second step
The Passport service provider registers its own database migration directory with the framework, so you should migrate your database after installing the package. The Passport migrations will create the tables your application needs to store clients and access tokens:

`php artisan migrate`

This will generate the necessary encryption keys required for the creation of secure access tokens. Additionally, the command will establish "personal access" and "password grant" clients crucial for the generation of access tokens:
`php artisan sanctum:install`
### Test
Third step run the phpunit test command
`./vendor/bin/phpunit`