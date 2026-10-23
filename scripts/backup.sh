#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."
umask 077

BACKUP_DIR="$(mktemp -d "$HOME/longtech-backups/longtech-$(date +%Y%m%d-%H%M%S)-XXXXXX")"
echo "BACKUP_DIR=$BACKUP_DIR"

docker compose -f docker-compose.yml exec -T db sh -c '
set -eu
: "${MYSQL_ROOT_PASSWORD:?Missing MYSQL_ROOT_PASSWORD}"
: "${MYSQL_DATABASE:?Missing MYSQL_DATABASE}"
MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqldump \
  -u root \
  --single-transaction \
  --quick \
  --no-tablespaces \
  --set-gtid-purged=OFF \
  --databases "$MYSQL_DATABASE"
' > "$BACKUP_DIR/database.sql.part"

test -s "$BACKUP_DIR/database.sql.part"
grep -q '^-- Dump completed on ' "$BACKUP_DIR/database.sql.part"
mv "$BACKUP_DIR/database.sql.part" "$BACKUP_DIR/database.sql"

docker compose -f docker-compose.yml exec -T wordpress \
  tar -czf - -C /var/www/html . \
  > "$BACKUP_DIR/wordpress-files.tar.gz.part"

gzip -t "$BACKUP_DIR/wordpress-files.tar.gz.part"
mv "$BACKUP_DIR/wordpress-files.tar.gz.part" \
   "$BACKUP_DIR/wordpress-files.tar.gz"

tar -czf "$BACKUP_DIR/project-config.tar.gz" \
  .env .env.example .gitignore README.md \
  docker-compose.yml \
  docker-compose.monitoring.yml \
  docker-compose.logging.yml \
  nginx blackbox prometheus grafana loki promtail scripts docs wordpress \
  data/grafana-admin-password

gzip -t "$BACKUP_DIR/project-config.tar.gz"

git bundle create "$BACKUP_DIR/repository.bundle" --all
git bundle verify "$BACKUP_DIR/repository.bundle"

docker compose -f docker-compose.yml exec -T wordpress php -r '
require "/var/www/html/wp-load.php";
$c = wp_count_posts("lt_product");
echo json_encode([
    "theme" => get_stylesheet(),
    "siteurl" => get_option("siteurl"),
    "published_products" => (int) ($c->publish ?? 0),
    "wordpress_version" => get_bloginfo("version")
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
' > "$BACKUP_DIR/site-manifest.json"

cat > "$BACKUP_DIR/BACKUP-NOTES.txt" <<'NOTES'
Phạm vi:
- database.sql: database WordPress.
- wordpress-files.tar.gz: toàn bộ /var/www/html.
- project-config.tar.gz: cấu hình, mã nguồn, minh chứng và thông tin đăng nhập.
- repository.bundle: lịch sử Git đã commit.
- site-manifest.json: dữ liệu đối chiếu khi khôi phục.

Chưa sao lưu lịch sử metrics Prometheus, log Loki hoặc database Grafana.
Dashboard và datasource được lưu dưới dạng provisioning trong project-config.
Khôi phục Grafana mới sẽ dùng mật khẩu khởi tạo trong tệp secret.

Tệp nén và SHA-256 đã được kiểm tra; chưa đồng nghĩa khôi phục thành công.
Bản sao lưu chứa mật khẩu và dữ liệu riêng tư. Không đưa lên GitHub công khai.
NOTES

(
  cd "$BACKUP_DIR"
  sha256sum \
    database.sql \
    wordpress-files.tar.gz \
    project-config.tar.gz \
    repository.bundle \
    site-manifest.json \
    BACKUP-NOTES.txt > SHA256SUMS
  sha256sum -c SHA256SUMS
)

printf '%s\n' "$BACKUP_DIR" > data/latest-backup-path
echo "SAO LUU HOAN TAT: $BACKUP_DIR"
du -sh "$BACKUP_DIR"
