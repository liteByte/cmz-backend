#  Proyecto CMZ - Framework CodeIgniter.

## Requirements

1. PHP >= 7.0.0
2. CodeIgniter 3.1.2

## Setup (Wamp/Xamp)
```
1. Install XAMP server
2. Run XAMP server
3. Test XAMP Server (localhost)
4. Create Database
   4.1 Connect to localhost:3306 with user "root" (if the db is not starting, 
   try changing the port, it's usually skype not letting 3306 go)
   4.2 Execute "CREATE DATABASE cmz"
5. Copy the .env.example file, name it .env and change the values as necessary
6. Copy the entire project to XAMP's htdocs folder (delete everything that was 
   there before)
   NOTE: you will need to constantly upload changes to this folder or work 
   directly on it.
7. Install composer
8. Go to the directory that has the "composer.json" file and run "composer install" in cmd
9. Exectute migrations
   http://localhost/migrate/up
```

## Otra Documentación / Tutorial

* [NetTuts: Working with RESTful Services in CodeIgniter](http://net.tutsplus.com/tutorials/php/working-with-restful-services-in-codeigniter-2/)