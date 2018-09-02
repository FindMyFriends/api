<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Description;

use Klapuch\Sql;

final class InsertInto implements Sql\InsertInto {
	/** @var \Klapuch\Sql\PgInsertInto */
	private $insert;

	public function __construct(string $table, array $additionalParameters = []) {
		$this->insert = new Sql\PgInsertInto(
			$table,
			$additionalParameters + [
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
			]
		);
	}

	public function returning(array $columns, array $parameters = []): Sql\Returning {
		return $this->insert->returning($columns, $parameters);
	}

	public function onConflict(array $target = []): Sql\Conflict {
		return $this->insert->onConflict($target);
	}

	public function sql(): string {
		return $this->insert->sql();
	}

	public function parameters(): Sql\Parameters {
		return $this->insert->parameters();
	}
}
