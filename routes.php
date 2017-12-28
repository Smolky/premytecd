<?php

// Export module
$router->map ('GET', '/', function () {

    // Load the classifier page
    require __DIR__ . '/controllers/export/Export.php';
    return new Export ();
    
});


$router->map ('GET', '/[:sample]?', function ($sample='') {

    // Load the classifier page
    require __DIR__ . '/controllers/export/Export.php';
    return new Export ($sample);
    
});
