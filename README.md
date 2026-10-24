# LongTech — Website giới thiệu sản phẩm công nghệ

## 1. Thông tin đề tài

- Sinh viên: Nguyễn Kim Thành Long.
- MSSV: DTC245200458.
- Môn học: Triển khai và Quản trị Hệ thống Phần mềm.
- Đề 3: Website Quảng bá Sản phẩm.
- WordPress có 15 sản phẩm: 5 laptop, 5 điện thoại, 5 phụ kiện.
- Có trang chi tiết và liên kết tham khảo nhà sản xuất.
- Chưa có chức năng đặt hàng hoặc thanh toán.

## 2. Kiến trúc

| Tệp Compose | Dịch vụ |
|---|---|
| docker-compose.yml | Nginx, WordPress, MySQL, phpMyAdmin |
| docker-compose.monitoring.yml | Prometheus, Grafana, Blackbox, Nginx exporter |
| docker-compose.logging.yml | Loki, Promtail |
| docker-compose.database-metrics.yml | MySQL exporter |
| docker-compose.container-metrics.yml | cAdvisor |

Nginx chuyển request tới WordPress; WordPress sử dụng MySQL.
Prometheus thu thập metrics từ các exporter; Grafana hiển thị dashboard.
Promtail đọc log Docker, gửi Loki; Grafana truy vấn log từ Loki.

- frontend: Nginx, WordPress, Blackbox và Nginx exporter.
- backend internal: MySQL, WordPress, phpMyAdmin và MySQL exporter.
- admin: mạng bổ sung cho phpMyAdmin.
- monitoring: kết nối các thành phần giám sát và log.

## 3. Cấu trúc dự án

- wordpress/themes/longtech-ready/: theme và tài nguyên.
- grafana/provisioning/: dashboard và datasource.
- scripts/start.sh: khởi chạy 12 dịch vụ và cập nhật log targets.
- scripts/check-system.sh: kiểm tra ứng dụng, header và dịch vụ nền.
- scripts/check-final.py: kiểm tra 5 Compose, metrics, log mới và secret.
- scripts/backup.sh: sao lưu ứng dụng, cấu hình và lịch sử Git.
- scripts/refresh-log-targets.py: cập nhật đường dẫn log container.
- docs/evidence/: kết quả kiểm tra thực tế.
- docs/log-queries.md: các truy vấn LogQL.

## 4. Chạy trên máy hiện tại

Yêu cầu Docker Engine, Docker Compose, Bash, Python 3, curl, Git;
các image và secret đã được chuẩn bị.

Trong ~/longtech-product-website, chạy lần lượt:

    bash scripts/start.sh
    bash scripts/check-system.sh
    python3 scripts/check-final.py

Sau khi vừa khởi động, chờ khoảng 2–3 phút để truy vấn rate có đủ mẫu.
Script start dùng --pull never; cần tải image trước nếu máy chưa có.

Website hiện tại: http://192.168.105.130/.
Địa chỉ này thuộc máy triển khai, không phải địa chỉ chung cho mọi máy.

## 5. Truy cập công cụ từ Windows

Mở CMD hoặc PowerShell trên Windows và chạy:

    ssh -N -o ExitOnForwardFailure=yes -o ServerAliveInterval=30 -L 3000:127.0.0.1:3000 -L 9090:127.0.0.1:9090 -L 8081:127.0.0.1:8081 thanhlong@192.168.105.130

Giữ cửa sổ mở. Nếu cổng bị tunnel cũ chiếm, đóng tunnel cũ trước.

- Grafana tổng quan: http://localhost:3000/d/longtech-system
- Dashboard HTTP: http://localhost:3000/d/longtech-overview
- Prometheus: http://localhost:9090/
- phpMyAdmin: http://localhost:8081/

Grafana dùng admin; mật khẩu khởi tạo nằm trong data/grafana-admin-password.
Nếu đã đổi mật khẩu trong Grafana, dùng mật khẩu mới.
phpMyAdmin sử dụng tài khoản MySQL đã cấu hình.
Mật khẩu Ubuntu, Grafana và MySQL là các thông tin đăng nhập riêng.

## 6. Dừng và bật lại

Dừng toàn bộ stack bằng lệnh:

    docker compose -f docker-compose.yml -f docker-compose.monitoring.yml -f docker-compose.logging.yml -f docker-compose.database-metrics.yml -f docker-compose.container-metrics.yml stop

Bật lại bằng bash scripts/start.sh.
Các dịch vụ dùng restart: unless-stopped.
Sau khi chủ động stop, cần bật lại bằng script.
Sau khi khởi động lại Windows, cần mở lại SSH tunnel.
Không dùng down -v nếu cần giữ dữ liệu.

## 7. Giám sát và log

Dashboard tổng quan có 12 panel:
- Trạng thái HTTP, Nginx, MySQL và cAdvisor.
- CPU, RAM theo dịch vụ.
- Request Nginx mỗi giây và kết nối đang mở.
- Kết nối MySQL và câu lệnh client mỗi giây.
- Thời gian kiểm tra HTTP và số container có dữ liệu RAM.

CPU 100% tương ứng một lõi; có thể vượt 100% khi dùng nhiều lõi.
RAM là working set.
HTTP probe đi từ mạng Docker qua Nginx, không đo đầy đủ tốc độ tải trang
trên máy người dùng. Lưu lượng Nginx có thể bao gồm request giám sát.

