<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql\Description;

use FindMyFriends\Sql;

final class Mapping implements Sql\Mapping {
	private const MAP = [
		'beard_color_id' => 'beard.color_id',
		'beard_length' => 'beard.length',
		'beard_style' => 'beard.style',
		'body_breast_size' => 'body.breast_size',
		'body_build_id' => 'body.build_id',
		'body_height' => 'body.height',
		'body_weight' => 'body.weight',
		'eyebrow_care' => 'eyebrow.care',
		'eyebrow_color_id' => 'eyebrow.color_id',
		'face_care' => 'face.care',
		'face_freckles' => 'face.freckles',
		'face_shape_id' => 'face.shape_id',
		'general_age' => 'general.age',
		'general_ethnic_group_id' => 'general.ethnic_group_id',
		'general_firstname' => 'general.firstname',
		'general_lastname' => 'general.lastname',
		'general_sex' => 'general.sex',
		'hair_color_id' => 'hair.color_id',
		'hair_highlights' => 'hair.highlights',
		'hair_length' => 'hair.length',
		'hair_nature' => 'hair.nature',
		'hair_roots' => 'hair.roots',
		'hair_style_id' => 'hair.style_id',
		'hands_care' => 'hands.care',
		'hands_hair_amount' => 'hands.hair.amount',
		'hands_joint_visibility' => 'hands.joint_visibility',
		'hands_nails_care' => 'hands.nails.care',
		'hands_nails_color_id' => 'hands.nails.color_id',
		'hands_nails_length' => 'hands.nails.length',
		'hands_vein_visibility' => 'hands.vein_visibility',
		'left_eye_color_id' => 'eye.left.color_id',
		'left_eye_lenses' => 'eye.left.lenses',
		'right_eye_color_id' => 'eye.right.color_id',
		'right_eye_lenses' => 'eye.right.lenses',
		'tooth_braces' => 'teeth.braces',
		'tooth_care' => 'teeth.care',
	];

	/** @var \FindMyFriends\Sql\Mapping */
	private $mapping;

	public function __construct() {
		$this->mapping = new Sql\KeyValueMapping(self::MAP);
	}

	public function application(array $database): array {
		return $this->mapping->application($database);
	}

	public function database(array $application): array {
		return $this->mapping->database($application);
	}
}
