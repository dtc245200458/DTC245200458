#!/usr/bin/env python3
import json
import math
import subprocess
import time
from urllib.parse import urlencode
from urllib.request import urlopen

def api(url, params=None):
    if params:
        url += "?" + urlencode(params)
    with urlopen(url, timeout=20) as response:
        body = json.load(response)
    if body.get("status") != "success":
        raise RuntimeError("API khong thanh cong")
    return body["data"]

compose = ["docker", "compose"]
for name in (
    "docker-compose.yml",
    "docker-compose.monitoring.yml",
    "docker-compose.logging.yml",
    "docker-compose.database-metrics.yml",
    "docker-compose.container-metrics.yml",
):
    compose += ["-f", name]

subprocess.run(compose + ["config", "--quiet"], check=True)

def services(args):
    return set(subprocess.check_output(
        compose + args, text=True
    ).split())

expected = services(["config", "--services"])
running = services(["ps", "--services", "--status", "running"])
missing = expected - running
if missing:
    raise RuntimeError(f"Dich vu chua chay: {sorted(missing)}")
print(f"DAT: {len(expected)} dich vu dang chay")

synced = subprocess.check_output(
    ["timedatectl", "show", "-p", "NTPSynchronized", "--value"],
    text=True,
).strip()
if synced != "yes":
    raise RuntimeError("Dong ho chua dong bo")
print("DAT: Dong ho dong bo")

targets = api("http://127.0.0.1:9090/api/v1/targets")["activeTargets"]
for job in (
    "prometheus", "longtech_http", "longtech_nginx",
    "longtech_mysql", "longtech_containers",
):
    matches = [t for t in targets if t.get("labels", {}).get("job") == job]
    if not matches or any(
        t.get("health") != "up" or t.get("lastError") for t in matches
    ):
        raise RuntimeError(f"Target loi: {job}")
    print(f"DAT: target {job}")

checks = [
    ("HTTP", 'probe_success{job="longtech_http"}', True),
    ("Nginx", 'nginx_up{job="longtech_nginx"}', True),
    ("MySQL", 'mysql_up{job="longtech_mysql"}', True),
    ("cAdvisor", 'up{job="longtech_containers"}', True),
    ("CPU", 'sum by(service)(rate(container_cpu_usage_seconds_total{job="longtech_containers",cpu="total"}[2m]))', False),
    ("RAM", 'sum by(service)(container_memory_working_set_bytes{job="longtech_containers"})', False),
    ("Nginx request/s", 'rate(nginx_http_requests_total{job="longtech_nginx"}[2m])', False),
    ("Nginx connections", 'nginx_connections_active{job="longtech_nginx"}', False),
    ("MySQL connections", 'mysql_global_status_threads_connected{job="longtech_mysql"}', False),
    ("MySQL questions/s", 'rate(mysql_global_status_questions{job="longtech_mysql"}[2m])', False),
]

for name, expression, status in checks:
    rows = api("http://127.0.0.1:9090/api/v1/query",
               {"query": expression})["result"]
    if not rows:
        raise RuntimeError(f"Thieu du lieu: {name}")
    values = [float(r["value"][1]) for r in rows]
    if not all(math.isfinite(v) for v in values):
        raise RuntimeError(f"Gia tri khong hop le: {name}")
    if status and any(v != 1 for v in values):
        raise RuntimeError(f"Trang thai loi: {name}")
    if name in ("CPU", "RAM"):
        found = {r["metric"].get("service") for r in rows}
        if expected - found:
            raise RuntimeError(f"{name} thieu: {sorted(expected - found)}")
    print(f"DAT: {name} — {len(rows)} chuoi")

marker = f"longtech_final_check={time.time_ns()}"
with urlopen("http://localhost/?" + marker, timeout=20) as response:
    if response.status != 200:
        raise RuntimeError("Website khong tra HTTP 200")

for attempt in range(6):
    rows = api("http://127.0.0.1:3100/loki/api/v1/query_range", {
        "query": '{job="longtech",service="nginx"} |= "' + marker + '"',
        "start": str(time.time_ns() - 300_000_000_000),
        "end": str(time.time_ns()),
        "limit": "20",
    })["result"]
    if any(r.get("values") for r in rows):
        print("DAT: Request moi da vao Loki")
        break
    if attempt == 5:
        raise RuntimeError("Chua tim thay request moi trong Loki")
    time.sleep(5)

tracked = set(subprocess.check_output(
    ["git", "ls-files"], text=True
).splitlines())
for secret in (
    ".env", "data/grafana-admin-password",
    "data/mysql-exporter.cnf", "data/mysql-exporter-password",
):
    subprocess.run(["git", "check-ignore", "--quiet", secret], check=True)
    if secret in tracked:
        raise RuntimeError(f"Secret bi Git theo doi: {secret}")
print("DAT: Secret duoc Git bo qua")
print("KIEM TRA BO SUNG: DAT")
