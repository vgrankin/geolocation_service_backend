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
    - Ctreate database and table to work with:
    
        CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
        USE `test`;
        
        CREATE TABLE `ipinfos` (
          `ip` varbinary(16) NOT NULL,
          `city` varchar(255) DEFAULT NULL,
          `country` varchar(2) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
        
        ALTER TABLE `ipinfos`
          ADD PRIMARY KEY (`ip`);
          
## Implementation details:

- All application code is stored in /src folder. ./src/app.php is used to create Silex 
application. We configure ActiveRecord there, define required controller and services.
Services are injected into controller to promote better OOD and testability.
- In terms of workflow the following interaction is used: to get the job done for any 
given request something like this is happening: Controller uses Service 
(which uses Service) which uses ActiveRecord which uses Model to work with DB. 
This way we have a good thin controller along with practices like Separation of Concerns, 
Single responsibility principle etc. SOLID basically.
- App\Service\ResponseErrorDecoratorService is a simple helper to prepare error responses 
and to make this process consistent along the framework. It is used every time error 
response (such as status 400 or 404) is returned.
- HTTP status codes and REST API url structure is implemented in a way similar to 
described here (feel free to reshape it how you wish): 
https://blog.mwaysolutions.com/2014/06/05/10-best-practices-for-better-restful-api/
- No authentication (like JWT) is used. Application is NOT secured.
- All application code is in /src folder
- All tests are located in /tests folder
- In most cases the following test-case naming convention is used: MethodUnderTest____Scenario____Behavior()
- index.php is a starting point which uses (./src/app.php) and is located outside ./src in ./web/index.php
- http://ipinfo.io/ service is used to retrieve IP geolocation information. As soon as information is retrieved
  it is then saved to database and will be retrieved from DB (cache) next time corresponding endpoint is 
  accessed with given IP.
     
## Usage/testing:

    First of all, start your MySQL server and PHP server. Here is example of how to start local PHP server on Windows 10:
    D:\dev\geolocation_service_backend>php -S 127.0.0.1:8000 -t web
    * After that http://localhost:8000 should be up and running
    
    * If you use docker, make sure PHP and MySQL (with required database) containers are up and running

It is assumed that this REST API will be consumed by frontend application. Please see another github repository
for a frontend (jQuery-based) consumer: https://github.com/vgrankin/geolocation_service_frontend

Another way is that you can look at and run PHPUnit tests (look at tests folder where all test files are located) 
to execute all possible REST API endpoints. (To run all tests execute this command from project's root folder: 
`./vendor/bin/simple-phpunit --configuration phpunit.xml.dist`, but if you want, you can also use browser to 
manually access REST API endpoints. Here is how to test all currently available API endpoints:
    
We can simply use Google Chrome browser to access all endpoints:

    * Here is a table of possible operations:
    (to make testing fun you can replace $ip with "8.8.8.8" for example. Just temporary replace
    line IpinfoController::ipinfo(): $ip = $this->request->getClientIp(); to $ip = "8.8.8.8"
    and here: IpinfoController::index(): $ip = $this->request->getClientIp(); to $ip = "8.8.8.8")
    
    --------------------------- --------  -------------------- 
     Action                      Method    Path                
    --------------------------- --------  --------------------  
     Get client's IP address     GET       /api/ip
     Get IP geolocation info     GET       /api/ipinfo     
    --------------------------- --------  --------------------     
    
    * First of all, create DB and table using SQL queries provided above.
    
    ===========================================================    
    - Here is how to access REST API endpoint to create listing:
    
    method: GET
    url: http://localhost:8000/api/ip            
    
    Response should look similar to this:
    
    {"data":{"ip":"8.8.8.8"}}     
    
    ===========================================================
    - Get geolocation information:      
    
    method: GET
    url: http://localhost:8000/api/ipinfo
        
    Response should look similar to this:
    
    {"data":{"city":"Mountain View","country":"US"}}                
    
    Here is a response in case you are trying to get geolocation IP info for a non-public IP:    
    {
        "error": {
            "code": 400,
            "message": "Not a public IP. Geolocation info is not available."
        }
    }
    
    * There are other errors addressed, but JSON result you get back is consistent and looks 
      like in example just described.
