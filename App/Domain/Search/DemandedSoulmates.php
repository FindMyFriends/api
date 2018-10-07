<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Elasticsearch;
use FindMyFriends;
use Klapuch\Dataset;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Soulmates suited for the particular demand
 */
final class DemandedSoulmates implements Soulmates {
	/** @var int */
	private $demand;

	/** @var \FindMyFriends\Elasticsearch\RelationshipEvolutions */
	private $elasticsearch;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(
		int $demand,
		Elasticsearch\Client $elasticsearch,
		Storage\Connection $connection
	) {
		$this->demand = $demand;
		$this->elasticsearch = new FindMyFriends\Elasticsearch\RelationshipEvolutions($elasticsearch);
		$this->connection = $connection;
	}

	public function matches(Dataset\Selection $selection): \Iterator {
		$demand = (new Storage\BuiltQuery(
			$this->connection,
			(new Sql\AnsiSelect(
				[
					'id',
					'(general).sex',
					'(general).ethnic_group_id',
					'(general).firstname',
					'(general).lastname',
					'(general).birth_year_range',
					'(body).build_id',
					'(body).breast_size',
					'(hair).style_id',
					'(hair).color_id AS hair_color_id',
					'(hair).similar_colors_id AS hair_similar_colors_id',
					'(hair).length_id AS hair_length_id',
					'(hair).highlights AS hair_highlights',
					'(hair).roots AS hair_roots',
					'(hair).nature AS hair_nature',
					'(face).freckles AS face_freckles',
					'(face).care AS face_care',
					'(face).shape_id AS face_shape_id',
					'(hand).nail AS hand_nail',
					'(hand).care AS hand_care',
					'(hand).visible_veins AS hand_visible_veins',
					'(beard).color_id AS beard_color_id',
					'(beard).similar_colors_id AS beard_similar_colors_id',
					'(beard).length_id AS beard_length_id',
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
			))
				->from(['elasticsearch_demands'])
				->where('id = ?', [$this->demand])
		))->row();
		$response = $this->elasticsearch->search(['body' => $this->query($demand)]);
		if (!$response['hits']['total']) {
			return new \ArrayIterator();
		}
		$evolutions = array_column(array_column($response['hits']['hits'], '_source'), 'id');
		$demands = array_fill(0, count($evolutions), $this->demand);
		$scores = array_column($response['hits']['hits'], '_score');
		(new Storage\NativeQuery(
			$this->connection,
			(new Sql\PgMultiInsertInto(
				'soulmates',
				[
					'evolution_id' => array_fill(0, count($evolutions), '?'),
					'demand_id' => array_fill(0, count($demands), '?'),
					'score' => array_fill(0, count($scores), '?'),
				]
			))->onConflict(['evolution_id', 'demand_id'])->doUpdate(['version' => 'EXCLUDED.version + 1'])->sql(),
			array_merge(...array_map(null, $evolutions, $demands, $scores))
		))->execute();
		return new \ArrayIterator();
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\BuiltQuery(
			$this->connection,
			new Dataset\SelectiveStatement(
				(new Sql\AnsiSelect(['count(*)']))
					->from(['suited_soulmates'])
					->where('demand_id = ?', [$this->demand]),
				$selection
			)
		))->field();
	}

