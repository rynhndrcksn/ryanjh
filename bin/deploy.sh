#!/usr/bin/env bash
# Pulls new image for ryanjh, runs migrations, and warms up the cache.

set -e

echo "Pulling latest image..."
docker compose pull ryanjh

echo "Turning off containers..."
docker compose down

echo "Removing volumes..."
docker volume rm srv_public-volume

echo "Restarting containers..."
docker compose up -d

echo "Waiting for app to be ready..."
timeout=30
elapsed=0
while [ $elapsed -lt $timeout ]; do
    health_status=$(docker inspect --format='{{.State.Health.Status}}' ryanjh-app 2>/dev/null || echo "none")

    if [ "$health_status" = "healthy" ]; then
        break
    fi

    echo "Current status: $health_status (${elapsed}s elapsed)"
    sleep 2
    elapsed=$((elapsed + 2))
done

if [ $elapsed -ge $timeout ]; then
    echo "Timeout waiting for container to be ready"
    exit 1
fi

echo "Running migrations..."
docker compose exec -T ryanjh php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing --allow-no-migration

echo "Warming up cache..."
docker compose exec -T ryanjh php bin/console cache:warmup
