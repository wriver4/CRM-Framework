#!/bin/bash

# Download Setup Files Script
# Run this on your Ubuntu laptop to download the setup files

REMOTE_HOST="159.203.116.150"
REMOTE_PORT="222"
REMOTE_USER="democrm"
REMOTE_PATH="/home/democrm"

echo "=================================================="
echo "Downloading Setup Files from Remote Server"
echo "=================================================="
echo ""

# Create local directory for setup files
mkdir -p ~/democrm-setup
cd ~/democrm-setup

echo "Downloading setup script..."
scp -P "$REMOTE_PORT" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/ubuntu-laptop-setup.sh" .

echo "Downloading setup guide..."
scp -P "$REMOTE_PORT" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/UBUNTU_LAPTOP_SETUP.md" .

echo ""
echo "=================================================="
echo "Files downloaded to: ~/democrm-setup/"
echo "=================================================="
echo ""
echo "Next steps:"
echo ""
echo "1. Make the setup script executable:"
echo "   chmod +x ~/democrm-setup/ubuntu-laptop-setup.sh"
echo ""
echo "2. Run the setup script:"
echo "   ~/democrm-setup/ubuntu-laptop-setup.sh"
echo ""
echo "3. Read the detailed guide:"
echo "   cat ~/democrm-setup/UBUNTU_LAPTOP_SETUP.md"
echo ""
echo "=================================================="