#!/bin/bash

# Docker Cleanup Script for EC2
# This will remove ALL Docker containers, images, volumes, and networks
# WARNING: This is destructive and irreversible!

# Check if running as root
if [ "$(id -u)" -ne 0 ]; then
  echo "This script must be run as root" >&2
  exit 1
fi

# Verify the user really wants to do this
echo "WARNING: This will remove ALL Docker containers, images, volumes, and networks!"
read -p "Are you sure you want to continue? [y/N] " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborting cleanup."
    exit 1
fi

# Stop all running containers
echo "Stopping all running containers..."
docker stop $(docker ps -aq) 2>/dev/null || echo "No containers to stop"

# Remove all containers
echo "Removing all containers..."
docker rm $(docker ps -aq) 2>/dev/null || echo "No containers to remove"

# Remove all images
echo "Removing all images..."
docker rmi $(docker images -q) 2>/dev/null || echo "No images to remove"

# Remove all volumes
echo "Removing all volumes..."
docker volume rm $(docker volume ls -q) 2>/dev/null || echo "No volumes to remove"

# Remove all networks (except predefined ones)
echo "Removing all custom networks..."
docker network rm $(docker network ls -q --filter type=custom) 2>/dev/null || echo "No networks to remove"

# Clean up unused objects (Docker 17.09.0+)
echo "Pruning system..."
docker system prune -a --volumes -f

# Optional: Uninstall Docker completely
read -p "Do you want to uninstall Docker completely? [y/N] " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Uninstalling Docker..."
    
    # Remove Docker packages
    if command -v apt-get >/dev/null; then
        apt-get purge -y docker-ce docker-ce-cli containerd.io
        apt-get autoremove -y
    elif command -v yum >/dev/null; then
        yum remove -y docker-ce docker-ce-cli containerd.io
    fi
    
    # Remove Docker files
    rm -rf /var/lib/docker
    rm -rf /var/lib/containerd
fi

echo "Docker cleanup complete!"