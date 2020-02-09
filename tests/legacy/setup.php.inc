<?php
require_once __DIR__ . '/../../vendor/autoload.php';
$config = new Config;
$phpt = new PEAR_PHPTest(true);
// install Text_Diff for spectacular results...
