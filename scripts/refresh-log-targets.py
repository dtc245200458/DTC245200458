import json
import subprocess
from pathlib import Path

root = Path(__file__).resolve().parent.parent
names = [
    "longtech-nginx-1",
    "longtech-wordpress-1",
    "longtech-db-1",
    "longtech-grafana-1",
]
targets = []
for name in names:
    raw = subprocess.check_output([
        "docker", "inspect", "--format",
        '{{.LogPath}}|{{index .Config.Labels "com.docker.compose.service"}}|{{.HostConfig.LogConfig.Type}}',
        name,
    ], text=True).strip()
    path, service, driver = raw.split("|")
    if driver != "json-file" or not path.startswith("/var/lib/docker/containers/"):
        raise SystemExit(f"Duong dan/driver khong phu hop: {name}")
    targets.append({
        "targets": ["localhost"],
        "labels": {
            "job": "longtech",
            "service": service,
            "__path__": path,
        },
    })

dest = root / "promtail/targets/containers.json"
temp = dest.with_suffix(".tmp")
temp.write_text(json.dumps(targets, indent=2) + "\n", encoding="utf-8")
temp.replace(dest)
print(f"DA CAP NHAT {len(targets)} NGUON LOG")
