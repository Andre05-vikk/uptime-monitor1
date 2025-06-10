#!/opt/homebrew/bin/fish

# Uptime Monitor - Start Script (Fish Shell)
# Käivitab PHP serveri ja avab brauseri

echo "🚀 Starting Uptime Monitor..."

# Check if port 8000 is already in use
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1
    echo "⚠️  Port 8000 is already in use. Stopping existing process..."
    lsof -ti:8000 | xargs kill -9 2>/dev/null; or true
    sleep 2
end

# Get current directory
set SCRIPT_DIR (dirname (status --current-filename))
cd $SCRIPT_DIR

# Clear any existing PHP sessions
echo "🧹 Clearing old sessions..."
if test -d /tmp
    rm -f /tmp/sess_* 2>/dev/null; or true
end
# Also clear local session files if they exist
rm -f .sessions/* 2>/dev/null; or true

# Start PHP development server in background
echo "🔧 Starting PHP server on localhost:8000..."
php -S localhost:8000 > server.log 2>&1 &
set SERVER_PID $last_pid

# Wait a moment for server to start
sleep 3

# Check if server started successfully
if not lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1
    echo "❌ Failed to start PHP server. Check server.log for details."
    exit 1
end

echo "✅ PHP server started successfully (PID: $SERVER_PID)"
echo "📊 Server running at: http://localhost:8000"
echo "📝 Server logs: server.log"

# Open browser (works on macOS)
if command -v open >/dev/null 2>&1
    echo "🌐 Opening browser with fresh session..."
    open "http://localhost:8000?fresh=1"
else
    echo "🌐 Please open http://localhost:8000?fresh=1 in your browser"
end

echo ""
echo "📋 Default login credentials:"
echo "   Username: admin"
echo "   Password: admin123"
echo ""
echo "⏹️  To stop the server, run: ./stop.fish"
echo "   Or press Ctrl+C if running in foreground"

# Keep script running to show logs
echo "📊 Server is running. Press Ctrl+C to stop..."
echo "📄 Server logs:"
echo "----------------------------------------"

# Set up trap for Ctrl+C
function cleanup
    echo ""
    echo "🛑 Stopping server..."
    kill $SERVER_PID 2>/dev/null; or true
    exit 0
end

trap cleanup INT

# Follow server logs
tail -f server.log