Prometheus scrape mỗi 15 giây; retention 7 ngày hoặc 1 GB,
tùy giới hạn nào đạt trước; đây không phải hạn mức tổng volume.
Loki được cấu hình retention 7 ngày.

Promtail thu log Nginx, WordPress, MySQL và Grafana.
Trong Grafana Explore, chọn Loki và dùng truy vấn ở docs/log-queries.md.

Sau khi tạo lại container nguồn, chạy:

    python3 scripts/refresh-log-targets.py

Script start đã thực hiện bước này.
Nếu dashboard không có dữ liệu, kiểm tra đồng hồ Windows/Ubuntu,
NTP, khoảng thời gian hiển thị và trạng thái target.

## 8. Biện pháp bảo vệ

- MySQL không công bố cổng ra host; backend là mạng internal.
- Grafana, Prometheus, Loki, phpMyAdmin và cAdvisor bind host tại 127.0.0.1.
- Công cụ quản trị được truy cập từ Windows qua SSH tunnel.
- Nginx ẩn số phiên bản và X-Powered-By.
- Có X-Content-Type-Options, X-Frame-Options và Referrer-Policy.
- Grafana tắt đăng ký tự do và truy cập ẩn danh.
- .env và data/ được Git bỏ qua.
- Log Docker xoay vòng 10 MB/tệp, tối đa 3 tệp mỗi dịch vụ.
- Nginx exporter và MySQL exporter dùng read_only, cap_drop ALL
  và no-new-privileges.
- MySQL exporter sử dụng tài khoản giám sát riêng.

Giới hạn: website hiện dùng HTTP, chưa có HTTPS.
cAdvisor chạy privileged và đọc tài nguyên host.
Promtail chạy root để đọc log.
Tài khoản MySQL exporter có quyền SELECT phạm vi rộng.
Chưa thực hiện kiểm thử bảo mật toàn diện.

## 9. Kết quả kiểm tra

Minh chứng kiểm tra cuối:
docs/evidence/final-check-20261024-154009.txt.

Kết quả tại thời điểm kiểm tra:
- Bài kiểm tra hệ thống và kiểm tra bổ sung đều đạt.
- 12 dịch vụ đang chạy, đồng hồ đã đồng bộ.
- 5 target Prometheus hoạt động.
- CPU và RAM có dữ liệu của 12 dịch vụ.
- Có metrics HTTP, Nginx và MySQL.
- Request mới tới Nginx xuất hiện trong Loki.
- Các tệp secret được Git bỏ qua, không bị theo dõi.

Ảnh dashboard tổng quan đã hiển thị trạng thái, CPU/RAM và request Nginx.
Kết quả này phản ánh thời điểm kiểm tra, không bảo đảm hệ thống luôn sẵn sàng.

## 10. Sao lưu và phục hồi

Commit thay đổi trước khi sao lưu; tránh sửa website trong lúc chạy.

    bash scripts/backup.sh
    cat data/latest-backup-path

Bản sao lưu trong ~/longtech-backups/ gồm database.sql,
wordpress-files.tar.gz, project-config.tar.gz, repository.bundle,
site-manifest.json, BACKUP-NOTES.txt và SHA256SUMS.

Cấu hình sao lưu gồm đủ 5 Compose và secret Grafana/MySQL exporter.
Có kiểm tra tệp nén, Git bundle và SHA-256.
Chuyển bản sao lưu sang Windows để lưu ngoài máy ảo.
Bản sao lưu chứa mật khẩu; không đưa lên GitHub công khai.

Không sao lưu lịch sử Prometheus/Loki hoặc database Grafana.
Dashboard/datasource được lưu bằng provisioning.
SQL và tệp được sao lưu tuần tự, không phải snapshot đồng thời.
SQL ứng dụng không chứa tài khoản/grant MySQL cấp máy chủ.

Đã kiểm thử bản sao lưu trước bằng cách nhập SQL, giải nén WordPress
vào volume riêng và nạp WordPress qua PHP để đối chiếu:
theme longtech-ready, siteurl http://192.168.105.130,
15 sản phẩm, WordPress 7.1.1.

Minh chứng: docs/evidence/restore-check-20261023-234231.txt.
Chưa kiểm thử trình duyệt của môi trường phục hồi hoặc khôi phục
toàn bộ stack giám sát. Container thử đã dừng; volume thử còn giữ.

## 11. Máy mới và giới hạn tái triển khai

Clone repository chưa tự phục hồi website đang có.
Cần chuẩn bị image, .env, secret, volume; phục hồi SQL/tệp WordPress.
Trên MySQL mới phải tạo lại tài khoản longtech_metrics với mật khẩu
khớp data/mysql-exporter.cnf và quyền giám sát đã cấu hình.
Nếu IP thay đổi, cập nhật URL WordPress.
Cập nhật log targets, đồng bộ thời gian và chạy kiểm tra.

Không ghi đè .env hoặc volume đang hoạt động để thử phục hồi.
Chưa khóa toàn bộ image bằng digest.
Promtail đã hết vòng đời hỗ trợ; Alloy là hướng nâng cấp.
Hai volume ứng dụng thiếu nhãn Compose nên có cảnh báo;
đã xác nhận gắn đúng vào WordPress và MySQL.
