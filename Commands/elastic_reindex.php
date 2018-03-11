<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

use FindMyFriends\Configuration;

$configuration = (new Configuration\ApplicationConfiguration())->read();
$elasticsearch = Elasticsearch\ClientBuilder::create()
	->setHosts($configuration['ELASTICSEARCH']['hosts'])
	->build();
$elasticsearch->index(['index' => 'relationships', 'type' => 'evolutions', 'body' => []]);
