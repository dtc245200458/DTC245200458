# LongTech — Website giới thiệu sản phẩm công nghệ

## 1. Thông tin đề tài

- Môn học: Triển khai và Quản trị Hệ thống Phần mềm.
- Đề tài: Đề 3 — Website Quảng bá Sản phẩm.
- Website WordPress giới thiệu 15 sản phẩm: 5 laptop, 5 điện thoại,
  5 phụ kiện; có trang chi tiết và liên kết nhà sản xuất.
- Phạm vi: giới thiệu sản phẩm, chưa có đặt hàng hoặc thanh toán.

## 2. Kiến trúc

- Nginx: reverse proxy tới WordPress.
- WordPress và MySQL: ứng dụng và cơ sở dữ liệu.
- phpMyAdmin: quản trị database.
- Blackbox Exporter: kiểm tra HTTP website qua Nginx.
- Prometheus: thu thập số liệu mỗi 15 giây.
- Grafana: dashboard HTTP và truy vấn log.
- Promtail: đọc log Docker của Nginx, WordPress, MySQL và Grafana.
- Loki: lưu và truy vấn log tập trung.

Mạng:
- frontend: Nginx, WordPress và Blackbox Exporter.
- backend (internal): MySQL, WordPress và phpMyAdmin.
- admin: mạng bổ sung để phpMyAdmin công bố cổng quản trị.
- monitoring: các dịch vụ giám sát và log.

## 3. Cấu trúc mã nguồn

- docker-compose.yml: ứng dụng, database và reverse proxy.
- docker-compose.monitoring.yml: Prometheus, Grafana, Blackbox Exporter.
- docker-compose.logging.yml: Loki và Promtail.
- wordpress/themes/longtech-ready/: mã nguồn theme và tài nguyên.
- grafana/provisioning/: datasource và dashboard khai báo bằng tệp.
- scripts/start.sh: khởi chạy toàn bộ hệ thống.
- scripts/check-system.sh: kiểm tra hệ thống.
- scripts/backup.sh: sao lưu WordPress, database và cấu hình.
- scripts/refresh-log-targets.py: cập nhật đường dẫn log container.
- docs/evidence/: kết quả kiểm tra thực tế.

## 4. Khởi chạy trên máy hiện tại

Yêu cầu: Docker Engine, Docker Compose, Bash, Python 3, curl,
OpenSSL, Git và các image đã có trên máy.

```bash
cd ~/longtech-product-website
bash scripts/start.sh
bash scripts/check-system.sh
```

Script start dùng --pull never để tránh tự thay image trong lúc demo.
Nếu thiếu image, cần tải trước.

Website hiện tại:
http://192.168.105.130/

Đây là IP của máy triển khai, không phải địa chỉ dùng chung cho mọi máy.

## 5. Truy cập công cụ quản trị từ Windows

Mở CMD hoặc PowerShell trên Windows:

```powershell
ssh -N -o ExitOnForwardFailure=yes -L 3000:127.0.0.1:3000 -L 9090:127.0.0.1:9090 -L 8081:127.0.0.1:8081 thanhlong@192.168.105.130
```

Giữ cửa sổ SSH này mở:
- Grafana: http://localhost:3000/
- Prometheus: http://localhost:9090/
- phpMyAdmin: http://localhost:8081/

Nếu cổng đã bị đường hầm khác sử dụng, đóng đường hầm cũ trước.

Grafana:
- Tài khoản: admin.
- Mật khẩu khởi tạo: data/grafana-admin-password.
- Nếu đã đổi mật khẩu trong Grafana, sử dụng mật khẩu mới.
- Dashboard: http://localhost:3000/d/longtech-overview

phpMyAdmin sử dụng tài khoản database trong cấu hình .env.
Mật khẩu Ubuntu, Grafana và MySQL là các thông tin đăng nhập riêng.

## 6. Dừng và bật lại

```bash
docker compose -f docker-compose.yml \
  -f docker-compose.monitoring.yml \
  -f docker-compose.logging.yml stop
```

Bật lại:

```bash
bash scripts/start.sh
```

Các dịch vụ có restart: unless-stopped.
Sau khi chủ động stop, dùng script start để bật lại.
Sau khi khởi động lại Windows, cần mở lại đường hầm SSH để vào công cụ.
Không dùng down -v nếu cần giữ dữ liệu.

## 7. Giám sát và log

