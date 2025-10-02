# 🚀 Quick Start Guide - Ubuntu Laptop Setup

## For Your Ubuntu Laptop

### Step 1: Download Setup Files

On your Ubuntu laptop, run:

```bash
# Create directory
mkdir -p ~/democrm-setup && cd ~/democrm-setup

# Download setup script
scp -P 222 democrm@159.203.116.150:/home/democrm/ubuntu-laptop-setup.sh .

# Download documentation
scp -P 222 democrm@159.203.116.150:/home/democrm/UBUNTU_LAPTOP_SETUP.md .
scp -P 222 democrm@159.203.116.150:/home/democrm/MCP_SERVER_SETUP_SUMMARY.md .
```

### Step 2: Run Setup Script

```bash
# Make executable
chmod +x ubuntu-laptop-setup.sh

# Run setup
./ubuntu-laptop-setup.sh
```

The script will:
- ✅ Install VS Code (if needed)
- ✅ Install Node.js (if needed)
- ✅ Install Remote-SSH extension
- ✅ Install Zencoder extension
- ✅ Set up SSH configuration
- ✅ Copy SSH key to remote server
- ✅ Create quick connect script

### Step 3: Connect to Remote Server

**Option A: Using Quick Connect Script**
```bash
~/connect-democrm.sh
```

**Option B: Using VS Code**
1. Open VS Code
2. Press `Ctrl+Shift+P`
3. Type: `Remote-SSH: Connect to Host`
4. Select `democrm-server` (or enter `democrm@159.203.116.150`)
5. Open folder: `/home/democrm`

**Option C: Using SSH Command**
```bash
ssh democrm-server
# OR
ssh -p 222 democrm@159.203.116.150
```

### Step 4: Test MCP Server

1. In VS Code (connected to remote server)
2. Press `Ctrl+Shift+P` → `Zencoder: Open Chat`
3. Ask: "Can you use get_repo_context to show me the repository structure?"

---

## For Your Current Machine

### Enable Settings Sync (One-Time Setup)

1. Press `Ctrl+Shift+P`
2. Type: `Settings Sync: Turn On`
3. Sign in with Microsoft or GitHub
4. Select what to sync (Settings, Extensions, etc.)

### Reload VS Code

```
Ctrl+Shift+P → "Developer: Reload Window"
```

### Test MCP Server

```
Ctrl+Shift+P → "Zencoder: Open Chat"
Ask: "Can you use get_repo_context to show the repository?"
```

---

## Troubleshooting

### Can't connect via SSH?
```bash
# Test connection
ssh -p 222 democrm@159.203.116.150

# If fails, check:
# 1. Internet connection
# 2. Server is running
# 3. Port 222 is not blocked by firewall
```

### MCP server not working?
```bash
# Test manually on remote server
ssh democrm-server
/usr/bin/node /home/democrm/.zencoder/mcp-server/index.js
# Should show: "MCP Server running on stdio"
# Press Ctrl+C to exit
```

### VS Code can't find remote server?
```bash
# Check SSH config
cat ~/.ssh/config | grep -A 5 democrm-server

# Should show:
# Host democrm-server
#     HostName 159.203.116.150
#     Port 222
#     User democrm
```

---

## Quick Commands Reference

```bash
# Connect to server
ssh democrm-server

# Download files from server
scp -P 222 democrm@159.203.116.150:/path/to/file ~/local/path

# Upload files to server
scp -P 222 ~/local/file democrm@159.203.116.150:/home/democrm/

# Test MCP server
/usr/bin/node /home/democrm/.zencoder/mcp-server/index.js

# Check Node.js version
/usr/bin/node --version

# View MCP config
cat /home/democrm/.vscode/settings.json
```

---

## Files You Downloaded

- **`ubuntu-laptop-setup.sh`** - Automated setup script (run this first)
- **`UBUNTU_LAPTOP_SETUP.md`** - Detailed setup guide with troubleshooting
- **`MCP_SERVER_SETUP_SUMMARY.md`** - Complete overview and architecture
- **`QUICK_START.md`** - This file (quick reference)

---

## Need More Help?

Read the detailed guides:
- **Quick Start:** `QUICK_START.md` (this file)
- **Full Setup Guide:** `UBUNTU_LAPTOP_SETUP.md`
- **Architecture & Overview:** `MCP_SERVER_SETUP_SUMMARY.md`

---

## Summary

1. ✅ Download setup files to Ubuntu laptop
2. ✅ Run `ubuntu-laptop-setup.sh`
3. ✅ Connect to remote server via VS Code Remote-SSH
4. ✅ Open folder: `/home/democrm`
5. ✅ Test MCP server in Zencoder

**That's it!** Your Ubuntu laptop will have access to the same MCP server and repository as your current machine.

---

**Remote Server:** 159.203.116.150:222  
**MCP Server:** `/home/democrm/.zencoder/mcp-server/index.js`  
**Node.js:** `/usr/bin/node` (v16.20.2)