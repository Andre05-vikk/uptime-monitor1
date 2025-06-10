#!/bin/bash

# Uptime Monitor - Start Script
# Käivitab PHP serveri ja avab brauseri

echo "🚀 Starting Uptime Monitor..."

# Check if port 8000 is already in use
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "⚠️  Port 8000 is already in use. Stopping existing process..."
    lsof -ti:8000 | xargs kill -9 2>/dev/null || true
    sleep 2
fi

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Clear any existing PHP sessions
echo "🧹 Clearing old sessions..."
rm -rf /tmp/sess_* 2>/dev/null || true

# Start PHP development server in background
echo "🔧 Starting PHP server on localhost:8000..."
php -S localhost:8000 > server.log 2>&1 &
SERVER_PID=$!

# Wait a moment for server to start
sleep 3

# Check if server started successfully
if ! lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "❌ Failed to start PHP server. Check server.log for details."
    exit 1
fi

echo "✅ PHP server started successfully (PID: $SERVER_PID)"
echo "📊 Server running at: http://localhost:8000"
echo "📝 Server logs: server.log"

# Open browser (works on macOS)
if command -v open >/dev/null 2>&1; then
    echo "🌐 Opening browser with fresh session..."
    open "http://localhost:8000?fresh=1"
else
    echo "🌐 Please open http://localhost:8000?fresh=1 in your browser"
fi

echo ""
echo "📋 Default login credentials:"
echo "   Username: admin"
echo "   Password: admin123"
echo ""
echo "⏹️  To stop the server, run: ./stop.sh"
echo "   Or press Ctrl+C if running in foreground"

# Keep script running to show logs
echo "📊 Server is running. Press Ctrl+C to stop..."
echo "📄 Server logs:"
echo "----------------------------------------"

# Follow server logs
trap 'echo ""; echo "🛑 Stopping server..."; kill $SERVER_PID 2>/dev/null; exit 0' INT

tail -f server.log
