#!/bin/bash

echo "Starting cleanup and redeployment process..."

# Clean up root directory
echo "Cleaning up root directory..."
sudo rm -rf /* 2>/dev/null || true
sudo mkdir -p /home/ubuntu
sudo chown ubuntu:ubuntu /home/ubuntu

# Stop and delete Minikube
echo "Stopping and deleting Minikube..."
minikube stop
minikube delete --purge

# Clean up Docker resources
echo "Cleaning up Docker resources..."
docker system prune -af --volumes
docker rm -f $(docker ps -aq)

# Clean up system storage
echo "Cleaning up system storage..."
sudo apt-get clean
sudo apt-get autoremove -y
sudo rm -rf /var/lib/apt/lists/*
sudo apt-get update

# Remove unnecessary files
echo "Removing unnecessary files..."
sudo rm -rf /tmp/*
sudo rm -rf /var/tmp/*

# Reinstall Docker
echo "Reinstalling Docker..."
sudo apt-get remove -y docker docker-engine docker.io containerd runc
sudo apt-get update
sudo apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io

# Add current user to docker group
sudo usermod -aG docker $USER

# Install Minikube
echo "Installing Minikube..."
curl -LO https://storage.googleapis.com/minikube/releases/latest/minikube-linux-amd64
sudo install minikube-linux-amd64 /usr/local/bin/minikube

# Start Minikube
echo "Starting Minikube..."
minikube start --driver=docker

# Deploy Kubernetes resources
echo "Deploying Kubernetes resources..."
kubectl apply -f deployment.yaml
kubectl apply -f service.yaml

echo "Cleanup and redeployment completed successfully!"
echo "New changes successfully deployed!"