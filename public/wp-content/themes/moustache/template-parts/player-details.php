<div>
	<?php if ($player_data['image']) : ?>
		<figure>
			<img src="<?php echo $player_data['image']['sizes']['medium']; ?>"
				alt="<?php printf(esc_attr__('Portrait of %s', 'moustache'), get_the_title()); ?>">
		</figure>
	<?php endif; ?>

	<div>
		<?php foreach (['shirt_number', 'shirt_name', 'dob'] as $field) : ?>
			<?php if ($player_data[$field]) : ?>
				<dl>
					<dt><?php esc_html_e(ucwords(str_replace('_', ' ', $field)), 'moustache'); ?>:</dt>
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
					<dt><?php esc_html_e(ucwords(str_replace('_', ' ', $field)), 'moustache'); ?>:</dt>
					<dd><?php echo esc_html($player_data[$field]); ?></dd>
				</dl>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>