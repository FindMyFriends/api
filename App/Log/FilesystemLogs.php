<?php
declare(strict_types = 1);

namespace FindMyFriends\Log;

use Klapuch\Log;

/**
 * Logs stored on the filesystem
 */
final class FilesystemLogs implements Log\Logs {
	/** @var \SplFileInfo */
	private $location;

	public function __construct(\SplFileInfo $location) {
		$this->location = $location;
	}

	public function put(\Throwable $exception, Log\Environment $environment): void {
		file_put_contents(
			$this->location->getPathname(),
			$this->format($exception, $environment, new \DateTimeImmutable()),
			LOCK_EX | FILE_APPEND
		);
	}

	private function format(\Throwable $exception, Log\Environment $environment, \DateTimeInterface $now): string {
		$log = (new Log\CompleteLog(
			new Log\ExceptionLog($exception),
			new Log\ExceptionsLog($exception)
		))->content();
		return <<<TXT
[{$now->format('Y-m-d H:i')}] {$log['type']}({$log['message']}, {$log['code']})
{$log['file']}:{$log['line']}

TRACE:
{$log['trace']}

PREVIOUS:
{$this->dump($log['previous'])}

POST:
{$this->dump($environment->post())}

GET:
{$this->dump($environment->get())}

SESSION:
{$this->dump($environment->session())}

COOKIE:
{$this->dump($environment->cookie())}

INPUT:
{$environment->input()}

SERVER:
{$this->dump($environment->server())}
TXT;
	}

	private function dump(array $expression): string {
		return var_export($expression, true);
	}
}
