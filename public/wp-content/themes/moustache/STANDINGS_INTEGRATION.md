# Standings Integration Documentation

This WordPress theme includes a smart integration system for displaying standings data from an external JSON source.

## Overview

The system fetches standings data from a GitHub-hosted JSON file and displays it in a responsive table format. It includes caching, error handling, and admin management tools.

## Features

- **Smart Caching**: Data is cached for 6 hours to reduce API calls
- **Error Handling**: Graceful fallbacks when data is unavailable
- **Admin Interface**: Manage cache and view data status
- **REST API**: Access data programmatically via REST endpoints
- **Responsive Design**: Table works on all device sizes
- **Accessibility**: Proper ARIA labels and semantic HTML

## Data Source

The system fetches data from:

```
https://raw.githubusercontent.com/aprestmo/bedriftsidretten-standings-scraper/refs/heads/main/public/standings.json?token=GHSAT0AAAAAADFPKLZBELTJLJGDSCIRDCVM2C2NZUA
```

## Usage

### Display Standings Table

Include the template part in any page or post:

```php
<?php get_template_part('template-parts/standings-table'); ?>
```

### Programmatic Access

Get standings data in PHP:

```php
$standings = fetch_standings_data();
if ($standings) {
    // Process standings data
    foreach ($standings as $team) {
        echo $team['team'] . ': ' . $team['points'] . ' points';
    }
}
```

### REST API Access

Access standings data via REST API:

```javascript
// Get all standings
fetch('/wp-json/moustache/v1/standings')
  .then((response) => response.json())
  .then((data) => console.log(data))

// Filter by team name
fetch('/wp-json/moustache/v1/standings?team=Kampbart')
  .then((response) => response.json())
  .then((data) => console.log(data))
```

## Admin Management

### Access Admin Interface

1. Go to **WordPress Admin → Theme Settings → Standings**
2. View cache status and last update time
3. Clear cache manually if needed
4. Preview current standings data

### Cache Management

The system automatically caches data for 6 hours. You can:

- **Clear cache manually** via admin interface
- **Clear cache programmatically**:
  ```php
  clear_standings_cache();
  ```
- **Check last update time**:
  ```php
  $last_update = get_standings_last_update();
  ```

## Data Structure

The JSON data should have this structure:

```json
[
  {
    "position": 1,
    "team": "Team Name",
    "matches": 10,
    "wins": 8,
    "draws": 1,
    "losses": 1,
    "points": 25,
    "goalsScored": 20,
    "goalsConceded": 5
  }
]
```

## Error Handling

The system handles various error scenarios:

- **Network errors**: Logs errors and shows fallback message
- **Invalid JSON**: Validates data structure
- **Empty responses**: Checks for valid data
- **Missing fields**: Uses null coalescing operators for safety

## Customization

### Cache Duration

To change cache duration, modify the `set_transient` call in `functions.php`:

```php
// Cache for 12 hours instead of 6
set_transient($cache_key, $data, 12 * HOUR_IN_SECONDS);
```

## Troubleshooting

### Data Not Loading

1. Check if the JSON URL is accessible
2. Verify JSON structure is correct
3. Clear cache via admin interface
4. Check error logs for specific issues

### Cache Issues

1. Clear cache manually via admin interface
2. Check if transients are working on your server
3. Verify WordPress permissions

### Performance

1. Cache duration is optimized for weekly updates
2. Consider increasing cache time if data updates less frequently
3. Monitor server response times for the JSON endpoint

## Security

- All output is properly escaped
- Admin functions require proper capabilities
- REST API includes permission checks
- Nonces are used for admin actions

## Maintenance

- Monitor the GitHub repository for updates
- Check admin interface regularly for data status
- Review error logs for any issues
- Update cache duration based on data update frequency
