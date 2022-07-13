<?php declare(strict_types=1);

header_remove();

$parameter = $_GET['parameter'];
assert(is_string($parameter));

echo $parameter;
