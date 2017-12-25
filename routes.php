<?php

// Home page
$router->map ('GET', '/', function () {

    // Load the classifier page
    require __DIR__ . '/controllers/export/Export.php';
    return new Export ();
    
});
