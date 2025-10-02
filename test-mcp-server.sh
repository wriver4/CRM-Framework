#!/bin/bash

# Test MCP Server Configuration
# This script verifies the MCP server is properly configured

echo "=================================================="
echo "MCP Server Configuration Test"
echo "=================================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Check Node.js
echo -e "${YELLOW}Test 1: Checking Node.js...${NC}"
if [ -f /usr/bin/node ]; then
    NODE_VERSION=$(/usr/bin/node --version 2>&1)
    echo -e "${GREEN}✓ Node.js found: $NODE_VERSION${NC}"
else
    echo -e "${RED}✗ Node.js not found at /usr/bin/node${NC}"
    exit 1
fi
echo ""

# Test 2: Check MCP Server file
echo -e "${YELLOW}Test 2: Checking MCP Server file...${NC}"
if [ -f /home/democrm/.zencoder/mcp-server/index.js ]; then
    echo -e "${GREEN}✓ MCP Server file exists${NC}"
    ls -lh /home/democrm/.zencoder/mcp-server/index.js
else
    echo -e "${RED}✗ MCP Server file not found${NC}"
    exit 1
fi
echo ""

# Test 3: Check node_modules
echo -e "${YELLOW}Test 3: Checking dependencies...${NC}"
if [ -d /home/democrm/.zencoder/mcp-server/node_modules ]; then
    echo -e "${GREEN}✓ node_modules directory exists${NC}"
    
    # Check for MCP SDK
    if [ -d /home/democrm/.zencoder/mcp-server/node_modules/@modelcontextprotocol ]; then
        echo -e "${GREEN}✓ @modelcontextprotocol/sdk installed${NC}"
    else
        echo -e "${RED}✗ @modelcontextprotocol/sdk not found${NC}"
        echo -e "${YELLOW}  Run: cd /home/democrm/.zencoder/mcp-server && npm install${NC}"
    fi
else
    echo -e "${RED}✗ node_modules not found${NC}"
    echo -e "${YELLOW}  Run: cd /home/democrm/.zencoder/mcp-server && npm install${NC}"
    exit 1
fi
echo ""

# Test 4: Check VS Code configuration
echo -e "${YELLOW}Test 4: Checking VS Code configuration...${NC}"
if [ -f /home/democrm/.vscode/settings.json ]; then
    echo -e "${GREEN}✓ VS Code settings.json exists${NC}"
    
    # Check if MCP server is configured
    if grep -q "democrm-context" /home/democrm/.vscode/settings.json; then
        echo -e "${GREEN}✓ MCP server 'democrm-context' configured${NC}"
        echo ""
        echo "Configuration:"
        cat /home/democrm/.vscode/settings.json
    else
        echo -e "${RED}✗ MCP server not configured in settings.json${NC}"
    fi
else
    echo -e "${RED}✗ VS Code settings.json not found${NC}"
    exit 1
fi
echo ""

# Test 5: Test MCP Server startup (quick test)
echo -e "${YELLOW}Test 5: Testing MCP Server startup...${NC}"
echo -e "${YELLOW}(This will run for 2 seconds then timeout)${NC}"
echo ""

# Run the server with a timeout
timeout 2 /usr/bin/node /home/democrm/.zencoder/mcp-server/index.js 2>&1 &
SERVER_PID=$!

# Wait a moment for startup
sleep 1

# Check if process is still running
if ps -p $SERVER_PID > /dev/null 2>&1; then
    echo -e "${GREEN}✓ MCP Server started successfully${NC}"
    echo -e "${GREEN}✓ Server is running on stdio (as expected)${NC}"
    
    # Kill the process
    kill $SERVER_PID 2>/dev/null
    wait $SERVER_PID 2>/dev/null
else
    # Check exit code
    wait $SERVER_PID 2>/dev/null
    EXIT_CODE=$?
    
    if [ $EXIT_CODE -eq 124 ]; then
        # Timeout (expected - server was running)
        echo -e "${GREEN}✓ MCP Server ran successfully (timeout as expected)${NC}"
    elif [ $EXIT_CODE -eq 0 ]; then
        echo -e "${YELLOW}⚠ Server exited with code 0 (may need stdio connection)${NC}"
        echo -e "${YELLOW}  This is normal - MCP servers need a client connection${NC}"
    else
        echo -e "${RED}✗ Server exited with code $EXIT_CODE${NC}"
        echo -e "${YELLOW}  Check logs for errors${NC}"
    fi
fi
echo ""

# Test 6: Check systemd service status
echo -e "${YELLOW}Test 6: Checking systemd service status...${NC}"
if systemctl is-active --quiet mcp-server.service 2>/dev/null; then
    echo -e "${RED}✗ WARNING: mcp-server.service is running${NC}"
    echo -e "${YELLOW}  MCP servers should NOT run as systemd services${NC}"
    echo -e "${YELLOW}  They are spawned by Zencoder when needed${NC}"
    echo -e "${YELLOW}  Run: sudo systemctl stop mcp-server.service${NC}"
    echo -e "${YELLOW}       sudo systemctl disable mcp-server.service${NC}"
else
    echo -e "${GREEN}✓ mcp-server.service is not running (correct)${NC}"
fi
echo ""

# Summary
echo "=================================================="
echo -e "${GREEN}Configuration Test Complete!${NC}"
echo "=================================================="
echo ""
echo "Summary:"
echo "  • Node.js: $NODE_VERSION"
echo "  • MCP Server: /home/democrm/.zencoder/mcp-server/index.js"
echo "  • Configuration: /home/democrm/.vscode/settings.json"
echo "  • Server Name: democrm-context"
echo ""
echo "Next Steps:"
echo "  1. Reload VS Code: Ctrl+Shift+P → 'Developer: Reload Window'"
echo "  2. Open Zencoder Chat: Ctrl+Shift+P → 'Zencoder: Open Chat'"
echo "  3. Test: Ask 'Can you use get_repo_context to show the repository?'"
echo ""
echo "=================================================="