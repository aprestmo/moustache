#!/bin/bash
# RunCloud Deployment Fix Script
# This script should be run on your RunCloud server to fix the Git issues

echo "=== RunCloud Deployment Fix ==="
echo "Starting at $(date)"

# Fix 1: Clean up conflicting branch references  
echo "1. Cleaning up branch references..."
git remote prune origin
git remote update

# Fix 2: Handle the ACF JSON file conflict
echo "2. Handling ACF JSON file conflict..."

# Check if the file exists and has changes
if git diff-index --quiet HEAD -- public/wp-content/themes/moustache/acf-json/group_5611b70e91d20.json; then
    echo "   No local changes to ACF JSON file"
else
    echo "   Found local changes to ACF JSON file"
    
    # Option A: Backup and commit the changes (recommended)
    echo "   Backing up local ACF changes..."
    cp public/wp-content/themes/moustache/acf-json/group_5611b70e91d20.json public/wp-content/themes/moustache/acf-json/group_5611b70e91d20.json.backup
    
    # Commit the local changes
    git add public/wp-content/themes/moustache/acf-json/group_5611b70e91d20.json
    git commit -m "chore: Update ACF JSON configuration from production"
    
    echo "   ACF changes committed"
fi

# Fix 3: Force fetch and reset to latest
echo "3. Fetching latest changes..."
git fetch origin

echo "4. Merging/pulling latest changes..."
git pull origin main

echo "=== Deployment Status Check ==="

# Check if our key files exist
files_to_check=(
    "public/wp-content/themes/moustache/functions.php"
    "public/wp-content/themes/moustache/template-parts/standings-table.php" 
    "public/wp-content/themes/moustache/test-standings.php"
    "public/wp-content/themes/moustache/verify-deployment.php"
)

for file in "${files_to_check[@]}"; do
    if [ -f "$file" ]; then
        echo "✓ $file exists"
    else
        echo "✗ $file missing!"
    fi
done

# Check if standings functions exist in functions.php
if grep -q "fetch_standings_data" public/wp-content/themes/moustache/functions.php; then
    echo "✓ Standings functions found in functions.php"
else
    echo "✗ Standings functions not found in functions.php!"
fi

echo "=== Deployment completed at $(date) ==="
echo ""
echo "Next steps:"
echo "1. Test the deployment by visiting: yourdomain.com/wp-content/themes/moustache/verify-deployment.php?allow=1"
echo "2. Check your tournament page for the standings table"
echo "3. Monitor WordPress error logs"
echo "4. Delete verify-deployment.php after testing"