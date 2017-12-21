<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Output;

/**
 * Format for common description
 */
final class DescriptionFormat implements Output\Format {
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
					'race' => $description['general_race'],
				],
				'hair' => [
					'style' => $description['hair_style'],
					'color' => $description['hair_color'],
					'length' => $description['hair_length'],
					'highlights' => $description['hair_highlights'],
					'roots' => $description['hair_roots'],
					'nature' => $description['hair_nature'],
				],
				'face' => [
					'care' => $description['face_care'],
					'beard' => [
						'id' => $description['face_beard']['id'],
						'length' => $description['face_beard']['length'],
						'style' => $description['face_beard']['style'],
						'color' => $description['face_beard_color'],
					],
					'eyebrow' => [
						'id' => $description['face_eyebrow']['id'],
						'care' => $description['face_eyebrow']['care'],
						'color' => $description['face_eyebrow_color'],
					],
					'freckles' => $description['face_freckles'],
					'eye' => [
						'left' => [
							'id' => $description['face_left_eye']['id'],
							'color' => $description['face_left_eye_color'],
							'lenses' => $description['face_left_eye']['lenses'],
						],
						'right' => [
							'id' => $description['face_right_eye']['id'],
							'color' => $description['face_right_eye_color'],
							'lenses' => $description['face_right_eye']['lenses'],
						],
					],
					'shape' => $description['face_shape'],
					'teeth' => $description['face_tooth'],
				],
				'body' => [
					'build' => $description['body_build'],
					'skin_color' => $description['body_skin_color'],
					'weight' => $description['body']['weight'],
					'height' => $description['body']['height'],
				],
				'hands' => [
					'nails' => [
						'length' => $description['hands_nails']['length'],
						'care' => $description['hands_nails']['care'],
						'color' => $description['hand_nail_color'],
					],
					'vein_visibility' => $description['hands_vein_visibility'],
					'joint_visibility' => $description['hands_joint_visibility'],
					'care' => $description['hands_care'],
					'hair' => [
						'color' => $description['hand_hair_color'],
						'amount' => $description['hand_hair_amount'],
					],
				],
			]
		);
	}
}