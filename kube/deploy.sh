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

# Pull and run the FocusFlow container
echo "Pulling and running FocusFlow container..."
sudo docker pull mubaraklouis/focusflow:1.0.0
sudo docker run -d -p 8000:8000 --name focusflow-app mubaraklouis/focusflow:1.1.0

echo "Docker installation and container deployment completed successfully!"
echo "The FocusFlow container is now running on port 8000"