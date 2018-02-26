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

	private function query(array $demand): array {
		$bool = (new class($demand, $this->database) {
			private $demand;
			private $database;

			public function __construct(array $demand, \PDO $database) {
				$this->demand = $demand;
				$this->database = $database;
			}

			private function should(array $demand): array {
				$should = [
					['term' => ['general.ethnic_group_id' => $demand['ethnic_group_id']]],
					['match' => ['general.firstname^2' => $demand['firstname']]],
					['match' => ['general.lastname^3' => $demand['lastname']]],
					['range' => ['general.birth_year' => ['gte' => $demand['birth_year'][0], 'lte' => $demand['birth_year'][1]]]],
					['term' => ['body.build_id' => $demand['build_id']]],
					['term' => ['hair.style_id' => $demand['style_id']]],
					[
						'bool' => [
							'should' => [
								['term' => ['hair.color_id^2' => $demand['hair_color_id']]],
								['terms' => ['hair.color_id' => $demand['hair_similar_colors_id']]],
							],
						],
					],
					['term' => ['hair.length' => $demand['hair_length']]],
					['term' => ['hair.highlights' => $demand['hair_highlights']]],
					['term' => ['hair.roots' => $demand['hair_roots']]],
					['term' => ['hair.nature' => $demand['hair_nature']]],
					['term' => ['face.freckles' => $demand['face_freckles']]],
					['range' => ['face.care' => ['gte' => $demand['face_care'][0], 'lte' => $demand['face_care'][1]]]],
					['term' => ['face.shape_id' => $demand['face_shape_id']]],
				];
				$should[] = $demand['breast_size'] ? ['range' => ['body.breast_size' => ['gte' => $demand['breast_size'][0], 'lte' => $demand['breast_size'][1]]]] : [];
				return $should;
			}

			private function must(array $demand): array {
				return [
					['term' => ['general.gender' => $demand['gender']]],
				];
			}

			private function mustNot(array $demand): array {
				return [
					[
						'term' => [
							'seeker_id' => (new Storage\NativeQuery(
								$this->database,
								'SELECT seeker_id FROM demands WHERE id = ?',
								[$demand['id']]
							))->field(),
						],
					],
				];
			}

			public function bool(): array {
				return [
					'must_not' => $this->mustNot($this->demand),
					'must' => $this->must($this->demand),
					'should' => array_filter($this->should($this->demand)),
				];
			}
		})->bool();
		return ['query' => ['bool' => $bool]];
	}

	public function find(): void {
		$demand = (new Storage\TypedQuery(
			$this->database,
			(new Storage\Clauses\AnsiSelect(
				[
					'id',
					'(general).gender',
					'(general).ethnic_group_id',
					'(general).firstname',
					'(general).lastname',
					'(general).birth_year',
					'(body).build_id',
					'(body).breast_size',
					'(hair).style_id',
					'(hair).color_id AS hair_color_id',
					'(hair).similar_colors_id AS hair_similar_colors_id',
					'(hair).length AS hair_length',
					'(hair).highlights AS hair_highlights',
					'(hair).roots AS hair_roots',
					'(hair).nature AS hair_nature',
					'(face).freckles AS face_freckles',
					'(face).care AS face_care',
					'(face).shape_id AS face_shape_id',
					'(hand).nail AS hand_nail',
					'(hand).hair AS hand_hair',
					'(hand).care AS hand_care',
					'(hand).vein_visibility AS hand_vein_visibility',
					'(hand).joint_visibility AS hand_joint_visibility',
					'(beard).color_id AS beard_color_id',
					'(beard).similar_colors_id AS beard_similar_colors_id',
					'(beard).length AS beard_length',
					'(eyebrow).color_id AS eyebrow_color_id',
					'(eyebrow).similar_colors_id AS eyebrow_similar_colors_id',
					'(tooth).care AS tooth_care',
					'(tooth).braces AS tooth_braces',
					'heterochromic_eyes',
					'(left_eye).color_id AS left_eye_color_id',
					'(left_eye).similar_colors_id left_eye_similar_colors_id',
					'(left_eye).lenses left_eye_lenses',
					'(right_eye).color_id AS right_eye_color_id',
					'(right_eye).similar_colors_id AS right_eye_similar_colors_id',
					'(right_eye).lenses AS right_eye_lenses',
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
				'body' => $this->query($demand),
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