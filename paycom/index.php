<?php

require_once 'vendor/autoload.php';
require_once 'functions.php';

use Paycom\Application;
use Paycom\MerchantConfig;

const CONFIG_FILE = 'paycom.config.php';
// load configuration
$paycomConfig = require_once CONFIG_FILE;

$merchantConfig = new MerchantConfig();
$paycomMerchant = $merchantConfig->getConfigurations();

$application = new Application($paycomMerchant);
$application->run();
