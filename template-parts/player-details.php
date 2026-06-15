<div>
	<?php if ($player_data['image']) : ?>
		<figure>
			<img src="<?php echo $player_data['image']['sizes']['medium']; ?>"
				alt="<?php printf(esc_attr__('Portrait of %s', 'moustache'), get_the_title()); ?>">
		</figure>
	<?php endif; ?>

	<div>
		<?php
		$field_labels = [
			'shirt_number'  => __('Shirt Number', 'moustache'),
			'shirt_name'    => __('Shirt Name', 'moustache'),
			'dob'           => __('Date of Birth', 'moustache'),
			'former_clubs'  => __('Former Clubs', 'moustache'),
			'best_memory'   => __('Best Memory', 'moustache'),
		];
		foreach (['shirt_number', 'shirt_name', 'dob'] as $field) : ?>
			<?php if ($player_data[$field]) : ?>
				<dl>
					<dt><?php echo esc_html($field_labels[$field]); ?>:</dt>
					<dd><?php echo esc_html($player_data[$field]); ?></dd>
				</dl>
			<?php endif; ?>
		<?php endforeach; ?>

		<?php if ($player_data['position']) : ?>
			<dl>
				<dt><?php esc_html_e('Position', 'moustache'); ?>:</dt>
				<dd>
					<?php
					$positions = array_map(function ($pos) use ($player_data) {
						return $player_data['position_labels']['choices'][$pos];
					}, $player_data['position']);
					echo esc_html(implode('/', $positions));
					?>
				</dd>
			</dl>
		<?php endif; ?>

		<?php foreach (['former_clubs', 'best_memory'] as $field) : ?>
			<?php if ($player_data[$field]) : ?>
				<dl>
					<dt><?php echo esc_html($field_labels[$field]); ?>:</dt>
					<dd><?php echo esc_html($player_data[$field]); ?></dd>
				</dl>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>