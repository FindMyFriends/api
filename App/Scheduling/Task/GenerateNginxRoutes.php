<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling\Task;

use FindMyFriends\Scheduling;
use Klapuch\Configuration;

final class GenerateNginxRoutes implements Scheduling\Job {
	/** @var \Klapuch\Configuration\Source */
	private $source;

	/** @var \SplFileInfo */
	private $destination;

	public function __construct(Configuration\Source $source, \SplFileInfo $destination) {
		$this->source = $source;
		$this->destination = $destination;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function fulfill(): void {
		file_put_contents(
			$this->destination->getPathname(),
			$this->locations($this->source->read())
		);
	}

	private function locations(array $source): string {
		return '# Automatically generated, do not manually edit' . PHP_EOL . implode(
			PHP_EOL . PHP_EOL,
			array_map(
				function(string $name, array $block): string {
					$directives = implode(
						PHP_EOL,
						array_filter(
							[
								sprintf('fastcgi_param ROUTE_NAME "%s";', $name),
								$this->routerParams($block['params'] ?? []),
								'	include php.conf;',
								$this->limitExcept($block['methods']),
								$this->preflight($block['methods']),
							]
						)
					);
					return <<<CONF
{$this->location($block['params'] ?? [], $block['location'])} {
	{$directives}
}
CONF;
				},
				array_keys($source),
				$source
			)
		);
	}

	private function preflight(array $methods): string {
		if (in_array('OPTIONS', $methods, true))
			return '';
		return '	include preflight.conf;';
	}

	private function limitExcept(array $methods): string {
		$except = implode(' ', array_unique(array_merge($methods, ['OPTIONS'])));
		return <<<CONF
	limit_except {$except} {
		deny all;
	}
CONF;
	}

	private function routerParams(array $params): string {
		if ($params === [])
			return '';
		$query = implode(
			'&',
			array_map(
				static function(string $param): string {
					return sprintf('%1$s=$%1$s', $param);
				},
				array_keys($params)
			)
		);
		return <<<CONF
	fastcgi_param ROUTE_PARAM_QUERY {$query};
CONF;
	}

	private function location(array $params, string $sample): string {
		return sprintf(
			'location %s',
			str_replace(
				array_map(
					static function(string $name): string {
						return sprintf('{%s}', $name);
					},
					array_keys($params)
				),
				array_map(
					static function(string $name, string $regex): string {
						return sprintf('(?<%s>%s)', $name, $regex);
					},
					array_keys($params),
					$params
				),
				$sample
			)
		);
	}

	public function name(): string {
		return 'GenerateNginxRoutes';
	}
}
