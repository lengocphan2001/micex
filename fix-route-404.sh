#!/bin/bash

# Script để fix lỗi 404 cho API routes
# Usage: ./fix-route-404.sh

echo "=== Fixing 404 Error for API Routes ==="
echo ""

PROJECT_PATH="/var/www/micex"
cd "$PROJECT_PATH" || exit 1

echo "1. Clearing all Laravel caches..."
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo ""
echo "2. Checking route exists..."
php artisan route:list | grep "round-result"

echo ""
echo "3. Re-caching routes..."
php artisan route:cache

echo ""
echo "4. Verifying route cache..."
php artisan route:list | grep "round-result"

echo ""
echo "5. Testing route directly..."
php artisan tinker --execute="echo route('explore.round-result', ['round_number' => 515287]);"

echo ""
echo "6. Checking Nginx configuration..."
echo "Make sure nginx config has:"
echo "  location / {"
echo "      try_files \$uri \$uri/ /index.php?\$query_string;"
echo "  }"

echo ""
echo "7. Restarting services..."
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx

echo ""
echo "8. Checking nginx access log for round-result requests..."
echo "   (This will show if requests are reaching the server)"
sudo tail -20 /var/log/nginx/micex-access.log | grep round-result || echo "   No round-result requests found in recent logs"

echo ""
echo "=== Done ==="
echo ""
echo "9. Test the route:"
echo "   curl -v https://micex-x.com/api/explore/round-result?round_number=515287"
echo ""
echo "   Or with authentication (if needed):"
echo "   curl -v -H 'Cookie: laravel_session=YOUR_SESSION' https://micex-x.com/api/explore/round-result?round_number=515287"
echo ""
echo "10. Monitor logs in real-time:"
echo "    # Access log (see all requests)"
echo "    sudo tail -f /var/log/nginx/micex-access.log | grep round-result"
echo ""
echo "    # Error log (see errors)"
echo "    sudo tail -f /var/log/nginx/micex-error.log"
echo ""
echo "    # Laravel log (see application errors)"
echo "    tail -f storage/logs/laravel.log"
echo ""
echo "11. Fix missing CSS/JS assets (if needed):"
echo "    cd /var/www/micex"
echo "    npm install"
echo "    npm run build"

