#!/usr/bin/env bash
set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"
if [ ! -f deploy.env ]; then
  echo "Error: deploy.env not found. Copy deploy.env.format to deploy.env and fill in values."
  exit 1
fi
source deploy.env
IMAGE="${AR_HOSTNAME}/${AR_PROJECT_ID}/${AR_IMAGE_PATH}:${IMAGE_TAG}"
docker build -t "$IMAGE" .
docker push "$IMAGE"
echo "Image pushed. Run ./deploy.sh to deploy to Cloud Run."
