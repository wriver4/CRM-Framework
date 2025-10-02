#!/bin/bash

# Ubuntu Laptop Setup Script for MCP Server Access
# This script sets up your Ubuntu laptop to connect to the remote MCP server

set -e  # Exit on error

echo "=================================================="
echo "Ubuntu Laptop Setup for MCP Server"
echo "=================================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Remote server details
REMOTE_HOST="159.203.116.150"
REMOTE_PORT="222"
REMOTE_USER="democrm"
REMOTE_PATH="/home/democrm"

echo -e "${YELLOW}Step 1: Checking system requirements...${NC}"

# Check if running on Ubuntu/Debian
if ! command -v apt &> /dev/null; then
    echo -e "${RED}Error: This script is for Ubuntu/Debian systems only${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Ubuntu/Debian detected${NC}"

# Check if VS Code is installed
echo ""
echo -e "${YELLOW}Step 2: Checking VS Code installation...${NC}"

if ! command -v code &> /dev/null; then
    echo -e "${YELLOW}VS Code not found. Installing...${NC}"
    sudo snap install code --classic
    echo -e "${GREEN}✓ VS Code installed${NC}"
else
    echo -e "${GREEN}✓ VS Code already installed${NC}"
    code --version
fi

# Check if SSH client is installed
echo ""
echo -e "${YELLOW}Step 3: Checking SSH client...${NC}"

if ! command -v ssh &> /dev/null; then
    echo -e "${YELLOW}SSH client not found. Installing...${NC}"
    sudo apt update
    sudo apt install -y openssh-client
    echo -e "${GREEN}✓ SSH client installed${NC}"
else
    echo -e "${GREEN}✓ SSH client already installed${NC}"
fi

# Check if Node.js is installed
echo ""
echo -e "${YELLOW}Step 4: Checking Node.js installation...${NC}"

if ! command -v node &> /dev/null; then
    echo -e "${YELLOW}Node.js not found. Installing...${NC}"
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt install -y nodejs
    echo -e "${GREEN}✓ Node.js installed${NC}"
else
    echo -e "${GREEN}✓ Node.js already installed${NC}"
    node --version
fi

# Install VS Code Remote-SSH extension
echo ""
echo -e "${YELLOW}Step 5: Installing VS Code Remote-SSH extension...${NC}"

if code --list-extensions | grep -q "ms-vscode-remote.remote-ssh"; then
    echo -e "${GREEN}✓ Remote-SSH extension already installed${NC}"
else
    code --install-extension ms-vscode-remote.remote-ssh
    echo -e "${GREEN}✓ Remote-SSH extension installed${NC}"
fi

# Install Zencoder extension
echo ""
echo -e "${YELLOW}Step 6: Installing Zencoder extension...${NC}"

if code --list-extensions | grep -q "zencoder.zencoder"; then
    echo -e "${GREEN}✓ Zencoder extension already installed${NC}"
else
    code --install-extension zencoder.zencoder
    echo -e "${GREEN}✓ Zencoder extension installed${NC}"
fi

# Set up SSH directory
echo ""
echo -e "${YELLOW}Step 7: Setting up SSH configuration...${NC}"

mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Check if SSH key exists
if [ ! -f ~/.ssh/id_ed25519 ] && [ ! -f ~/.ssh/id_rsa ]; then
    echo -e "${YELLOW}No SSH key found. Generating new key...${NC}"
    echo -e "${YELLOW}Please enter your email address:${NC}"
    read -p "Email: " user_email
    ssh-keygen -t ed25519 -C "$user_email"
    echo -e "${GREEN}✓ SSH key generated${NC}"
else
    echo -e "${GREEN}✓ SSH key already exists${NC}"
fi

# Create SSH config
echo ""
echo -e "${YELLOW}Step 8: Creating SSH config...${NC}"

SSH_CONFIG="$HOME/.ssh/config"

# Check if config already has the host
if grep -q "Host democrm-server" "$SSH_CONFIG" 2>/dev/null; then
    echo -e "${GREEN}✓ SSH config already contains democrm-server${NC}"
else
    cat >> "$SSH_CONFIG" << EOF

# DemoCRM Remote Server
Host democrm-server
    HostName $REMOTE_HOST
    Port $REMOTE_PORT
    User $REMOTE_USER
    ServerAliveInterval 60
    ServerAliveCountMax 3
