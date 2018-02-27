<?php

$router->map ('GET', '/export', function () {
    require __DIR__ . '/controllers/export/Export.php';
    return new Export ();
});

$router->map ('GET', '/export/[:sample]?', function ($sample='') {
    require __DIR__ . '/controllers/export/Export.php';
    return new Export ($sample);
});


$router->map ('GET', '/import', function () {
    require __DIR__ . '/controllers/import/Import.php';
    return new Import ();
});

$router->map ('GET', '/rxcodes', function () {
    require __DIR__ . '/controllers/scripts/getRxNormCodes.php';
    return new getRxNormCodes ();
});

$router->map ('GET', '/generate-sample-data', function () {
    require __DIR__ . '/controllers/sample-data-generator/SampleDataGenerator.php';
    return new SampleDataGenerator ();
});

$router->map ('GET', '/generate-sample-data', function () {
    require __DIR__ . '/controllers/sample-data-generator/SampleDataGenerator.php';
    return new SampleDataGenerator ();
});

$router->map ('GET', '/generate-sample-data/[:how_many]?/[:start_date]?', function ($how_many, $start_date) {
    require __DIR__ . '/controllers/sample-data-generator/SampleDataGenerator.php';
    return new SampleDataGenerator ($how_many, $start_date);
});


