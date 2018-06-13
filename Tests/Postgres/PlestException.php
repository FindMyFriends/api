<?php
declare(strict_types = 1);

namespace FindMyFriends\Postgres;

final class PlestException extends \PDOException
{
	public function __construct(\PDOException $e, \SplFileInfo $file, ?\Throwable $previous = null)
	{
		parent::__construct($this->format($e->getMessage(), $file), 0, $previous ?: $e);
	}

	private function format(string $message, \SplFileInfo $file): string
	{
		preg_match('~ERROR:\s+(?<message>.+)~', $message, $error);
		preg_match('~SQL statement\s+"(?<message>.+)"~s', $message, $statement);
		preg_match('~PL/pgSQL function\s+(?<name>tests\.\w+\(\))~', $message, $function);
		if (isset($error['message'], $statement['message'], $function['name'])) {
			return sprintf(
				"\r\n\r\nMESSAGE: %s \r\nSTATEMENT: %s \r\nFUNCTION: %s\r\nFILE: %s\r\n\r\n",
				$error['message'],
				$statement['message'],
				$function['name'],
				$file->getPathname()
			);
		}
		return sprintf("%s\r\n\r\nFILE: %s\r\n\r\n", $message, $file->getPathname());
	}
}
