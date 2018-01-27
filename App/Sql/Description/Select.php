<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Description;

use Klapuch\Storage\Clauses;

final class Select implements Clauses\Select {
	private $select;

	public function __construct(array $additionalColumns = []) {
		$this->select = new Clauses\AnsiSelect(
			array_merge(
				[
					'id',
					'general_age',
					'general_firstname',
					'general_lastname',
					'general_gender',
					'general_ethnic_group_id',
					'hair_style_id',
					'hair_color_id',
					'hair_length',
					'hair_highlights',
					'hair_roots',
					'hair_nature',
					'face_care',
					'beard_length',
					'beard_style',
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
					'body_weight',
					'body_height',
					'body_breast_size',
					'hands_nails_length',
					'hands_nails_care',
					'hands_nails_color_id',
					'hands_vein_visibility',
					'hands_joint_visibility',
					'hands_care',
					'hands_hair_color_id',
					'hands_hair_amount',
				],
				$additionalColumns
			)
		);
	}

	public function from(array $tables): Clauses\From {
		return $this->select->from($tables);
	}

	public function sql(): string {
		return $this->select->sql();
	}
}