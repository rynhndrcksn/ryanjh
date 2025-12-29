#!/usr/bin/env bash
# Handles pulling the freshest image, spinning down any running instance, and starting the latest one.
# Expects you to add APP_ENV and APP_RUNTIME_ENV to the .bashrc file on the `stg`/`prod` servers.

set -e

APP_ENV=${RYANJH_APP_ENV:-prod}
APP_RUNTIME_ENV=${APP_RUNTIME_ENV:-$APP_ENV}
IMAGE="ghcr.io/rynhndrcksn/ryanjh:latest"
CONTAINER_NAME="ryanjh-${APP_RUNTIME_ENV}"
ENV_FILE="/srv/ryanjh/.env.${APP_RUNTIME_ENV}.local"
PUBLIC_DIR="/srv/ryanjh/public"
POD_NAME="ryanjh"
APP_NETWORK="app-network"

echo "========================================"
echo "Deploying Ryanjh - Environment: $APP_RUNTIME_ENV"
echo "========================================"

# Verify .env file exists
if [ ! -f "$ENV_FILE" ]; then
    echo "❌ ERROR: Environment file not found at $ENV_FILE"
    exit 1
fi

echo "→ Pulling latest image..."
podman pull "$IMAGE"

echo "→ Stopping old container..."
podman stop "$CONTAINER_NAME" 2>/dev/null || true
podman rm "$CONTAINER_NAME" 2>/dev/null || true

if ! podman pod exists "$POD_NAME"; then
    echo "→ Creating pod..."
    if ! podman network exists "$APP_NETWORK"; then
        echo "→ Creating $APP_NETWORK..."
        podman network create "$APP_NETWORK"
    fi
    podman pod create --name "$POD_NAME" --network "$APP_NETWORK" -p 127.0.0.1:9000:9000
fi

echo "→ Starting container..."
podman run -d \
    --name "$CONTAINER_NAME" \
    --pod ryanjh \
    -v "$ENV_FILE:/app/.env.${APP_RUNTIME_ENV}.local:ro" \
    -v "$PUBLIC_DIR":/app/public:rw \
    -e APP_ENV="$APP_ENV" \
    -e APP_RUNTIME_ENV="$APP_RUNTIME_ENV" \
    "$IMAGE"

sleep 3

echo "→ Clearing and warming cache..."
podman exec "$CONTAINER_NAME" php -d memory_limit=-1 bin/console cache:clear --env="$APP_RUNTIME_ENV"

echo "→ Running migrations..."
podman exec "$CONTAINER_NAME" php bin/console doctrine:migrations:migrate --allow-no-migration --all-or-nothing --no-interaction --env="$APP_RUNTIME_ENV"

echo "✓ Deployment complete!"
echo "Logs: podman logs -f $CONTAINER_NAME"
