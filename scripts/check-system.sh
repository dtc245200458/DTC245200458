#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

compose=(
  docker compose
  -f docker-compose.yml
  -f docker-compose.monitoring.yml
  -f docker-compose.logging.yml
)

date -Is
"${compose[@]}" config --quiet
"${compose[@]}" ps
"${compose[@]}" exec -T nginx nginx -t

headers="$(curl -fsSI --max-time 20 http://localhost/)"
printf '%s\n' "$headers"
printf '%s\n' "$headers" | grep -q '^HTTP/1.1 200'
for header in X-Content-Type-Options X-Frame-Options Referrer-Policy; do
  printf '%s\n' "$headers" | grep -qi "^$header:"
done
if printf '%s\n' "$headers" | grep -qi '^X-Powered-By:'; then
  echo "LOI: van con X-Powered-By"
  exit 1
fi

code="$(curl -sS -L --max-time 20 -o /dev/null \
  -w '%{http_code}' http://127.0.0.1:8081/)"
echo "phpMyAdmin HTTP=$code"
test "$code" = 200

curl -fsS --max-time 10 http://127.0.0.1:9090/-/ready
curl -fsS --max-time 10 http://127.0.0.1:3100/ready

curl -fsS --max-time 10 http://127.0.0.1:3000/api/health |
python3 -c '
import json, sys
d = json.load(sys.stdin)
assert d.get("database") == "ok", d
print("GRAFANA: OK, version=" + d["version"])
'

curl -fsS --max-time 15 --get \
  http://127.0.0.1:9090/api/v1/query \
  --data-urlencode 'query=probe_success{job="longtech_http"}' |
python3 -c '
import json, sys
d = json.load(sys.stdin)
r = d.get("data", {}).get("result", [])
assert d.get("status") == "success" and r, d
assert all(float(x["value"][1]) == 1 for x in r), r
print("HTTP PROBE: OK")
'

"${compose[@]}" exec -T wordpress php -r '
require "/var/www/html/wp-load.php";
$c = wp_count_posts("lt_product");
$n = (int) ($c->publish ?? 0);
echo "THEME=", get_stylesheet(), PHP_EOL;
echo "PUBLISHED_PRODUCTS=", $n, PHP_EOL;
exit($n === 15 && get_stylesheet() === "longtech-ready" ? 0 : 1);
'

git check-ignore -v .env data/grafana-admin-password
df -h /
free -h
echo "KIEM TRA HE THONG: DAT"
