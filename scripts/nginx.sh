#!/bin/bash

# Update system and install Nginx
sudo apt update -y
sudo apt install nginx -y

# Stop Nginx to configure it
sudo systemctl stop nginx

# Remove default Nginx config
sudo rm /etc/nginx/sites-enabled/default

# Create a new Nginx reverse proxy config
sudo bash -c 'cat > /etc/nginx/sites-available/reverse_proxy <<EOF
server {
    listen 80;
    server_name _;

    location / {
        proxy_pass http://56.228.35.162:8000:x;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF'

# Enable the config
sudo ln -s /etc/nginx/sites-available/reverse_proxy /etc/nginx/sites-enabled/

# Test Nginx config
sudo nginx -t

# Start Nginx
sudo systemctl start nginx

# Enable Nginx to start on boot
sudo systemctl enable nginx

echo "Nginx installed and configured as a reverse proxy for http://0.0.0.0:8000"