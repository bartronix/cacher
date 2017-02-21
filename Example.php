<?php
require_once("Cacher.php");
$cacher = new Bartronix\Cacher("data");
$testdata = $cacher->getEntry("testdata");
if(!$testdata) {
    $testdata = array("a" => "a", "b" => "b");
    $cacher->addEntry("testdata", $testdata, 20000);	
}
$testdata = $cacher->getEntry("testdata");
var_dump($testdata);
