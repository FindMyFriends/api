<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Dasuos\Mail\AssembledMail;
use FindMyFriends\Mail\Verification;
use FindMyFriends\Task;
use Klapuch\Log;
use Klapuch\Storage;
use PhpAmqpLib;

final class Consumer extends Task\Consumer {
	private $database;

	public function __construct(
		PhpAmqpLib\Connection\AbstractConnection $rabbitMq,
		Log\Logs $logs,
		Storage\MetaPDO $database
	) {
		parent::__construct($rabbitMq, $logs);
		$this->database = $database;
	}

	/** @internal */
	public function action(PhpAmqpLib\Message\AMQPMessage $message): void {
		$receiver = $message->getBody();
		(new AssembledMail(
			'noreply@fmf.com'
		))->send(
			$receiver,
			'Welcome and verification email',
			new Verification\Message($receiver, $this->database)
		);
	}

	protected function queue(): string {
		return 'content';
	}

	protected function key(): string {
		return 'verification_message';
	}
}
