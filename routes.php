<?php

$router->map ('GET', '/export', function () {
    require __DIR__ . '/controllers/export/Export.php';
    return new Export ();
});

$router->map ('GET', '/import', function () {
    require __DIR__ . '/controllers/import/Import.php';
    return new Import ();
});

$router->map ('GET', '/rxcodes', function () {
    require __DIR__ . '/controllers/scripts/getRxNormCodes.php';
    return new getRxNormCodes ();
    
});

$router->map ('GET', '/[:sample]?', function ($sample='') {
    require __DIR__ . '/controllers/export/Export.php';
    return new Export ($sample);
});

