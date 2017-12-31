<?php

$router->map ('GET', '/', function () {
    require __DIR__ . '/controllers/export/Export.php';
    return new Export ();
    
});

$router->map ('GET', '/rxcodes', function () {
    require __DIR__ . '/controllers/scripts/getRxNormCodes.php';
    return new getRxNormCodes ();
    
});

$router->map ('GET', '/[:sample]?', function ($sample='') {
    require __DIR__ . '/controllers/export/Export.php';
    return new Export ($sample);
});

