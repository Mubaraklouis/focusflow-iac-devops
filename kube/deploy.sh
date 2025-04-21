#!/bin/bash

echo "Starting Docker installation and container deployment..."

# Install Docker
echo "Installing Docker..."
sudo apt-get update
sudo apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

# Add Docker's official GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Set up the stable repository
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker Engine
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io

# Add current user to docker group
sudo usermod -aG docker $USER

# Clean up existing containers
echo "Cleaning up existing containers..."
sudo docker stop focusflow-app || true
sudo docker rm focusflow-app || true

# Pull the FocusFlow container
echo "Pulling FocusFlow container..."
sudo docker pull mubaraklouis/focusflow-cms-service:0.0.0

# Run with interactive console to see any issues
echo "Running FocusFlow container with extended options..."
sudo docker run --name focusflow-app \
  -p 8000:8000 \
  -e "PORT=8000" \
  --restart unless-stopped \
  -d mubaraklouis/focusflow-cms-service:0.0.0

# Check if container is running
echo "Checking container status..."
sudo docker ps
echo "If no container is listed above, checking for failed containers..."
sudo docker ps -a

# Check container logs if it exists
echo "Checking container logs..."
sudo docker logs focusflow-app || echo "No logs available"

echo "Docker installation and container deployment completed successfully!"
echo "The FocusFlow container should be running on port 8000"
echo "If you don't see the container in 'docker ps', check the logs above for errors"