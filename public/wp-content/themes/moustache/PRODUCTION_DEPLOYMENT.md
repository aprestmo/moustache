# Standings Table Production Deployment Checklist

## Files to Deploy

Make sure these files are uploaded to your production server:

### Updated Files:
- `functions.php` (contains all the standings functions)
- `template-parts/standings-table.php` (the template that displays the table)
- `taxonomy-tournament.php` (already includes the standings table)

### New Files:
- `test-standings.php` (optional - for debugging)
- `PRODUCTION_DEPLOYMENT.md` (this file)

## Pre-Deployment Checks

### 1. Server Requirements
- PHP 8.0+ (for return type declarations like `array|false`)
- cURL extension enabled
- OpenSSL extension for HTTPS requests
- `allow_url_fopen` enabled (recommended)

### 2. WordPress Requirements
- WordPress 5.0+ 
- `wp_remote_get()` function available
- Transient caching working

## Deployment Steps

### 1. Upload Files
Upload the modified files to your production server, maintaining the same directory structure.

### 2. Clear Cache
If you have any caching plugins (like WP Rocket, W3 Total Cache, etc.):
- Clear all caches
- Clear object cache if using Redis/Memcached

### 3. Test the Functions
Go to **WP Admin > Tools > Standings** to:
- Check environment status
- Test data fetching
- Clear cache if needed

### 4. Check Error Logs
Monitor your WordPress error logs for any issues:
- Look for lines containing "Standings"
- Check for HTTP request errors
- Verify no fatal PHP errors

## Common Production Issues & Solutions

### Issue 1: Functions Not Found
**Symptoms:** "Standings functions not available in functions.php"
**Solution:** 
- Ensure `functions.php` was uploaded correctly
- Check file permissions (should be 644)
- Verify no PHP syntax errors in functions.php

### Issue 2: HTTP Request Failures
**Symptoms:** "Standings API Error" in logs
**Solutions:**
- Check if your server can make outbound HTTPS requests
- Verify firewall isn't blocking GitHub API (api.github.com)
- Try increasing timeout in wp-config.php: `define('WP_HTTP_BLOCK_EXTERNAL', false);`

### Issue 3: SSL/TLS Issues
**Symptoms:** SSL verification errors
**Solutions:**
- Ensure server has up-to-date SSL certificates
- If needed, temporarily disable SSL verification (not recommended for production)

### Issue 4: Caching Issues
**Symptoms:** Data not updating, cache not working
**Solutions:**
- Clear all WordPress caches
- Check if transients are working: go to Tools > Standings
- Verify database permissions for wp_options table

### Issue 5: Memory or Timeout Issues
**Symptoms:** Page doesn't load, white screen, timeout errors
**Solutions:**
- Increase PHP memory limit in wp-config.php: `ini_set('memory_limit', '256M');`
- Increase max execution time: `ini_set('max_execution_time', 60);`

## Testing in Production

### 1. Visit the Tournament Page
Go to your "uteserie-2025" tournament page and verify:
- ✓ Standings table appears in the "Tabell" section
- ✓ Data is displayed correctly
- ✓ No error messages visible

### 2. Check Admin Dashboard
Go to **Tools > Standings** and verify:
- ✓ No environment issues
- ✓ Data fetch succeeds
- ✓ Cache is working

### 3. Monitor Performance
- Check page load times
- Monitor server resources
- Watch error logs for the first few hours

## Disable Debug Mode

Once everything is working in production:

1. Edit `template-parts/standings-table.php`
2. Change the debug conditions to disable debug output:
   ```php
   // Only enable debug on localhost 
   $debug_mode = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
   ```

## Troubleshooting Commands

If you have SSH access to your production server:

```bash
# Check error logs
tail -f /path/to/wordpress/wp-content/debug.log | grep Standings

# Test direct API access
curl -I "https://api.github.com/repos/aprestmo/bedriftsidretten-standings-scraper/contents/public/standings.json?ref=main"

# Check PHP configuration
php -m | grep -E "(curl|openssl|json)"
```

## Support

If issues persist after following this checklist:
1. Check the WordPress error logs
2. Use the test-standings.php script
3. Verify all files were uploaded correctly
4. Contact your hosting provider about HTTP request restrictions