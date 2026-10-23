#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

compose=(
  docker compose
  -f docker-compose.yml
  -f docker-compose.monitoring.yml
  -f docker-compose.logging.yml
  -f docker-compose.database-metrics.yml
)

test -s data/mysql-exporter.cnf
"${compose[@]}" config --quiet

"${compose[@]}" up -d --pull never \
  db wordpress nginx phpmyadmin \
  prometheus blackbox nginx-exporter mysql-exporter grafana loki

python3 scripts/refresh-log-targets.py

"${compose[@]}" up -d --pull never promtail
"${compose[@]}" ps