	private function query(array $demand): array {
		$bool = (new class($demand, $this->connection) {
			/** @var mixed[] */
			private $demand;

			/** @var \Klapuch\Storage\Connection */
			private $connection;

			public function __construct(array $demand, Storage\Connection $connection) {
				$this->demand = $demand;
				$this->connection = $connection;
			}

			private function should(array $demand): array {
				$should = [
					['term' => ['general.ethnic_group_id' => $demand['ethnic_group_id']]],
					$this->withRange('general.birth_year_range', $demand['birth_year_range']),
				];
				$should[] = $demand['firstname'] !== null ? ['match' => ['general.firstname' => ['query' => $demand['firstname'], 'boost' => 2.0]]] : [];
				$should[] = $demand['lastname'] !== null ? ['match' => ['general.lastname' => ['query' => $demand['lastname'], 'boost' => 3.0]]] : [];
				$should[] = $demand['build_id'] !== null ? ['term' => ['body.build_id' => $demand['build_id']]] : [];
				$should[] = $demand['style_id'] !== null ? ['term' => ['hair.style_id' => $demand['style_id']]] : [];
				$should[] = $demand['hair_color_id'] !== null
					? $this->withSimilarColor('hair.color_id', $demand['hair_color_id'], $demand['hair_similar_colors_id'])
					: [];
				$should[] = $demand['hair_length_id'] !== null ? ['term' => ['hair.length_id' => $demand['hair_length_id']]] : [];
				$should[] = $demand['hair_highlights'] !== null ? ['term' => ['hair.highlights' => $demand['hair_highlights']]] : [];
				$should[] = $demand['hair_roots'] !== null ? ['term' => ['hair.roots' => $demand['hair_roots']]] : [];
				$should[] = $demand['hair_nature'] !== null ? ['term' => ['hair.nature' => $demand['hair_nature']]] : [];
				$should[] = $demand['face_freckles'] !== null ? ['term' => ['face.freckles' => $demand['face_freckles']]] : [];
				$should[] = $demand['face_care'] !== null ? $this->withRange('face.care', $demand['face_care']) : [];
				$should[] = $demand['face_shape_id'] !== null ? ['term' => ['face.shape_id' => $demand['face_shape_id']]] : [];
				$should[] = $demand['hand_nail']['color_id'] !== null
					? $this->withSimilarColor('hand.nail.color_id', $demand['hand_nail']['color_id'], $demand['hand_nail']['similar_colors_id'])
					: [];
				//              $should[] = $demand['hand_nail']['care'] !== null ? $this->withRange('hand.nail.care', $demand['hand_nail']['care']) : [];
				$should[] = $demand['hand_care'] !== null ? $this->withRange('hand.care', $demand['hand_care']) : [];
				$should[] = $demand['hand_visible_veins'] ?? [];
				$should[] = $demand['beard_color_id'] !== null
					? $this->withSimilarColor('beard.color_id', $demand['beard_color_id'], $demand['beard_similar_colors_id'])
					: [];
				$should[] = $demand['eyebrow_color_id'] !== null
					? $this->withSimilarColor('eyebrow.color_id', $demand['eyebrow_color_id'], $demand['eyebrow_similar_colors_id'])
					: [];
				$should[] = $demand['tooth_care'] !== null ? $this->withRange('tooth.care', $demand['tooth_care']) : [];
				$should[] = $demand['tooth_braces'] !== null ? ['term' => ['tooth.braces' => $demand['tooth_braces']]] : [];
				$should[] = $demand['left_eye_color_id'] !== null
					? $this->withSimilarColor('left_eye.color_id', $demand['left_eye_color_id'], $demand['left_eye_similar_colors_id'])
					: [];
				$should[] = $demand['left_eye_lenses'] !== null ? ['term' => ['left_eye.lenses' => $demand['left_eye_lenses']]] : [];
				$should[] = $demand['right_eye_color_id'] !== null
					? $this->withSimilarColor('right_eye.color_id', $demand['right_eye_color_id'], $demand['right_eye_similar_colors_id'])
					: [];
				$should[] = $demand['right_eye_lenses'] !== null ? ['term' => ['right_eye.lenses' => $demand['right_eye_lenses']]] : [];
				$should[] = $demand['breast_size'] !== null ? $this->withRange('body.breast_size', $demand['breast_size']) : [];
				return $should;
			}

			private function withRange(string $field, array $range): array {
				return ['range' => [$field => ['gte' => $range[0], 'lte' => $range[1]]]];
			}

			private function heterochromic(array $demand): bool {
				return $demand['left_eye_color_id'] !== $demand['right_eye_color_id'];
			}

			private function withSimilarColor(string $field, int $base, array $similarities): array {
				return [
					'bool' => [
						'should' => [
							['term' => [$field => ['value' => $base, 'boost' => 2.0]]],
							['terms' => [$field => $similarities]],
						],
					],
				];
			}

			private function must(array $demand): array {
				$terms = [
					['term' => ['general.sex' => $demand['sex']]],
				];
				if ($this->heterochromic($demand)) {
					$terms[] = [
						'script' => [
							'script' => [
								'source' => "doc['left_eye.color_id'].value != doc['right_eye.color_id'].value",
							],
						],
					];
				}
				return $terms;
			}

			private function mustNot(array $demand): array {
				return [
					[
						'term' => [
							'seeker_id' => (new Storage\NativeQuery(
								$this->connection,
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
					'should' => array_values(array_filter($this->should($this->demand))),
				];
			}
		})->bool();
		return ['query' => ['bool' => $bool]];
	}
}
