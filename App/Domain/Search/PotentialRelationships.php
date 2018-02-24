<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Elasticsearch;
use Klapuch\Storage;

/**
 * All future potential relationships
 */
final class PotentialRelationships implements Relationships {
	private const INDEX = 'relationships',
		TYPE = 'evolutions';
	private $demand;
	private $elastic;
	private $database;

	public function __construct(int $demand, Elasticsearch\Client $elastic, Storage\MetaPDO $database) {
		$this->demand = $demand;
		$this->elastic = $elastic;
		$this->database = $database;
	}

	public function find(): void {
		$demand = (new Storage\TypedQuery(
			$this->database,
			(new Storage\Clauses\AnsiSelect(
				[
					'(general).gender',
					'(general).ethnic_group_id',
					'(general).firstname',
					'(general).lastname',
					'(general).birth_year',
				]
			))->from(['elasticsearch_demands'])
				->where('id = ?')
				->sql(),
			[$this->demand]
		))->row();
		$response = $this->elastic->search(
			[
				'index' => self::INDEX,
				'type' => self::TYPE,
				'body' => [
					'query' => [
						'bool' => [
							'must_not' => [
								[
									'term' => [
										'seeker_id' => (new Storage\NativeQuery(
											$this->database,
											'SELECT seeker_id FROM demands WHERE id = ?',
											[$this->demand]
										))->field(),
									],
								],
							],
							'must' => [
								['term' => ['general.gender' => $demand['gender']]],
							],
							'should' => [
								['term' => ['general.ethnic_group_id' => $demand['ethnic_group_id']]],
								['term' => ['general.firstname^2' => $demand['firstname']]],
								['term' => ['general.lastname^3' => $demand['lastname']]],
								['range' => ['general.birth_year' => ['gte' => $demand['birth_year'][0], 'lte' => $demand['birth_year'][1]]]],
							],
						],
					],
				],
			]
		);
		if (!$response['hits']['total']) {
			return;
		}
		$evolutions = array_column(array_column($response['hits']['hits'], '_source'), 'id');
		$demands = array_fill(0, count($evolutions), $this->demand);
		$scores = array_column($response['hits']['hits'], '_score');
		(new Storage\NativeQuery(
			$this->database,
			(new Storage\Clauses\AnsiMultiInsertInto(
				'relationships',
				[
					'evolution_id' => array_fill(0, count($evolutions), '?'),
					'demand_id' => array_fill(0, count($demands), '?'),
					'score' => array_fill(0, count($scores), '?'),
				]
			))->sql(),
			array_merge(...array_map(null, $evolutions, $demands, $scores))
		))->execute();
	}
}