<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Description;

use Klapuch\Sql;

final class Set implements Sql\Set {
	private const CONDITIONS = [
		'general_ethnic_group_id' => ['general_ethnic_group_id'],
		'general_firstname' => ['general_firstname'],
		'general_lastname' => ['general_lastname'],
		'general_sex' => ['general_sex'],
		'body_build_id' => ['body_build_id'],
		'body_breast_size' => ['body_breast_size'],
		'hands_nails_color_id' => ['hands_nails_color_id'],
		'hands_nails_length_id' => ['hands_nails_length_id'],
		'face_freckles' => ['face_freckles'],
		'face_care' => ['face_care'],
		'beard_color_id' => ['beard_color_id'],
		'beard_length_id' => ['beard_length_id'],
		'beard_style_id' => ['beard_style_id'],
		'eyebrow_color_id' => ['eyebrow_color_id'],
		'eyebrow_care' => ['eyebrow_care'],
		'face_shape_id' => ['face_shape_id'],
		'tooth_care' => ['teeth_care'],
		'tooth_braces' => ['teeth_braces'],
		'left_eye_color_id' => ['eye_left_color_id'],
		'left_eye_lenses' => ['eye_left_lenses'],
		'right_eye_color_id' => ['eye_right_color_id'],
		'right_eye_lenses' => ['eye_right_lenses'],
		'hands_visible_veins' => ['hands_visible_veins'],
		'hands_care' => ['hands_care'],
		'hair_color_id' => ['hair_color_id'],
		'hair_style_id' => ['hair_style_id'],
		'hair_length_id' => ['hair_length_id'],
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
		'body_breast_size' => ':body_breast_size',
		'hands_nails_color_id' => ':hands_nails_color_id',
		'hands_nails_length_id' => ':hands_nails_length_id',
		'face_freckles' => ':face_freckles',
		'face_care' => ':face_care',
		'beard_color_id' => ':beard_color_id',
		'beard_length_id' => ':beard_length_id',
		'beard_style_id' => ':beard_style_id',
		'eyebrow_color_id' => ':eyebrow_color_id',
		'eyebrow_care' => ':eyebrow_care',
		'face_shape_id' => ':face_shape_id',
		'tooth_care' => ':teeth_care',
		'tooth_braces' => ':teeth_braces',
		'left_eye_color_id' => ':eye_left_color_id',
		'left_eye_lenses' => ':eye_left_lenses',
		'right_eye_color_id' => ':eye_right_color_id',
		'right_eye_lenses' => ':eye_right_lenses',
		'hands_visible_veins' => ':hands_visible_veins',
		'hands_care' => ':hands_care',
		'hair_color_id' => ':hair_color_id',
		'hair_style_id' => ':hair_style_id',
		'hair_length_id' => ':hair_length_id',
		'hair_highlights' => ':hair_highlights',
		'hair_roots' => ':hair_roots',
		'hair_nature' => ':hair_nature',
	];

	/** @var \Klapuch\Sql\AnsiSet */
	private $set;

	/** @var mixed[] */
	private $parameters;

	public function __construct(Sql\Statement $statement, array $columns, array $parameters) {
		$this->parameters = $parameters;
		$this->set = new Sql\AnsiSet(
			$statement,
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
