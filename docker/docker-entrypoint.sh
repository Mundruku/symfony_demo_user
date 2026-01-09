#!/bin/bash
set -e

echo "🚀 Symfony Docker Entrypoint"
echo "=============================="

# Check if vendor directory exists, if not install dependencies
if [ ! -d "vendor" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader --no-progress
fi

# Wait for database to be ready (health check already in docker-compose)
echo "⏳ Waiting for database to be ready..."
sleep 5

# Create database if it doesn't exist
echo "🗄️  Setting up database..."
php bin/console doctrine:database:create --if-not-exists --no-interaction 2>/dev/null || true

# Run migrations
echo "🔄 Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

# Load fixtures if they exist
if [ -f "src/DataFixtures/AppFixtures.php" ]; then
    echo "🌱 Loading fixtures..."
    php bin/console doctrine:fixtures:load --no-interaction || true
fi

# Clear cache
echo "🧹 Clearing cache..."
php bin/console cache:clear --no-warmup || true

echo "✅ Setup complete!"
echo "🔒 Starting PHP server with HTTPS simulation on port 8000..."
echo "🌍 Open https://localhost in your browser (accept the security warning)"

# Start PHP built-in server with HTTPS simulation
# Note: PHP built-in server doesn't support HTTPS natively
# We're simulating it by using HTTP on port 8000, mapped to 443
exec php -S 0.0.0.0:8000 -t public