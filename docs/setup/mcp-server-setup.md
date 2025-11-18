# MCP Server Setup Summary

## ✅ Current Status (Remote Server)

### What's Configured:
- **MCP Server Location:** `/home/democrm/.zencoder/mcp-server/index.js`
- **Node.js Path:** `/usr/bin/node` (v16.20.2)
- **VS Code Workspace Config:** `/home/democrm/.vscode/settings.json`
- **Server Name:** `democrm-context`

### Configuration:
```json
{
  "zencoder.mcpServers": {
    "democrm-context": {
      "command": "/usr/bin/node",
      "args": ["/home/democrm/.zencoder/mcp-server/index.js"]
    }
  }
}
```

### How It Works:
- ✅ MCP server is **NOT** running as a systemd service (correct behavior)
- ✅ MCP server is spawned automatically by Zencoder when needed
- ✅ Communication happens via stdio (no network ports needed)
- ✅ No firewall configuration required

---

## 🎯 Ubuntu Laptop Setup (Option 1 - Recommended)

### Overview:
Your Ubuntu laptop will connect to the **same remote server** via SSH, giving you access to the same MCP server and repository.

### What You Need to Do:

#### On Your Current Machine (Now):
1. **Enable VS Code Settings Sync:**
   - Press `Ctrl+Shift+P` → `Settings Sync: Turn On`
   - Sign in with Microsoft or GitHub account
   - This will sync your extensions and user settings

#### On Your Ubuntu Laptop:

**Option A: Download and Run Setup Script (Easiest)**

```bash
# 1. Download setup files from remote server
curl -o download-setup.sh https://raw.githubusercontent.com/YOUR_REPO/download-setup-files.sh
# OR manually download via SCP:
scp -P 222 democrm@159.203.116.150:/home/democrm/ubuntu-laptop-setup.sh ~/
scp -P 222 democrm@159.203.116.150:/home/democrm/UBUNTU_LAPTOP_SETUP.md ~/

# 2. Make executable and run
chmod +x ~/ubuntu-laptop-setup.sh
~/ubuntu-laptop-setup.sh

# 3. Connect to remote server
~/connect-democrm.sh
```

**Option B: Manual Setup**

```bash
# 1. Install VS Code
sudo snap install code --classic

# 2. Install extensions
code --install-extension ms-vscode-remote.remote-ssh
code --install-extension zencoder.zencoder

# 3. Set up SSH
ssh-copy-id -p 222 democrm@159.203.116.150

# 4. Connect via VS Code
# Press Ctrl+Shift+P → "Remote-SSH: Connect to Host"
# Enter: democrm@159.203.116.150
# Port: 222
# Open folder: /home/democrm
```

---

## 📋 Files Created

### Setup Files (on remote server):
1. **`UBUNTU_LAPTOP_SETUP.md`** - Detailed setup guide with troubleshooting
2. **`ubuntu-laptop-setup.sh`** - Automated setup script for Ubuntu laptop
3. **`download-setup-files.sh`** - Script to download setup files to laptop
4. **`MCP_SERVER_SETUP_SUMMARY.md`** - This file (overview)

### Configuration Files:
1. **`.vscode/settings.json`** - MCP server configuration (workspace-specific)
2. **`.zencoder/mcp-server/index.js`** - MCP server implementation

---

## 🔄 How Settings Sync Works

### What Syncs Automatically:
- ✅ VS Code extensions (Zencoder, Remote-SSH, etc.)
- ✅ User settings (themes, keyboard shortcuts, etc.)
- ✅ Extension settings (global Zencoder settings)

### What Doesn't Sync:
- ❌ Workspace settings (`.vscode/settings.json`)
- ❌ SSH configuration (`~/.ssh/config`)
- ❌ Remote server files

### Why This Is Perfect:
When you connect to the remote server from your Ubuntu laptop:
1. VS Code Remote-SSH connects to the server
2. You open the `/home/democrm` folder
3. The `.vscode/settings.json` file is **already there** on the server
4. MCP server configuration is automatically available
5. Everything works exactly the same as on your current machine

---

## 🧪 Testing the Setup

### On Current Machine:
```bash
# 1. Reload VS Code
# Press Ctrl+Shift+P → "Developer: Reload Window"

# 2. Open Zencoder Chat
# Press Ctrl+Shift+P → "Zencoder: Open Chat"

# 3. Test MCP server
# Ask: "Can you use get_repo_context to show me the repository structure?"
```

### On Ubuntu Laptop (after setup):
```bash
# 1. Connect to remote server
ssh democrm-server
# OR
~/connect-democrm.sh

# 2. In VS Code, open folder: /home/democrm

# 3. Test MCP server (same as above)
```

