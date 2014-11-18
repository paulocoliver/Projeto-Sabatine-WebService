<?php
ini_set('display_errors', true);
ini_set('date.timezone',  "America/Sao_Paulo");
require_once __DIR__.'/core/app.php';

// definitions
$app['debug'] = true;

$app->run();