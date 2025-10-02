# Ubuntu Laptop Setup Guide for MCP Server Access

## Overview
This guide will help you set up your Ubuntu laptop to access the MCP server running on the remote CentOS server (159.203.116.150).

---

## Part 1: Enable VS Code Settings Sync (Current Machine)

### On Your Current Machine (where you're working now):

1. **Open VS Code Settings Sync**
   - Press `Ctrl+Shift+P` (or `Cmd+Shift+P` on Mac)
   - Type: `Settings Sync: Turn On`
   - Sign in with your Microsoft or GitHub account

2. **Verify Sync is Enabled**
   - Click the gear icon (⚙️) in the bottom-left corner
   - Look for a checkmark next to "Settings Sync is On"
   - Click "Settings Sync is On" to see what's being synced

3. **Ensure These Items Are Synced:**
   - ✅ Settings
   - ✅ Extensions
   - ✅ Keyboard Shortcuts (optional)
   - ✅ UI State (optional)

**Important Note:** The MCP server configuration in `.vscode/settings.json` is workspace-specific and won't sync automatically. We'll handle this separately below.

---

## Part 2: SSH Setup for Ubuntu Laptop

### On Your Ubuntu Laptop:

### 1. Install Required Software

```bash
# Update package list
sudo apt update

# Install VS Code (if not already installed)
sudo snap install code --classic

# Install SSH client (usually pre-installed)
sudo apt install openssh-client -y

# Install Node.js (for local MCP server if needed)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2. Set Up SSH Access to Remote Server

```bash
# Create SSH directory if it doesn't exist
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Generate SSH key (if you don't have one)
ssh-keygen -t ed25519 -C "your_email@example.com"
# Press Enter to accept default location
# Enter a passphrase (recommended) or leave empty

# Copy your public key to the remote server
ssh-copy-id -p 222 democrm@159.203.116.150

# Test SSH connection
ssh -p 222 democrm@159.203.116.150
```

### 3. Configure SSH for Easy Access

Create/edit `~/.ssh/config`:

```bash
nano ~/.ssh/config
```

Add this configuration:

```
Host democrm-server
    HostName 159.203.116.150
    Port 222
    User democrm
    ServerAliveInterval 60
    ServerAliveCountMax 3
```

Save and exit (`Ctrl+X`, then `Y`, then `Enter`).

Now you can connect with just: `ssh democrm-server`

### 4. Install VS Code Remote-SSH Extension

```bash
# Install from command line
code --install-extension ms-vscode-remote.remote-ssh

# Or install from VS Code:
# 1. Open VS Code
# 2. Press Ctrl+Shift+X
# 3. Search for "Remote - SSH"
# 4. Click Install
```

### 5. Install Zencoder Extension

```bash
# Install from command line
code --install-extension zencoder.zencoder

# Or install from VS Code Extensions panel
```

---

## Part 3: Connect to Remote Server from Ubuntu Laptop

### Method A: Using VS Code Remote-SSH (Recommended)

1. **Open VS Code on Ubuntu Laptop**

2. **Connect to Remote Server**
   - Press `Ctrl+Shift+P`
   - Type: `Remote-SSH: Connect to Host`
   - Select `democrm-server` (or type `democrm@159.203.116.150`)
   - Enter port `222` if prompted
   - Enter your SSH password or passphrase

3. **Open Remote Workspace**
   - Once connected, click "Open Folder"
   - Navigate to `/home/democrm`
   - Click OK

4. **Verify MCP Configuration**
   - Open `.vscode/settings.json` in the remote workspace
   - You should see the MCP server configuration:
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

5. **Reload VS Code**
   - Press `Ctrl+Shift+P`
   - Type: `Developer: Reload Window`

### Method B: Using SFTP Mount (Alternative)

```bash
# Install sshfs
sudo apt install sshfs -y

# Create mount point
mkdir -p ~/remote/democrm

# Mount remote filesystem
sshfs democrm@159.203.116.150:/home/democrm ~/remote/democrm -p 222

# Open in VS Code
code ~/remote/democrm
```

---

## Part 4: Verify MCP Server is Working

### Test the MCP Server Connection:

1. **Open VS Code on Ubuntu Laptop** (connected to remote server)

2. **Open Zencoder Chat**
   - Press `Ctrl+Shift+P`
   - Type: `Zencoder: Open Chat`

3. **Test MCP Tool**
   - In the chat, type:
   ```
   Can you use the get_repo_context tool to show me the repository structure?
   ```

4. **Check for Success**
   - You should see Zencoder using the `democrm-context` MCP server
   - The tool should return repository information

---

## Part 5: Troubleshooting

### If MCP Server Doesn't Work:

1. **Check Node.js on Remote Server**
   ```bash
   ssh democrm-server
   /usr/bin/node --version
   # Should show: v16.20.2
   ```

2. **Test MCP Server Manually**
   ```bash
   ssh democrm-server
   /usr/bin/node /home/democrm/.zencoder/mcp-server/index.js
   # Should show: MCP Server running on stdio
   # Press Ctrl+C to exit
   ```

3. **Check VS Code Output**
   - In VS Code, press `Ctrl+Shift+U` to open Output panel
   - Select "Zencoder" from the dropdown
   - Look for MCP server connection messages

4. **Verify Settings File**
   ```bash
   ssh democrm-server
   cat /home/democrm/.vscode/settings.json
   ```

### Common Issues:

**Issue:** "Cannot connect to remote server"
- **Solution:** Check SSH connection: `ssh -p 222 democrm@159.203.116.150`
- Verify firewall allows port 222

**Issue:** "MCP server not found"
- **Solution:** Verify file exists: `ls -la /home/democrm/.zencoder/mcp-server/index.js`

**Issue:** "Node.js not found"
- **Solution:** Check Node.js path: `which node` on remote server

**Issue:** "Permission denied"
- **Solution:** Check file permissions:
  ```bash
  chmod +x /home/democrm/.zencoder/mcp-server/index.js
  ```

---

## Part 6: Alternative - Run MCP Server Locally on Ubuntu

If you want to run the MCP server **locally** on your Ubuntu laptop (not recommended for this use case):

### 1. Copy MCP Server Files

```bash
# From Ubuntu laptop
scp -P 222 -r democrm@159.203.116.150:/home/democrm/.zencoder ~/
```

### 2. Create Local VS Code Settings

Create `~/.config/Code/User/settings.json`:

```json
{
  "zencoder.mcpServers": {
    "democrm-context-local": {
      "command": "/usr/bin/node",
      "args": ["/home/YOUR_USERNAME/.zencoder/mcp-server/index.js"]
    }
  }
}
```

**Note:** This will only give you access to local files, not the remote server's repository.

---

## Summary

### Recommended Setup:
1. ✅ Enable VS Code Settings Sync on current machine
2. ✅ Install VS Code + Remote-SSH extension on Ubuntu laptop
3. ✅ Set up SSH access to remote server
4. ✅ Connect to remote server via VS Code Remote-SSH
5. ✅ The `.vscode/settings.json` file will be there automatically
6. ✅ MCP server will work through the remote connection

### What Gets Synced:
- ✅ VS Code extensions (Zencoder will auto-install)
- ✅ User settings (keyboard shortcuts, themes, etc.)
- ❌ Workspace settings (`.vscode/settings.json` - but it's on the remote server, so you'll have it when you connect)

### Benefits:
- 🎯 Single source of truth (remote server)
- 🎯 No file syncing needed
- 🎯 MCP server has access to actual repository
- 🎯 Works from any machine with SSH access

---

## Quick Start Commands for Ubuntu Laptop

```bash
# 1. Install VS Code and extensions
sudo snap install code --classic
code --install-extension ms-vscode-remote.remote-ssh
code --install-extension zencoder.zencoder

# 2. Set up SSH
ssh-copy-id -p 222 democrm@159.203.116.150

# 3. Connect via VS Code
# Press Ctrl+Shift+P → "Remote-SSH: Connect to Host" → democrm@159.203.116.150

# 4. Open folder: /home/democrm

# 5. Reload VS Code and test MCP server
```

---

## Need Help?

If you encounter any issues:
1. Check the Troubleshooting section above
2. Verify SSH connection works: `ssh -p 222 democrm@159.203.116.150`
3. Check VS Code Output panel (Ctrl+Shift+U) → Select "Zencoder"
4. Test MCP server manually on remote server

---

**Last Updated:** 2025-01-XX
**Remote Server:** 159.203.116.150:222
**MCP Server Path:** `/home/democrm/.zencoder/mcp-server/index.js`
**Node.js Path:** `/usr/bin/node` (v16.20.2)