EOF
    chmod 600 "$SSH_CONFIG"
    echo -e "${GREEN}✓ SSH config created${NC}"
fi

# Test SSH connection
echo ""
echo -e "${YELLOW}Step 9: Testing SSH connection...${NC}"
echo -e "${YELLOW}You may need to enter your password and/or accept the host key${NC}"
echo ""

if ssh -p "$REMOTE_PORT" -o ConnectTimeout=10 -o BatchMode=yes "$REMOTE_USER@$REMOTE_HOST" exit 2>/dev/null; then
    echo -e "${GREEN}✓ SSH connection successful (key-based auth)${NC}"
else
    echo -e "${YELLOW}SSH key-based authentication not set up yet.${NC}"
    echo -e "${YELLOW}Let's copy your SSH key to the remote server...${NC}"
    echo ""
    
    # Try to copy SSH key
    if [ -f ~/.ssh/id_ed25519.pub ]; then
        ssh-copy-id -p "$REMOTE_PORT" "$REMOTE_USER@$REMOTE_HOST"
    elif [ -f ~/.ssh/id_rsa.pub ]; then
        ssh-copy-id -p "$REMOTE_PORT" "$REMOTE_USER@$REMOTE_HOST"
    else
        echo -e "${RED}Error: No SSH public key found${NC}"
        exit 1
    fi
    
    # Test again
    if ssh -p "$REMOTE_PORT" -o ConnectTimeout=10 "$REMOTE_USER@$REMOTE_HOST" exit; then
        echo -e "${GREEN}✓ SSH connection successful${NC}"
    else
        echo -e "${RED}✗ SSH connection failed${NC}"
        echo -e "${YELLOW}Please check your credentials and try again${NC}"
        exit 1
    fi
fi

# Create quick connect script
echo ""
echo -e "${YELLOW}Step 10: Creating quick connect script...${NC}"

CONNECT_SCRIPT="$HOME/connect-democrm.sh"

cat > "$CONNECT_SCRIPT" << 'EOF'
#!/bin/bash

# Quick connect script for DemoCRM server

echo "Connecting to DemoCRM server via VS Code Remote-SSH..."
echo ""
echo "Once VS Code opens:"
echo "1. Click 'Open Folder'"
echo "2. Navigate to /home/democrm"
echo "3. Click OK"
echo "4. Press Ctrl+Shift+P → 'Developer: Reload Window'"
echo "5. Test MCP server in Zencoder chat"
echo ""

code --remote ssh-remote+democrm-server /home/democrm
EOF

chmod +x "$CONNECT_SCRIPT"
echo -e "${GREEN}✓ Quick connect script created at: $CONNECT_SCRIPT${NC}"

# Summary
echo ""
echo "=================================================="
echo -e "${GREEN}Setup Complete!${NC}"
echo "=================================================="
echo ""
echo "Next steps:"
echo ""
echo "1. Connect to remote server:"
echo "   ${GREEN}$CONNECT_SCRIPT${NC}"
echo "   OR"
echo "   ${GREEN}ssh democrm-server${NC}"
echo ""
echo "2. In VS Code:"
echo "   - Press Ctrl+Shift+P"
echo "   - Type: 'Remote-SSH: Connect to Host'"
echo "   - Select 'democrm-server'"
echo "   - Open folder: /home/democrm"
echo ""
echo "3. Verify MCP server:"
echo "   - Press Ctrl+Shift+P"
echo "   - Type: 'Zencoder: Open Chat'"
echo "   - Ask: 'Can you use get_repo_context to show the repository?'"
echo ""
echo "4. Enable VS Code Settings Sync (optional):"
echo "   - Press Ctrl+Shift+P"
echo "   - Type: 'Settings Sync: Turn On'"
echo "   - Sign in with Microsoft or GitHub"
echo ""
echo "=================================================="
echo ""
echo "Remote Server: $REMOTE_HOST:$REMOTE_PORT"
echo "MCP Server: /home/democrm/.zencoder/mcp-server/index.js"
echo "Node.js: /usr/bin/node (v16.20.2)"
echo ""
echo "For detailed instructions, see:"
echo "  ${GREEN}UBUNTU_LAPTOP_SETUP.md${NC}"
echo ""
echo "=================================================="