#!/opt/homebrew/bin/fish

# Uptime Monitor - Stop Script (Fish Shell)
# Peatab PHP serveri

echo "🛑 Stopping Uptime Monitor server..."

# Kill all processes using port 8000
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1
    echo "🔧 Stopping PHP server on port 8000..."
    lsof -ti:8000 | xargs kill -9 2>/dev/null
    sleep 2
    
    # Verify server stopped
    if not lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1
        echo "✅ Server stopped successfully"
    else
        echo "⚠️  Server may still be running"
    end
else
    echo "ℹ️  No server running on port 8000"
end

# Clean up log files if they exist
if test -f "server.log"
    echo "🧹 Cleaning up server.log"
    rm -f server.log
end

echo "🏁 Done!"
