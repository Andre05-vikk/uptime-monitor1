#!/bin/bash
# Setup script for Uptime Monitor
# Creates necessary data files from examples

echo "🔧 Setting up Uptime Monitor data files..."

# Check if files exist, if not create from examples
if [ ! -f "alerts.json" ]; then
    cp alerts.json.example alerts.json
    echo "✅ Created alerts.json"
fi

if [ ! -f "monitors.json" ]; then
    cp monitors.json.example monitors.json
    echo "✅ Created monitors.json"
fi

if [ ! -f "users.json" ]; then
    cp users.json.example users.json
    echo "✅ Created users.json"
fi

if [ ! -f "monitor_status.json" ]; then
    cp monitor_status.json.example monitor_status.json
    echo "✅ Created monitor_status.json"
fi

if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "✅ Created .env"
    echo "⚠️  Please edit .env file with your Brevo API key!"
fi

echo "🎉 Setup complete! Make sure to configure your .env file."
