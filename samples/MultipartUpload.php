<?php
require_once __DIR__ . '/Common.php';

use xing393939\PgcSDK;

$client = new PgcSDK('scdddk@163.com', 'FBBA0D39FA8F0A34814B1DDC24FCBB42');

var_dump($client->upload(__DIR__ . '/example.mp4', 1));

