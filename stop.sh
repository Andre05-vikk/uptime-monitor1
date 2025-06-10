#!/bin/bash

# Uptime Monitor - Stop Script
# Peatab PHP serveri

echo "🛑 Stopping Uptime Monitor server..."

# Kill all processes using port 8000
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "🔧 Stopping PHP server on port 8000..."
    lsof -ti:8000 | xargs kill -9 2>/dev/null
    sleep 2
    
    # Verify server stopped
    if ! lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1; then
        echo "✅ Server stopped successfully"
    else
        echo "⚠️  Server may still be running"
    fi
else
    echo "ℹ️  No server running on port 8000"
fi

# Clean up log files if they exist
if [ -f "server.log" ]; then
    echo "🧹 Cleaning up server.log"
    rm -f server.log
fi

echo "🏁 Done!"
