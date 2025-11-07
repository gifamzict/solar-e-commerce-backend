#!/bin/bash

# Railway Post-Deployment Script
echo "🚀 Running post-deployment tasks..."

# Create storage directories
echo "📁 Setting up storage directories..."
php artisan storage:link

# Clear and cache configurations
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Create superadmin if not exists
echo "👤 Checking superadmin account..."
php artisan db:seed --class=SuperAdminSeeder --force || true

echo "✅ Deployment complete!"
