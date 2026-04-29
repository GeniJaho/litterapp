#!/bin/sh
# Wrapper for the Laravel Nightwatch agent.
# Exits cleanly when NIGHTWATCH_TOKEN is unset so non-prod images don't loop.
set -e

if [ -z "${NIGHTWATCH_TOKEN}" ]; then
    echo "[nightwatch-agent] NIGHTWATCH_TOKEN not set; agent disabled."
    exit 0
fi

exec php /app/artisan nightwatch:agent