Dashboard gồm:
- Trạng thái kiểm tra HTTP.
- Mã HTTP cuối cùng.
- Thời gian kiểm tra.
- Tỷ lệ mẫu thành công trong một giờ gần nhất.
- Hai biểu đồ lịch sử trạng thái và thời gian kiểm tra.

Đây là phép kiểm tra từ mạng Docker qua Nginx.
Không đo tốc độ tải đầy đủ của trình duyệt, CPU/RAM hoặc đường mạng
từ máy người dùng. Khoảng mất dữ liệu không được tính như mẫu thất bại.

Prometheus giữ dữ liệu theo giới hạn 7 ngày hoặc kích thước 1 GB,
tùy giới hạn nào đạt trước; đây không phải hạn mức tổng dung lượng volume.
Loki được cấu hình retention 7 ngày.

Trong Grafana Explore, chọn Loki để chạy truy vấn tại docs/log-queries.md.

Khi container nguồn được tạo lại, chạy:

```bash
python3 scripts/refresh-log-targets.py
```

Script start đã thực hiện bước này trước khi khởi chạy Promtail.

## 8. Các biện pháp bảo vệ đã áp dụng

- MySQL không công bố cổng ra host; nằm trong mạng backend internal.
- Grafana, Prometheus, Loki, phpMyAdmin chỉ bind cổng host trên 127.0.0.1.
- Truy cập công cụ quản trị từ Windows qua đường hầm SSH.
- Nginx ẩn số phiên bản và header X-Powered-By.
- Các header: X-Content-Type-Options, X-Frame-Options, Referrer-Policy.
- Grafana tắt đăng ký tự do và truy cập ẩn danh.
- .env và data/ được Git bỏ qua.
- Log Docker có xoay vòng: 10 MB/tệp, tối đa 3 tệp mỗi dịch vụ.

Website hiện dùng HTTP, chưa triển khai HTTPS.
Các biện pháp trên không thay thế kiểm thử bảo mật toàn diện.

## 9. Sao lưu

Trong thời gian sao lưu, tránh sửa nội dung/giao diện.

```bash
bash scripts/backup.sh
cat data/latest-backup-path
```

Bản sao lưu nằm trong ~/longtech-backups/, gồm:
- database.sql.
- wordpress-files.tar.gz.
- project-config.tar.gz.
- repository.bundle.
- site-manifest.json.
- BACKUP-NOTES.txt.
- SHA256SUMS.

Có kiểm tra tệp nén, lịch sử Git và SHA-256.
Bản sao lưu chứa mật khẩu và dữ liệu riêng tư; không đưa lên GitHub công khai.

Không sao lưu lịch sử Prometheus/Loki hoặc database Grafana.
Dashboard và datasource có thể dựng lại từ provisioning.
Sao lưu SQL và tệp được thực hiện tuần tự, không phải snapshot đồng thời.

## 10. Kết quả kiểm thử khôi phục

Đã nhập SQL và giải nén WordPress vào volume riêng của project thử,
sau đó khởi động WordPress qua PHP để đối chiếu:

- theme=longtech-ready
- siteurl=http://192.168.105.130
- published_products=15
- wordpress_version=7.1.1

Kết quả: đạt. Minh chứng tại docs/evidence/restore-check-20261023-234231.txt.
Chưa kiểm thử giao diện trình duyệt của môi trường phục hồi.
Container thử được dừng sau kiểm tra; volume thử còn giữ trên máy.

## 11. Triển khai trên máy mới và giới hạn

Clone repository và chạy Compose chưa tự phục hồi website hiện tại.

Cần:
1. Cài các công cụ, tải image phù hợp.
2. Tạo .env từ .env.example và đặt mật khẩu riêng.
3. Tạo secret Grafana tại data/grafana-admin-password.
4. Cài theme hoặc khôi phục tệp WordPress từ bản sao lưu.
5. Khôi phục SQL vào database đúng cấu hình.
6. Điều chỉnh URL WordPress nếu địa chỉ máy thay đổi.
7. Cập nhật log targets và kiểm tra lại hệ thống.

Không ghi đè .env trên máy đang hoạt động.
Không ghi đè volume sản xuất để thử khôi phục.

Các image hiện dùng tag latest/mysql:8.0; chưa khóa toàn bộ bằng digest.
Promtail đã hết vòng đời hỗ trợ; Grafana Alloy là hướng nâng cấp.
Hai volume ứng dụng hiện có thiếu nhãn Compose nên có cảnh báo khi chạy;
đã xác nhận chúng được gắn đúng vào WordPress và MySQL.
