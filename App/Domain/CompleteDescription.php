<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use Klapuch\Output;

/**
 * Format for common description
 */
final class CompleteDescription implements Output\Format {
	private $origin;
	private $description;

	public function __construct(Output\Format $origin, array $description) {
		$this->origin = $origin;
		$this->description = $description;
	}

	public function with($tag, $content = null): Output\Format {
		return $this->fill($this->origin, $this->description)->with($tag, $content);
	}

	public function serialization(): string {
		return $this->fill($this->origin, $this->description)->serialization();
	}

	public function adjusted($tag, callable $adjustment): Output\Format {
		return $this->fill($this->origin, $this->description)->adjusted($tag, $adjustment);
	}

	private function fill(Output\Format $format, array $description): Output\Format {
		return new Output\FilledFormat(
			$format,
			[
				'general' => [
					'age' => [
						'from' => $description['general_age'][0],
						'to' => $description['general_age'][1],
					],
					'firstname' => $description['general_firstname'],
					'lastname' => $description['general_lastname'],
					'gender' => $description['general_gender'],
					'ethnic_group_id' => $description['general_ethnic_group_id'],
				],
				'hair' => [
					'style_id' => $description['hair_style_id'],
					'color_id' => $description['hair_color_id'],
					'length' => $description['hair_length'],
					'highlights' => $description['hair_highlights'],
					'roots' => $description['hair_roots'],
					'nature' => $description['hair_nature'],
				],
				'eyebrow' => [
					'care' => $description['eyebrow_care'],
					'color_id' => $description['eyebrow_color_id'],
				],
				'beard' => [
					'length' => $description['beard_length'],
					'style' => $description['beard_style'],
					'color_id' => $description['beard_color_id'],
				],
				'eye' => [
					'left' => [
						'color_id' => $description['left_eye_color_id'],
						'lenses' => $description['left_eye_lenses'],
					],
					'right' => [
						'color_id' => $description['right_eye_color_id'],
						'lenses' => $description['right_eye_lenses'],
					],
				],
				'teeth' => [
					'care' => $description['tooth_care'],
					'braces' => $description['tooth_braces'],
				],
				'face' => [
					'care' => $description['face_care'],
					'freckles' => $description['face_freckles'],
					'shape_id' => $description['face_shape_id'],
				],
				'body' => [
					'build_id' => $description['body_build_id'],
					'weight' => $description['body_weight'],
					'height' => $description['body_height'],
					'breast_size' => $description['body_breast_size'],
				],
				'hands' => [
					'nails' => [
						'length' => $description['hands_nails_length'],
						'care' => $description['hands_nails_care'],
						'color_id' => $description['hands_nails_color_id'],
					],
					'vein_visibility' => $description['hands_vein_visibility'],
					'joint_visibility' => $description['hands_joint_visibility'],
					'care' => $description['hands_care'],
					'hair' => [
						'color_id' => $description['hands_hair_color_id'],
						'amount' => $description['hands_hair_amount'],
					],
				],
			]
		);
	}
}