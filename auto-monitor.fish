#!/usr/bin/env fish
# Automatic monitoring script for Fish shell
# Runs monitor.php every 30 seconds

# Get absolute path to script directory
set SCRIPT_DIR (dirname (realpath (status --current-filename)))
set PROJECT_DIR $SCRIPT_DIR
set LOG_FILE "$PROJECT_DIR/auto-monitor.log"

echo "🚀 Starting automatic monitoring (30-second intervals)..." | tee -a $LOG_FILE
echo "Started at: "(date) | tee -a $LOG_FILE
echo "Press Ctrl+C to stop" | tee -a $LOG_FILE
echo "Log file: $LOG_FILE" | tee -a $LOG_FILE
echo "Monitor logs: $PROJECT_DIR/monitor.log" | tee -a $LOG_FILE
echo "===========================================" | tee -a $LOG_FILE

# Function to run monitoring
function run_monitoring
    cd $PROJECT_DIR
    
    # Capture output and only log if there are significant events
    set monitor_output (php monitor.php 2>&1)
    set exit_code $status
    
    # Only log if there's actual output (status changes, errors, etc.)
    if test -n "$monitor_output"
        echo "["(date '+%Y-%m-%d %H:%M:%S')"] Monitoring events:" >> $LOG_FILE
        echo "$monitor_output" | while read -l line
            echo "  $line" >> $LOG_FILE
        end
        echo "---" >> $LOG_FILE
    end
    
    # Only log errors or first run of the day
    if test $exit_code -ne 0
        echo "["(date '+%Y-%m-%d %H:%M:%S')"] ⚠️ Monitoring completed with warnings (exit code: $exit_code)" | tee -a $LOG_FILE
    end
end

# Handle cleanup on exit
function cleanup --on-signal SIGINT --on-signal SIGTERM
    echo "" | tee -a $LOG_FILE
    echo "["(date '+%Y-%m-%d %H:%M:%S')"] 🛑 Automatic monitoring stopped (received signal)" | tee -a $LOG_FILE
    echo "Total checks performed: $check_count" | tee -a $LOG_FILE
    echo "===========================================" | tee -a $LOG_FILE
    exit 0
end

# Infinite loop with 30-second intervals
set check_count 0
while true
    run_monitoring
    set check_count (math $check_count + 1)
    
    # Show periodic status every 120 checks (1 hour)
    if test (math $check_count % 120) -eq 0
        echo "["(date '+%Y-%m-%d %H:%M:%S')"] ℹ️ Monitoring active - completed $check_count checks" >> $LOG_FILE
    end
    
    sleep 30
end
