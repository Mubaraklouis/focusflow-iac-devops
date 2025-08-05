#!/bin/bash

# Update system and install Nginx
sudo apt update -y
sudo apt install nginx -y

# Stop Nginx to configure it
sudo systemctl stop nginx

# Remove default Nginx config
sudo rm /etc/nginx/sites-enabled/default

# Create a new Nginx reœœverse proxy config
sudo bash -c 'cat > /etc/nginx/sites-available/reverse_proxy <<EOF
server {
    listen 80;
    server_name _;

    location / {
        proxy_pass http://56.228.35.162:8000;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }

    location /course-api/ {
        # Remove /course-api/ from the path and proxy to root of the EC2 instance
        proxy_pass http://ec2-13-60-64-213.eu-north-1.compute.amazonaws.com:8000/;
        proxy_set_header Host ec2-13-60-64-213.eu-north-1.compute.amazonaws.com;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        # Fix CORS issues
        add_header Access-Control-Allow-Origin *;
        add_header Access-Control-Allow-Methods "GET, POST, OPTIONS, PUT, DELETE";
        add_header Access-Control-Allow-Headers "X-Requested-With, Content-Type, Authorization";
    }

    # For requests without trailing slash
    location = /course-api {
        return 301 \$scheme://\$host/course-api/;
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

# Show detailed information
echo "Nginx configuration completed"
echo "Main service: http://56.228.35.162:8000"
echo "Course API: http://ec2-13-60-64-213.eu-north-1.compute.amazonaws.com:8000"
echo ""
echo "You can now access the API through: http://YOUR_SERVER/course-api/"
echo ""
echo "Testing steps:"
echo "1. Check if nginx is running: sudo systemctl status nginx"
echo "2. Test direct access to API: curl http://ec2-13-60-64-213.eu-north-1.compute.amazonaws.com:8000"
echo "3. Test through proxy: curl http://localhost/course-api/"