<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Description;

use Klapuch\Sql;

final class Select implements Sql\Select {
	/** @var \Klapuch\Sql\AnsiSelect */
	private $select;

	public function __construct(array $additionalColumns = []) {
		$this->select = new Sql\AnsiSelect(
			array_merge(
				[
					'id',
					'general_age',
					'general_firstname',
					'general_lastname',
					'general_sex',
					'general_ethnic_group_id',
					'hair_style_id',
					'hair_color_id',
					'hair_length_id',
					'hair_highlights',
					'hair_roots',
					'hair_nature',
					'face_care',
					'beard_length_id',
					'beard_style_id',
					'beard_color_id',
					'eyebrow_care',
					'eyebrow_color_id',
					'face_freckles',
					'left_eye_color_id',
					'left_eye_lenses',
					'right_eye_color_id',
					'right_eye_lenses',
					'face_shape_id',
					'tooth_care',
					'tooth_braces',
					'body_build_id',
					'body_breast_size',
					'hands_nails_length_id',
					'hands_nails_color_id',
					'hands_visible_veins',
					'hands_care',
				],
				$additionalColumns
			)
		);
	}

	public function from(array $tables, array $parameters = []): Sql\From {
		return $this->select->from($tables, $parameters);
	}

	public function sql(): string {
		return $this->select->sql();
	}

	public function parameters(): Sql\Parameters {
		return $this->select->parameters();
	}
}
