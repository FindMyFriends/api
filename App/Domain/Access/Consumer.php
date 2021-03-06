<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Dasuos\Mail;
use FindMyFriends\Mail\Verification;
use FindMyFriends\Task;
use Klapuch\Log;
use Klapuch\Storage;
use PhpAmqpLib;

final class Consumer extends Task\Consumer {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(
		PhpAmqpLib\Connection\AbstractConnection $rabbitMq,
		Log\Logs $logs,
		Storage\Connection $connection
	) {
		parent::__construct($rabbitMq, $logs);
		$this->connection = $connection;
	}

	/**
	 * @internal
	 * @throws \UnexpectedValueException
	 * @param array $body
	 */
	public function action(array $body): void {
		(new Mail\AssembledMail(
			'noreply@fmf.com'
		))->send(
			$body['email'],
			'Welcome and verification email',
			new Verification\Message($body['email'], $this->connection)
		);
	}

	protected function queue(): string {
		return 'content';
	}

	protected function key(): string {
		return 'verification_message';
	}
}
