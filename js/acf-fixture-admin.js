/**
 * Pass current "Tilstede" selection into goal/assist/card AJAX queries.
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

	acf.addFilter("select2_ajax_data", function (data, args, $input, field) {
		if (PLAYER_FIELD_KEYS.indexOf(field.get("key")) === -1) {
			return data;
		}

		data.moustache_present_ids = getPresentIds();
		return data;
	});
})();
