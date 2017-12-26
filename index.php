<?php

use Pimple\Container;
use Symfony\Component\Debug\Debug;


/**
 * premyTECD
 *
 * This application to create HL7 compilant files
 *
 * @author José Antonio García Díaz <joseantonio.garcia8@um.es>
 *
 * @package premyTECD
 */

// Require configuration
require 'config.php';

 
// Constants
define ('PRODUCTION', $production);
define ('BASE_URL', $base_url);
define ('VERSION', 0.1);


// Set the error level based on the stage
if (PRODUCTION) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    ini_set ('display_errors', 1);
    ini_set ('display_startup_errors', 1);
    error_reporting (E_ALL); 
}


// Require vendor
require 'vendor/autoload.php';


// Activate Debug
if ( ! PRODUCTION) {
    Debug::enable ();
}


// Dependency container
$container = new Container ();


// Store CDA
$container['cda'] = [
    'root' => $cda_hl7_root
];


// Require core libs
require ('custom/functions.php');


// Database connection
// Database info is stored at config.php
if ($user) {
    $database = new \CoreOGraphy\Database ($dsn, $user, $password);
    $container['connection'] = $database;
    $container['pdo'] = $database->connect ();
}


// Template system
Twig_Autoloader::register();


// Create and configure the template system
// @link http://whateverthing.com/blog/2015/02/17/twig-tips-configuring-cache/
$twig_configuration = array ();
if (PRODUCTION) {
    $twig_configuration = array (
        'cache' => __DIR__ . '/cache/templates',
        'auto_reload' => true
    );
}

$loader = new Twig_Loader_Filesystem ("templates");
$twig = new Twig_Environment ($loader, $twig_configuration);


// Add global variables to the template
$twig->addGlobal ('base_url', BASE_URL);
$twig->addGlobal ('version', PRODUCTION ? VERSION : rand (1, 10000));


// Store the template system as a service
$container['loader'] = $loader;
$container['templates'] = $twig;


// Configure the transform layer
$transport = Swift_SmtpTransport::newInstance ($email_server, $email_port, $email_protocol)
    ->setUsername ($email_username)
    ->setPassword ($email_password)
    ->setStreamOptions (array ('ssl' => array ('allow_self_signed' => true, 'verify_peer' => false)))
;

$container['transport'] = $transport;


// Translations
$i18n = new i18n ();
$i18n->setCachePath ('./cache/lang');
$i18n->setFilePath ('./lang/lang_{LANGUAGE}.json');
$i18n->setFallbackLang ('en');
$i18n->setPrefix ('I');
$i18n->setSectionSeperator ('_');
$i18n->init();

$container['i18n'] = $i18n;



// Attach to TWIG the global language object
$i18n_function = new Twig_SimpleFunction ('__', function ($method) {
    try {
        return call_user_func ('I' . '::' . $method); 
    } catch (Exception $e) {
        return '';
    }
    
});
$twig->addFunction ($i18n_function);



// Create the router
$router = new AltoRouter();
$router->setBasePath (ltrim (BASE_URL, '/'));

$container['router'] = $router;


session_start();


// Attach routers
require ('routes.php');


// match current request URL
$match = $router->match();


// Determine which controller will handle the current route
if ($match && is_callable ($match['target'])) {
    $controller = call_user_func_array ($match['target'], $match['params']);
    
} else {

    // No controller was found, using a 404 controller
    require __DIR__ . '/controllers/maintenance/NotFound404.php';
    $controller = new NotFound404 ();
    
}


// Handle the controller
$body = $controller->handle ();
