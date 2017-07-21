<?php
require_once __DIR__ . '/Common.php';

use xing393939\PgcSDK;

$client = new PgcSDK('scdddk@163.com', 'FBBA0D39FA8F0A34814B1DDC24FCBB42');

var_dump($client->upload('C:\\Esko\\bg_data_system_v010\\3.mp4', '', 'title', 'summary'));

