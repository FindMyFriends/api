<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

/**
 * Callback with assurance that every potential error will be transformed to HTTP status code suited (REST) API
 */
final class ApiErrorCallback implements Callback {
	/** @var int */
	private $code;

	public function __construct(int $code) {
		$this->code = $code;
	}

	/**
	 * @param callable $action
	 * @param array $args
	 * @throws \UnexpectedValueException
	 * @return mixed
	 */
	public function invoke(callable $action, array $args = []) {
		try {
			return call_user_func_array($action, $args);
		} catch (\UnexpectedValueException $ex) {
			throw new \UnexpectedValueException(
				$ex->getMessage(),
				$this->code,
				$ex
			);
		}
	}
}
