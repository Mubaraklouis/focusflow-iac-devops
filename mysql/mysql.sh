#!/bin/bash

# MySQL Installation and Configuration Script for EC2
# This script will:
# 1. Install MySQL Server
# 2. Secure the MySQL installation
# 3. Create the focusflow database and mubarak user
# 4. Configure basic settings

# Check if running as root
if [ "$(id -u)" -ne 0 ]; then
  echo "This script must be run as root" >&2
  exit 1
fi

# Set database and user parameters
DB_NAME="focusflow"
DB_USER="mubarak"
DB_USER_PASSWORD="kuallouis"  # Change this to a strong password

# Detect the OS distribution
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
elif type lsb_release >/dev/null 2>&1; then
    OS=$(lsb_release -si | tr '[:upper:]' '[:lower:]')
else
    OS=$(uname -s | tr '[:upper:]' '[:lower:]')
fi

# Function to install MySQL on Amazon Linux 2
install_mysql_amazon_linux() {
    echo "Installing MySQL on Amazon Linux 2"
    sudo yum update -y
    sudo yum install -y mysql-server
    sudo systemctl start mysqld
    sudo systemctl enable mysqld
}

# Function to install MySQL on Ubuntu/Debian
install_mysql_ubuntu() {
    echo "Installing MySQL on Ubuntu/Debian"
    sudo apt-get update
    sudo apt-get install -y mysql-server
    sudo systemctl start mysql
    sudo systemctl enable mysql
}

# Install MySQL based on OS
case $OS in
    amzn)
        install_mysql_amazon_linux
        ;;
    ubuntu|debian)
        install_mysql_ubuntu
        ;;
    *)
        echo "Unsupported OS: $OS"
        exit 1
        ;;
esac

# Wait for MySQL to start properly
sleep 5

# Secure MySQL installation
echo "Securing MySQL installation..."
SECURE_MYSQL=$(expect -c "
set timeout 10
spawn mysql_secure_installation

expect \"Press y|Y for Yes, any other key for No:\"
send \"n\r\"

expect \"New password:\"
send \"${MYSQL_ROOT_PASSWORD}\r\"

expect \"Re-enter new password:\"
send \"${MYSQL_ROOT_PASSWORD}\r\"

expect \"Remove anonymous users? (Press y|Y for Yes, any other key for No) :\"
send \"y\r\"

expect \"Disallow root login remotely? (Press y|Y for Yes, any other key for No) :\"
send \"y\r\"

expect \"Remove test database and access to it? (Press y|Y for Yes, any other key for No) :\"
send \"y\r\"

expect \"Reload privilege tables now? (Press y|Y for Yes, any other key for No) :\"
send \"y\r\"

expect eof
")

echo "$SECURE_MYSQL"

# Basic MySQL configuration
echo "Configuring MySQL basic settings..."
cat > /etc/mysql/conf.d/custom.cnf <<EOF
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 256M
innodb_log_file_size = 128M
skip-name-resolve
bind-address = 0.0.0.0
EOF

# Restart MySQL to apply changes
if [ "$OS" = "amzn" ]; then
    sudo systemctl restart mysqld
else
    sudo systemctl restart mysql
fi

# Create focusflow database and mubarak user
echo "Creating focusflow database and mubarak user..."
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" <<MYSQL_SCRIPT
CREATE DATABASE ${DB_NAME};
CREATE USER '${DB_USER}'@'%' IDENTIFIED BY '${DB_USER_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'%';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

echo "MySQL installation and configuration complete!"
echo "Root password: ${MYSQL_ROOT_PASSWORD}"
echo "Database user: ${DB_USER} with password: ${DB_USER_PASSWORD}"
echo "Database created: ${DB_NAME}"