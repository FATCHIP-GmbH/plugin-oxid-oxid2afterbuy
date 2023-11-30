<?php

require_once 'fco2abootstrap.php';
require_once '../core/fco2aorderimport_debug.php';

$sTestOrderId = false;
if (isset($argv) && count($argv) == 2 && is_numeric($argv[1])) {
    $sTestOrderId = $argv[1];
}

/**
 * Start the job
 */
$oJob = oxNew('fco2aorderimport_debug');
if ($sTestOrderId !== false) {
    $oJob->setTestOrderId($sTestOrderId);
    echo "Execute Debug OrderImport for Afterbuy OrderID: ".$sTestOrderId.PHP_EOL;
}
$oJob->execute();
