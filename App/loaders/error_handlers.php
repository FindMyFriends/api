<?php
declare(strict_types = 1);

register_shutdown_function(static function (): void {
	$error = error_get_last();
	if (in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_RECOVERABLE_ERROR, E_USER_ERROR], true))
		throw new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
});

set_error_handler(static function ($severity, $message, $file, $line): bool {
	if (($severity & error_reporting()) !== $severity)
		return false;
	throw new \ErrorException($message, 0, $severity, $file, $line);
});
