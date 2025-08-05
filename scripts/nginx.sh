#!/bin/bash
set -e

echo "Installing Nginx and configuring reverse proxy to localhost:8000..."

# Update package list and install nginx
sudo apt update
sudo apt install -y nginx

# Create Nginx site config for reverse proxy
sudo tee /etc/nginx/sites-available/laravel_proxy > /dev/null <<EOL
server {
    listen 80;
    server_name _;

    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOL

# Remove default config if exists
sudo rm -f /etc/nginx/sites-enabled/default

# Enable the new config
sudo ln -sf /etc/nginx/sites-available/laravel_proxy /etc/nginx/sites-enabled/laravel_proxy

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx

# Enable Nginx on boot
sudo systemctl enable nginx

echo "✅ Nginx installed and configured to proxy traffic to http://localhost:8000"
