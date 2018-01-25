<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Description;

use Klapuch\Storage\Clauses;
use Klapuch\Storage\Clauses\Returning;

final class InsertInto implements Clauses\InsertInto {
	private $insert;

	public function __construct(string $table, array $additionalParameters = []) {
		$this->insert = new Clauses\AnsiInsertInto(
			$table,
			$additionalParameters + [
				'general_ethnic_group_id' => ':general_ethnic_group_id',
				'general_firstname' => ':general_firstname',
				'general_lastname' => ':general_lastname',
				'general_gender' => ':general_gender',
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
			]
		);
	}

	public function returning(array $columns): Returning {
		return $this->insert->returning($columns);
	}


	public function sql(): string {
		return $this->insert->sql();
	}
}