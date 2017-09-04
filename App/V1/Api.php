<?php
declare(strict_types = 1);
namespace FindMyFriends\V1;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Storage;
use Klapuch\Uri;
use Predis;

abstract class Api implements Application\View {
	/** @var mixed[] */
	protected $configuration;

	/** @var \Klapuch\Uri\Uri */
	protected $url;

	/** @var \Klapuch\Log\Logs */
	protected $logs;

	/** @var \Klapuch\Access\User */
	protected $user;

	/** @var \PDO */
	protected $database;

	/** @var \Predis\ClientInterface */
	protected $redis;

	public function __construct(
		Uri\Uri $url,
		Log\Logs $logs,
		Ini\Source $configuration
	) {
		$this->url = $url;
		$this->logs = $logs;
		$this->configuration = $configuration->read();
		$this->database = new Storage\SafePDO(
			$this->configuration['DATABASE']['dsn'],
			$this->configuration['DATABASE']['user'],
			$this->configuration['DATABASE']['password']
		);
		$this->redis = new Predis\Client($this->configuration['REDIS']['uri']);
		$this->user = (new Access\ApiEntrance(
			$this->database
		))->enter((new Application\PlainRequest())->headers());
	}
}