<?php
/**
 * Statistics dashboard with tabs (Spillere | Motstandere | Lagstatistikk).
 *
 * @package Moustache
 */

defined('ABSPATH') || die('Shame on you');

/**
 * Render the statistics dashboard page with tabs.
 */
function moustache_stats_dashboard_page(): void {
	if (!current_user_can('manage_options')) {
		return;
	}

	$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'players';
	$allowed_tabs = ['players', 'opponents', 'team'];
	$active_tab = in_array($active_tab, $allowed_tabs, true) ? $active_tab : 'players';

	$base_url = admin_url('admin.php?page=moustache-stats-dashboard');

	?>
	<div class="wrap">
		<h1><?php esc_html_e('Statistikk', 'moustache'); ?></h1>

		<nav class="nav-tab-wrapper" style="margin-bottom: 20px;">
			<a href="<?php echo esc_url(add_query_arg('tab', 'players', $base_url)); ?>"
				class="nav-tab <?php echo $active_tab === 'players' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e('Spillere', 'moustache'); ?>
			</a>
			<a href="<?php echo esc_url(add_query_arg('tab', 'opponents', $base_url)); ?>"
				class="nav-tab <?php echo $active_tab === 'opponents' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e('Motstandere', 'moustache'); ?>
			</a>
			<a href="<?php echo esc_url(add_query_arg('tab', 'team', $base_url)); ?>"
				class="nav-tab <?php echo $active_tab === 'team' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e('Lagstatistikk', 'moustache'); ?>
			</a>
		</nav>

		<?php
		// Shared tournament list for filter forms.
		$tournaments = get_terms([
			'taxonomy'   => 'tournament',
			'hide_empty' => false,
		]);

		switch ($active_tab) {
			case 'players':
				$table = new Moustache_Player_Stats_List_Table();
				$table->prepare_items();

				$state = $table->get_sort_state();
				$selected_tournament = $state['tournament'] ?? 0;
				$orderby = $state['orderby'] ?? 'name';
				$order = $state['order'] ?? 'asc';
				?>
				<form method="get" style="margin-bottom: 20px;">
					<input type="hidden" name="page" value="moustache-stats-dashboard">
					<input type="hidden" name="tab" value="players">
					<input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>">
					<input type="hidden" name="order" value="<?php echo esc_attr($order); ?>">
					<label for="tournament" class="screen-reader-text"><?php esc_html_e('Turnering:', 'moustache'); ?></label>
					<select name="tournament" id="tournament">
						<option value="0"><?php esc_html_e('Alle turneringer', 'moustache'); ?></option>
						<?php foreach ($tournaments as $term) : ?>
							<option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($selected_tournament, $term->term_id); ?>>
								<?php echo esc_html($term->name); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<button type="submit" class="button"><?php esc_html_e('Filtrer', 'moustache'); ?></button>
				</form>
				<?php
				$table->display();
				break;
			case 'opponents':
				$table = new Moustache_Opponent_Stats_List_Table();
				$table->prepare_items();

				$state = $table->get_sort_state();
				$selected_tournament = $state['tournament'] ?? 0;
				$orderby = $state['orderby'] ?? 'name';
				$order = $state['order'] ?? 'asc';
				?>
				<form method="get" style="margin-bottom: 20px;">
					<input type="hidden" name="page" value="moustache-stats-dashboard">
					<input type="hidden" name="tab" value="opponents">
					<input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>">
					<input type="hidden" name="order" value="<?php echo esc_attr($order); ?>">
					<label for="tournament" class="screen-reader-text"><?php esc_html_e('Turnering:', 'moustache'); ?></label>
					<select name="tournament" id="tournament">
						<option value="0"><?php esc_html_e('Alle turneringer', 'moustache'); ?></option>
						<?php foreach ($tournaments as $term) : ?>
							<option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($selected_tournament, $term->term_id); ?>>
								<?php echo esc_html($term->name); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<button type="submit" class="button"><?php esc_html_e('Filtrer', 'moustache'); ?></button>
				</form>
				<?php
				$table->display();
				break;
			case 'team':
				$table = new Moustache_Team_Stats_List_Table();
				$table->prepare_items();

				$state = $table->get_sort_state();
				$selected_tournament = $state['tournament'] ?? 0;
				$orderby = $state['orderby'] ?? 'matches';
				$order = $state['order'] ?? 'asc';
				?>
				<form method="get" style="margin-bottom: 20px;">
					<input type="hidden" name="page" value="moustache-stats-dashboard">
					<input type="hidden" name="tab" value="team">
					<input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>">
					<input type="hidden" name="order" value="<?php echo esc_attr($order); ?>">
					<label for="tournament" class="screen-reader-text"><?php esc_html_e('Turnering:', 'moustache'); ?></label>
					<select name="tournament" id="tournament">
						<option value="0"><?php esc_html_e('Alle turneringer', 'moustache'); ?></option>
						<?php foreach ($tournaments as $term) : ?>
							<option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($selected_tournament, $term->term_id); ?>>
								<?php echo esc_html($term->name); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<button type="submit" class="button"><?php esc_html_e('Filtrer', 'moustache'); ?></button>
				</form>
				<?php
				$table->display();
				break;
		}
		?>
	</div>
	<?php
}