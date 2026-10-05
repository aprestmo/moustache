/**
 * Pass fixture context that other pickers depend on into their AJAX queries:
 * the current "Tilstede" selection, and the tournament chosen for this match.
 */
(function () {
	if (typeof acf === "undefined") {
		return;
	}

	var PRESENT_KEY = "field_5611b94e32f1c";
	var PLAYER_FIELD_KEYS = [
		"field_moustache_goal_scorer",
		"field_moustache_goal_assist",
		"field_moustache_card_player",
	];
	var TEAM_FIELD_KEYS = ["field_5539868a27be4", "field_553986be27be5"];

	function getPresentIds() {
		var field = acf.getField(PRESENT_KEY);
		if (!field) {
			return [];
		}

		var value = field.val();
		if (!value) {
			return [];
		}

		return Array.isArray(value) ? value : [value];
	}

	/**
	 * Term IDs ticked in the "Turneringer" meta box, unsaved ones included.
	 *
	 * There is no ACF field for the tournament, so the native WP meta box is the
	 * only source. Its "Mest brukte" tab repeats a checkbox for popular terms,
	 * hence the de-dupe.
	 */
	function getSelectedTournamentIds() {
		var inputs = document.querySelectorAll(
			'#taxonomy-tournament input[type="checkbox"]:checked, #taxonomy-tournament input[type="radio"]:checked'
		);
		var ids = [];

		Array.prototype.forEach.call(inputs, function (input) {
			var id = parseInt(input.value, 10);

			if (id > 0 && ids.indexOf(id) === -1) {
				ids.push(id);
			}
		});

		return ids;
	}

	acf.addFilter("select2_ajax_data", function (data, args, $input, field) {
		if (!field) {
			return data;
		}

		var key = field.get("key");

		if (PLAYER_FIELD_KEYS.indexOf(key) !== -1) {
			data.moustache_present_ids = getPresentIds();
		}

		if (TEAM_FIELD_KEYS.indexOf(key) !== -1) {
			data.moustache_tournament_ids = getSelectedTournamentIds().join(",");
		}

		return data;
	});
})();
