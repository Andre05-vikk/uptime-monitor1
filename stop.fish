#!/opt/homebrew/bin/fish

echo "🛑 Stopping Uptime Monitor system..."

# Kontrolli, kas port 8080 on aktiivne
set PIDS (lsof -ti :8080)

if test (count $PIDS) -gt 0
    for pid in $PIDS
        echo "� Killing PHP server PID $pid"
        kill -9 $pid 2>/dev/null
    end
    sleep 2
    if not lsof -Pi :8080 -sTCP:LISTEN -t >/dev/null 2>&1
        echo "✅ PHP server stopped successfully"
    else
        echo "⚠️  PHP server may still be running"
    end
else
    echo "ℹ️ No PHP server running on port 8080"
end

# Peata automaatne monitooring
echo "🔧 Stopping automatic monitoring..."
pkill -f "auto-monitor.fish" 2>/dev/null; or true
pkill -f "monitor.php" 2>/dev/null; or true
sleep 1
echo "✅ Monitoring stopped"

# Logifailide kustutamine (kui eksisteerivad)
if test -f "server.log"
    echo "🧹 Cleaning up server.log"
    rm -f server.log
end

if test -f "auto-monitor.log"
    echo "🧹 Cleaning up auto-monitor.log"
    rm -f auto-monitor.log
end

echo "🏁 System stopped completely!"
