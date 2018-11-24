<?php declare(strict_types=1);

header_remove();

header('Header-one: test1');
header('header-two: test2');

echo 'OK-HTTP';