---

## 🔧 Troubleshooting

### Issue: "MCP server not found"
**Solution:**
```bash
# Check if MCP server exists
ssh democrm-server
ls -la /home/democrm/.zencoder/mcp-server/index.js

# Test manually
/usr/bin/node /home/democrm/.zencoder/mcp-server/index.js
# Should show: "MCP Server running on stdio"
```

### Issue: "Cannot connect to remote server"
**Solution:**
```bash
# Test SSH connection
ssh -p 222 democrm@159.203.116.150

# Check SSH config
cat ~/.ssh/config | grep -A 5 democrm-server
```

### Issue: "Node.js not found"
**Solution:**
```bash
# On remote server
ssh democrm-server
which node
# Should show: /usr/bin/node

node --version
# Should show: v16.20.2
```

### Issue: "Settings not syncing"
**Solution:**
1. Check Settings Sync status in VS Code (gear icon → Settings Sync is On)
2. Manually trigger sync: `Ctrl+Shift+P` → `Settings Sync: Sync Now`
3. Remember: Workspace settings don't sync (but they're on the remote server)

---

## 📊 Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     Your Current Machine                     │
│  ┌────────────┐         SSH/SFTP          ┌──────────────┐  │
│  │  VS Code   │◄──────────────────────────►│ Remote Server│  │
│  │            │                            │ 159.203.116  │  │
│  │ + Zencoder │                            │ .150:222     │  │
│  │ + Remote   │                            │              │  │
│  │   SSH      │                            │ /home/democrm│  │
│  └────────────┘                            └──────────────┘  │
│       │                                            │         │
│       │ Settings Sync                              │         │
│       │ (Microsoft/GitHub)                         │         │
│       ▼                                            ▼         │
│  ┌────────────┐                            ┌──────────────┐  │
│  │  Cloud     │                            │ MCP Server   │  │
│  │  Sync      │                            │ (stdio)      │  │
│  └────────────┘                            └──────────────┘  │
│       │                                                      │
│       │ Settings Sync                                        │
│       ▼                                                      │
│  ┌────────────┐         SSH/SFTP          ┌──────────────┐  │
│  │ Ubuntu     │◄──────────────────────────►│ Remote Server│  │
│  │ Laptop     │                            │ (same)       │  │
│  │            │                            │              │  │
│  │ + Zencoder │                            │ /home/democrm│  │
│  │ + Remote   │                            │              │  │
│  │   SSH      │                            │ .vscode/     │  │
│  └────────────┘                            │ settings.json│  │
│                                            └──────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 Key Takeaways

1. **MCP Server Location:** Always on the remote server (`/home/democrm/.zencoder/mcp-server/`)
2. **No Systemd Service:** MCP server is spawned by Zencoder, not run as a daemon
3. **No Ports Needed:** Communication via stdio, no firewall configuration required
4. **Settings Sync:** User settings sync via cloud, workspace settings are on the server
5. **SSH Access:** Both machines connect to the same remote server via SSH
6. **Single Source of Truth:** Repository and MCP server are on the remote server

---

## 📞 Quick Reference

### Remote Server Details:
- **Host:** 159.203.116.150
- **Port:** 222
- **User:** democrm
- **Path:** /home/democrm

### MCP Server:
- **Script:** `/home/democrm/.zencoder/mcp-server/index.js`
- **Node.js:** `/usr/bin/node` (v16.20.2)
- **Config:** `/home/democrm/.vscode/settings.json`
- **Server Name:** `democrm-context`

### Commands:
```bash
# Connect via SSH
ssh -p 222 democrm@159.203.116.150

# Test MCP server
/usr/bin/node /home/democrm/.zencoder/mcp-server/index.js

# Check Node.js version
/usr/bin/node --version

# View MCP config
cat /home/democrm/.vscode/settings.json
```

---

## ✅ Next Steps

### On Current Machine:
1. ✅ Enable VS Code Settings Sync (if not already enabled)
2. ✅ Reload VS Code to activate MCP server
3. ✅ Test MCP server in Zencoder chat

### On Ubuntu Laptop:
1. ⏳ Download setup files from remote server
2. ⏳ Run `ubuntu-laptop-setup.sh`
3. ⏳ Connect to remote server via VS Code Remote-SSH
4. ⏳ Open folder: `/home/democrm`
5. ⏳ Test MCP server in Zencoder chat

---

**Last Updated:** 2025-01-XX
**Status:** ✅ Ready for Ubuntu laptop setup