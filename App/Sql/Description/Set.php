<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Description;

use Klapuch\Sql;

final class Set implements Sql\Set {
	private $set;
	private $parameters;
	private const CONDITIONS = [
		'general_ethnic_group_id' => ['general_ethnic_group_id'],
		'general_firstname' => ['general_firstname'],
		'general_lastname' => ['general_lastname'],
		'general_sex' => ['general_sex'],
		'body_build_id' => ['body_build_id'],
		'body_weight' => ['body_weight_value', 'body_weight_unit'],
		'body_height' => ['body_height_value', 'body_height_unit'],
		'body_breast_size' => ['body_breast_size'],
		'hands_nails_color_id' => ['hands_nails_color_id'],
		'hands_nails_length' => ['hands_nails_length_value', 'hands_nails_length_unit'],
		'hands_nails_care' => ['hands_nails_care'],
		'face_freckles' => ['face_freckles'],
		'face_care' => ['face_care'],
		'beard_color_id' => ['beard_color_id'],
		'beard_length' => ['beard_length_value', 'beard_length_unit'],
		'beard_style' => ['beard_style'],
		'eyebrow_color_id' => ['eyebrow_color_id'],
		'eyebrow_care' => ['eyebrow_care'],
		'face_shape_id' => ['face_shape_id'],
		'tooth_care' => ['teeth_care'],
		'tooth_braces' => ['teeth_braces'],
		'left_eye_color_id' => ['eye_left_color_id'],
		'left_eye_lenses' => ['eye_left_lenses'],
		'right_eye_color_id' => ['eye_right_color_id'],
		'right_eye_lenses' => ['eye_right_lenses'],
		'hands_vein_visibility' => ['hands_vein_visibility'],
		'hands_joint_visibility' => ['hands_joint_visibility'],
		'hands_care' => ['hands_care'],
		'hands_hair_color_id' => ['hands_hair_color_id'],
		'hands_hair_amount' => ['hands_hair_amount'],
		'hair_color_id' => ['hair_color_id'],
		'hair_style_id' => ['hair_style_id'],
		'hair_length' => ['hair_length_value', 'hair_length_unit'],
		'hair_highlights' => ['hair_highlights'],
		'hair_roots' => ['hair_roots'],
		'hair_nature' => ['hair_nature'],
	];
	private const SET = [
		'general_ethnic_group_id' => ':general_ethnic_group_id',
		'general_firstname' => ':general_firstname',
		'general_lastname' => ':general_lastname',
		'general_sex' => ':general_sex',
		'body_build_id' => ':body_build_id',
		'body_weight' => 'ROW(:body_weight_value, :body_weight_unit)',
		'body_height' => 'ROW(:body_height_value, :body_height_unit)',
		'body_breast_size' => ':body_breast_size',
		'hands_nails_color_id' => ':hands_nails_color_id',
		'hands_nails_length' => 'ROW(:hands_nails_length_value, :hands_nails_length_unit)',
		'hands_nails_care' => ':hands_nails_care',
		'face_freckles' => ':face_freckles',
		'face_care' => ':face_care',
		'beard_color_id' => ':beard_color_id',
		'beard_length' => 'ROW(:beard_length_value, :beard_length_unit)',
		'beard_style' => ':beard_style',
		'eyebrow_color_id' => ':eyebrow_color_id',
		'eyebrow_care' => ':eyebrow_care',
		'face_shape_id' => ':face_shape_id',
		'tooth_care' => ':teeth_care',
		'tooth_braces' => ':teeth_braces',
		'left_eye_color_id' => ':eye_left_color_id',
		'left_eye_lenses' => ':eye_left_lenses',
		'right_eye_color_id' => ':eye_right_color_id',
		'right_eye_lenses' => ':eye_right_lenses',
		'hands_vein_visibility' => ':hands_vein_visibility',
		'hands_joint_visibility' => ':hands_joint_visibility',
		'hands_care' => ':hands_care',
		'hands_hair_color_id' => ':hands_hair_color_id',
		'hands_hair_amount' => ':hands_hair_amount',
		'hair_color_id' => ':hair_color_id',
		'hair_style_id' => ':hair_style_id',
		'hair_length' => 'ROW(:hair_length_value, :hair_length_unit)',
		'hair_highlights' => ':hair_highlights',
		'hair_roots' => ':hair_roots',
		'hair_nature' => ':hair_nature',
	];

	public function __construct(Sql\Clause $clause, array $columns, array $parameters) {
		$this->parameters = $parameters;
		$this->set = new Sql\AnsiSet(
			$clause,
			array_reduce(
				array_keys(self::CONDITIONS),
				function (array $values, string $column): array {
					if (array_keys_exist($this->parameters, ...self::CONDITIONS[$column])) {
						$values[$column] = self::SET[$column];
					}
					return $values;
				},
				$columns
			)
		);
	}

	public function where(string $comparison, array $parameters = []): Sql\Where {
		return $this->set->where($comparison, $this->parameters()->bind($parameters)->binds());
	}

	public function sql(): string {
		return $this->set->sql();
	}

	public function parameters(): Sql\Parameters {
		return $this->set->parameters()->bind($this->parameters);
	}
}
