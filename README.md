# Silex (PHP Symfony based micro-service framework) based REST API geolocation service

This is a Silex based REST API (without authentication) backend. 
It more or less follows guidline/summary provided by this excellent 
article: https://blog.mwaysolutions.com/2014/06/05/10-best-practices-for-better-restful-api/

Regarding project itself. Several ideas were in mind, like thin-controller and TDD approach. 
SOLID principles, speaking names and other good design 
practices were also kept in mind.  
Most business logic is moved from controllers to corresponding services, 
which in turn use other services and ActiveRecord ORM to execute various DB queries.

## What this REST API is doing?

This is a simple micro-service implemented as REST API according 
to given requirements: 
    
    Develop geolocation service based on user IP address.
    
    Frontend contains one page which shows the IP address of the current user 
    and button "Get geolocation". When the user clicks the button, we load 
    location info (city and country) via Ajax request to the backend.
    
    Backend retrieves information from the JSON service of ipinfo.io with 
    caching into the database. Only city and country must be cached and 
    returned to Frontend. Let's assume that we have MySQL database "test" 
    on localhost with user "test_user" and with password "secret"
    
    The backend should be built on Silex (latest version) with 
    ActiveRecord ORM (https://packagist.org/packages/php-activerecord/php-activerecord) 
    using Composer.
    
    For Frontend, jQuery can be used.

See "Usage/testing" section.

## Technical details / Requirements:
- Current project is built using Silex 2.0 PHP micro-framework
  (It is based on the Symfony Components. See: https://silex.symfony.com/download)
- PHPUnit is used for tests
	* Note: it is better to run symfony/phpunit-bridge (built-in) PHPUnit 
      which will be available after you install the project on your machine (using composer install), 
      not the global one you have on your system, because different versions of PHPUnit expect different syntax. 
      Tests for this project were built using symfony/phpunit-bridge version of PHPUnit. 
      You can run all tests by running this command from project directory: 
      `./vendor/bin/simple-phpunit --configuration phpunit.xml.dist`
- PHP 7.2.9 is used so you will need something similar available on your system 
  (there are many options to install it: Docker/XAMPP/standalone version etc.)
- MariaDB (MySQL) is required (10.1.31-MariaDB was used during development)
- ActiveRecord ORM is used to work with MySQL database

## Installation:
	
    - git clone https://github.com/vgrankin/geolocation_service_backend
    
    - go to project directory and run: composer install
    
    * at this point make sure MySQL is installed and is running	
    - open src/app.php file in project directory
    * alternatively create a new file for configuration and add it to .gitignore
    
    - set connections for ActiveRecord:
        - This is example of how my config looks (same database for test/dev and production in this case): 
        
        ActiveRecord\Config::initialize(
            function ($cfg) {
                $cfg->set_connections(
                    [
                        'development' => 'mysql://test_user:secret@localhost/test',
                        'test' => 'mysql://test_user:secret@localhost/test',
                        'production' => 'mysql://test_user:secret@localhost/test',
                    ]
                );
            }
        );        
        
    * more infos:
        - https://packagist.org/packages/php-activerecord/php-activerecord        
                
    - In order to configure PHPUnit for your needs, you will need to create local version of phpunit.xml:
        - for that, just copy phpunit.xml.dist and rename it to phpunit.xml
        * you can skip this step and use existing configuration: phpunit.xml.dist
    - If you want to try this API without manually inserting new records, here are some example records to start with:
    
        DELETE FROM `listing`;
        
        DELETE FROM `city`;
        INSERT INTO `city` (`id`, `name`) VALUES (1, 'Berlin');
        INSERT INTO `city` (`id`, `name`) VALUES (2, 'Porta Westfalica');
        INSERT INTO `city` (`id`, `name`) VALUES (3, 'Lommatzsch');
        INSERT INTO `city` (`id`, `name`) VALUES (4, 'Hamburg');
        INSERT INTO `city` (`id`, `name`) VALUES (5, 'Bülzig');
        INSERT INTO `city` (`id`, `name`) VALUES (6, 'Diesbar-Seußlitz');
        
        DELETE FROM `period`;
        INSERT INTO `period` (`id`, `name`, `date_addon`) VALUES (1, 'Plus 3 days', 'P3D');
        INSERT INTO `period` (`id`, `name`, `date_addon`) VALUES (2, 'Plus 20 days', 'P40D');
        INSERT INTO `period` (`id`, `name`, `date_addon`) VALUES (3, 'Plus 60 days', 'P60D');
        
        DELETE FROM `section`;
        INSERT INTO `section` (`id`, `name`) VALUES (1, 'Sonstige Umzugsleistungen');
        INSERT INTO `section` (`id`, `name`) VALUES (2, 'Abtransport, Entsorgung und Entrümpelung');
        INSERT INTO `section` (`id`, `name`) VALUES (3, 'Fensterreinigung');
        INSERT INTO `section` (`id`, `name`) VALUES (4, 'Holzdielen schleifen');
        INSERT INTO `section` (`id`, `name`) VALUES (5, 'Kellersanierung');
        
        DELETE FROM `user`;
        INSERT INTO `user` (`id`, `password`) VALUES ('test1@restapier.com', 
            '$2y$10$dK0QHbmFiBaOKDx0sjNFAemqBhSjdjifTg6HZE3P6mQ9hIbAPraey');
        INSERT INTO `user` (`id`, `password`) VALUES ('test2@restapier.com', 
            '$2y$10$dK0QHbmFiBaOKDx0sjNFAemqBhSjdjifTg6HZE3P6mQ9hIbAPraey');
    
        * These records are required in order to create listing using REST API. This is because
          listing consists of several fields, including id of the city where listing is published,
          period which will be used to decided when listing will expire (in the examples above - 
          in 3 days, in 40 days and in 60 days from publishing date (P3D, P40D and P60D are 
          PHP date interval formats. More information here: 
          http://www.php.net/manual/de/dateinterval.format.php).
          
## Implementation details:

- No external libraries are used for this REST API. 
Everything is intentionally coded from scratch 
(as a demo project to explicitly demonstrate REST API application design) 
- In terms of workflow the following interaction is used: to get the job done for any 
given request usually something like this is happening: Controller uses Service 
(which uses Service) which uses Repository which uses Entity. This way we have a good 
thin controller along with practices like Separation of Concerns, Single responsibility 
principle etc.
- App\EventSubscriber\ExceptionSubscriber is used to process all Symfony-thrown exceptions 
and turn them into nice REST-API compatible JSON response (instead of HTML error pages 
shown by default in case of exception like 404 (Not Found) or 500 (Internal Server Error))
- App\Service\ResponseErrorDecoratorService is a simple helper to prepare error responses 
and to make this process consistent along the framework. It is used every time error 
response (such as status 400 or 404) is returned.
- HTTP status codes and REST API url structure is implemented in a way similar to 
described here (feel free to reshape it how you wish): 
https://blog.mwaysolutions.com/2014/06/05/10-best-practices-for-better-restful-api/
- No authentication (like JWT) is used. Application is NOT secured) 
- All application code is in /src folder
- All tests are located in /tests folder
- In most cases the following test-case naming convention is used: MethodUnderTest____Scenario____Behavior()
     
## Usage/testing:

    First of all, start your MySQL server and PHP server. Here is example of how to start local PHP server on Windows 10:
    C:\Users\admin\PhpProjects\symfony_restapi>php -S 127.0.0.1:8000 -t public
    * After that http://localhost:8000 should be up and running
    
    * If you use docker, make sure PHP and MySQL (with required database) containers are up and running

You can simply look at and run PHPUnit tests (look at tests folder where all test files are located) 
to execute all possible REST API endpoints. (To run all tests execute this command from project's root folder: 
"php bin/phpunit"), but if you want, you can also use tools like POSTMAN to manually access REST API endpoints. 
Here is how to test all currently available API endpoints:
    
We can use POSTMAN to access all endpoints:

    * Here is a table of possible operations:
    
    --------------------------- --------  -------------------- 
     Action                      Method    Path                
    --------------------------- --------  --------------------  
     Create listing              POST      /api/listings       
     Get listing                 GET       /api/listings/{id}  
     Get listings (filtered)     GET       /api/listings       
     Update listing              PUT       /api/listings/{id}  
     Delete listing              DELETE    /api/listings/{id}
    --------------------------- --------  --------------------     
    
    * First of all, clear DB and install some sample data using SQL queries provided above.
    
    ===========================================================    
    - Here is how to access REST API endpoint to create listing:
    
    method: POST
    url: http://localhost:8000/api/listings
    Body (select raw) and add this line: 
    
    {"section_id":1,"title":"Test listing 1","zip_code":"10115","city_id":1,"description":"Test listing 1 description Test listing 1 description","period_id":1,"user_id":"test1@restapier.com"}        
    
    Response should look similar to this:
    
    {
        "data": {
            "id": 326,
            "section_id": 1,
            "title": "Test listing 1",
            "zip_code": "10115",
            "city_id": 1,
            "description": "Test listing 1 description Test listing 1 description",
            "publication_date": "2018-09-10 14:29:33",
            "expiration_date": "2018-09-13 14:29:33",
            "user_id": "test1@restapier.com"
        }
    }        
    
    ===========================================================
    - Update attributes of a listing. 
      Let's say we want to change `city` and `title` of some particular listing:
    
    method: PUT
    url: http://localhost:8000/api/listings/{id} (where {id} is id of existing listing you want to modify, 
                                                   for example http://localhost:8000/api/listings/326)
    Body (select raw) and add this line: 
    {"title": "New title 1", "city_id": 2}        	
    
    Response should look similar to this:
    
    {
        "data": {
            "id": 326,
            "section_id": 1,
            "title": "New title 1",
            "zip_code": "10115",
            "city_id": 2,
            "description": "Test listing 1 description Test listing 1 description",
            "publication_date": "2018-09-10 14:29:33",
            "expiration_date": "2018-09-13 14:29:33",
            "user_id": "test1@restapier.com"
        }
    }
            
    ===========================================================    	
    - Get listings. 
      Let's say we want to get listings for some particular section and city. 
      You can do this using filter:                                                       
    
    method: GET
    url: http://localhost:8000/api/listings?section_id=1&city_id=1&days_back=30&excluded_user_id=1 
        (where 
            - section_id is id of a category you want to filter by
            - city_id is id of a city to filter by
            - days_back is used to get listings published up to 30 days ago
            - excluded_user_id if listing belongs to given excluded_user_id, it will be filtered out
            * all filter keys are optional (you can use none, one or all of them if needed)
            )
    Body: none (this is a GET request, so we pass params via query string)     
    
    Response should look similar to this:
    
    {
        "data": {
            "listings": [
                {
                    "id": 326,
                    "section_id": 1,
                    "title": "New title 1",
                    "zip_code": "10115",
                    "city_id": 2,
                    "description": "Test listing 1 description Test listing 1 description",
                    "publication_date": "2018-09-10 14:29:33",
                    "expiration_date": "2018-09-13 14:29:33",
                    "user_id": "test1@restapier.com"
                }
            ]
        }
    }
    
    ===========================================================
    - Delete listing:
    
    method: DELETE
    url: http://localhost:8000/api/listings/{id} (where {id} is id of existing listing you want to delete, 
      for example http://localhost:8000/api/listings/326)	       
    
    Response HTTP status should be 204 (endpoint is successfully executed, but there is nothing to return)
    
    ===========================================================
    * Errors are also taken into account (see PHPUnit tests on which errors are addressed) 
      and usually if there was an error during your request, special JSON response will be return. 
      
    Here are examples:
    
    You will see this in case item is deleted already or in case of inexisting endpoint:  
    {
        "error": {
            "code": 404,
            "message": "Not Found"
        }
    }    
    
    Here is response in case you tried to filter by city_id=XXX:      
    {
        "error": {
            "code": 400,
            "message": "Unexpected city_id"
        }
    }    
    
    Here is a response in case you are trying to use inexisting section id to create new listing:    
    {
        "error": {
            "code": 400,
            "message": "Unable to find section by given section_id"
        }
    }    
    
    * There are many other errors addressed, but JSON result you get back is consistent and looks 
      like in examples just described.

## To improve this REST API you can implement:
- pagination
- customize App\EventSubscriber to also support debug mode during development (to debug status 500 etc.) 
 (currently you need to manually go to processException() and just use "return;" on the first line of this method's body to avoid exception "prettyfying")
- SSL (https connection)
- there are many strings returned from services in case of various errors (see try/catch cases in ListingService.php for example). It will be probably better to convert these to exceptions instead.