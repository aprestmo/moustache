# Standings Table Integration

This integration fetches and displays standings data from an external JSON source on a specific tournament page.

## Files Modified/Created

### New Files

- `template-parts/standings-table.php` - Template part that fetches and displays the standings table

### Modified Files

- `taxonomy-tournament.php` - Updated to conditionally include the standings table for specific tournaments
- `src/css/05-objects/_tables.css` - Added styling for the standings table

## Scope

The standings table is **only displayed** on the tournament page with:

- Slug: `uteserie-2025`
- Term ID: `311`

For all other tournament pages, the original `tournament_content` field is displayed instead.

## Features

1. **Conditional Display**: Only shows on the specified tournament page
2. **Automatic Data Fetching**: Fetches standings data from the JSON URL automatically
3. **Caching**: Implements WordPress transients to cache data for 1 hour to improve performance
4. **Error Handling**: Graceful fallback if the JSON data cannot be fetched
5. **Team Highlighting**: FK Kampbart team is highlighted in the table
6. **Responsive Design**: Table is horizontally scrollable on smaller screens
7. **Accessibility**: Proper ARIA labels and semantic HTML structure

## Data Source

The standings data is fetched from:

```
https://raw.githubusercontent.com/aprestmo/bedriftsidretten-standings-scraper/refs/heads/main/public/standings.json
```

## Table Columns

- **Pos**: Position in the table
- **Lag**: Team name
- **K**: Matches played
- **V**: Wins
- **U**: Draws
- **T**: Losses
- **P**: Points (highlighted)
- **M+**: Goals scored
- **M-**: Goals conceded
- **M±**: Goal difference

## Styling

The table uses the theme's color scheme:

- FK Kampbart team is highlighted with a light red background
- Points column is highlighted in the brand red color
- Alternating row colors for better readability
- Hover effects for better user interaction

## Caching

Data is cached using WordPress transients for 1 hour to:

- Reduce load on the external API
- Improve page load performance
- Provide fallback data if the external service is temporarily unavailable

## Error Handling

If the JSON data cannot be fetched, a user-friendly error message is displayed in Norwegian.

## Implementation Details

The conditional logic checks for both the term slug and term ID to ensure compatibility:

```php
$should_show_standings = (
    $term->slug === 'uteserie-2025' ||
    $term->term_id === 311
);
```

This approach ensures the standings table appears on the correct tournament page while preserving the original functionality for all other tournament pages.
