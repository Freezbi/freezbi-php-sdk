Freezbi SDK
=================================================
Freezbi SDK is a PHP client library to work with
[Freezbi Notification System](http://www.freezbi.com/).



Installation
-------------------------------------------------
SDK has been written in PHP 5.4 and has no dependencies on external packages.
You only have to ensure that curl and openssl extensions (that are part of
standard PHP distribution) are enabled in your PHP installation.

The project attempts to comply with PSR-4 specification for autoloading classes from file paths. 
As a namespace prefix is 'Freezbi\' with base directory '{your-installation-dir}/'.

But if not using PSR-4 the installation is as easy as downloading the package and storing it
under any location that will be available for including by

    require_once '{your-installation-dir}/Freezbi/Autoloader.php';


Installation with Composer
-------------------------------------------------
You can use Freezbi SDK library as a dependency in your project with Composer. A composer.json file is available in the repository and it has been referenced on packagist. 

The installation with Composer is easy, reliable : 
Step 1 - Add the Freezbi SDK as a dependency in your composer.json file as follow :

    "require": {
        ...
        "freezbi/freezbi-php-sdk": "dev-master"
    },

Step 2 - Update your dependencies with Composer

    you@yourhost:/path/to/project$ php composer.phar update freezbi/freezbi-php-sdk

The Library has been added into your dependencies and ready to be used.

License
-------------------------------------------------
Freezbi SDK is distributed under MIT license, see LICENSE file.


Contacts
-------------------------------------------------
Report bugs or suggest features using
[Freezbi Contact Page](http://www.freezbi.com/contact).


Examples
-------------------------------------------------
3 examples are available on examples directory

- 1 Example without any http call
- 1 Example with an http call on freezbi website (get the last post)
- 1 Example with an http call but with parameters (1 call for each parameter) => Youtube channel subscription



Sample usage
-------------------------------------------------

    // Init the Freezbi Api
    $freezbiApi = new Freezbi\FreezbiApi();
    $freezbiApi->TemporaryFolder = 'temp/';
    $freezbiApi->Delay = 3600 * 24; // Each remote check will be separated by 24 hours
    
    // Create a new Notification with its name, url, and body type
    $notification = new \Freezbi\Notification\SingleStreamNotification('freezbiblog');
    
    // Prepare the api for that SingleStreamNotification
    $freezbiApi->prepare($notification);
    
    $notification->Action = function() use ($freezbiApi) {
    
        // Prepare a response
        $response = new Response();
    
        $datetime = new \DateTime();
    
        if ($datetime->format('m-d') == '06-23') {
            $response->SendNotification = true;
            $response->Title = 'Birthday';
            $response->Message = 'This is Alan Turing\'s birthday';
            $response->Data = 'https://fr.wikipedia.org/wiki/Alan_Turing';
        }
    
        return $response;
    };
    
    // Your script must return the output of the execute method
    echo $freezbiApi->execute();
