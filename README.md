# LongTech - Website Quảng bá Sản phẩm

## 1. Thông tin đề tài

- Môn học: Triển khai và Quản trị Hệ thống Phần mềm
- Đề tài: Đề 3 - Website Quảng bá Sản phẩm (WordPress)
- Tên hệ thống: LongTech
- Mục tiêu: Triển khai website giới thiệu sản phẩm công nghệ bằng WordPress, kết hợp cơ sở dữ liệu, reverse proxy, giám sát và quản lý log.

## 2. Danh mục sản phẩm

Website hiện có 15 sản phẩm thuộc 3 nhóm:

1. Laptop: 5 sản phẩm.
2. Điện thoại: 5 sản phẩm.
3. Phụ kiện: 5 sản phẩm.

Mỗi sản phẩm có trang chi tiết và liên kết tham khảo từ nhà sản xuất.
Website phục vụ giới thiệu sản phẩm, chưa có chức năng đặt hàng hoặc thanh toán.

## 3. Trạng thái triển khai

Đã triển khai và kiểm tra:

- WordPress: website LongTech.
- MySQL: lưu dữ liệu WordPress.
- phpMyAdmin: công cụ quản lý cơ sở dữ liệu.
- Nginx: reverse proxy, ẩn số phiên bản Nginx và thêm các header:
  - X-Content-Type-Options: nosniff
  - X-Frame-Options: SAMEORIGIN
  - Referrer-Policy: strict-origin-when-cross-origin
- Website trả HTTP 200 tại thời điểm kiểm tra.
- Dữ liệu WordPress và MySQL được lưu trong Docker named volumes.

Chưa triển khai trong cấu hình Compose hiện tại:

- Prometheus và Grafana: thu thập, hiển thị số liệu giám sát.
- Loki và Promtail: thu thập, lưu trữ và truy vấn log.

## 4. Khởi chạy

Yêu cầu: Docker Engine và Docker Compose.

Trên môi trường mới, tạo tệp cấu hình:

```bash
cp .env.example .env
```

Chỉnh các giá trị trong .env trước khi khởi chạy.
Không ghi đè .env của môi trường đang hoạt động.

```bash
docker compose -f docker-compose.yml up -d
docker compose -f docker-compose.yml ps
```

Truy cập website qua http://<IP-may-Ubuntu>/.

Lưu ý: Compose khởi chạy các dịch vụ. Giao diện LongTech và dữ liệu
15 sản phẩm cần được cài đặt/nhập riêng hoặc khôi phục từ bản sao lưu;
chúng chưa được đóng gói trong repository ở mốc này.

## 5. Sao lưu

Đã tạo và chuyển sang Windows:

- wordpress-files.tar.gz: tệp WordPress, giao diện và tài nguyên.
- database.sql: bản xuất cơ sở dữ liệu.
- project-config.tar.gz: cấu hình triển khai.

Đã kiểm tra tệp nén WordPress và dòng hoàn tất bản xuất SQL.
Chưa thực hiện kiểm thử khôi phục.

Các bản sao lưu có thể chứa thông tin nhạy cảm, không đưa lên repository công khai.
Không dùng docker compose down -v nếu cần giữ dữ liệu.

## 6. Ghi chú

Repository phục vụ bài thực hành môn Triển khai và Quản trị Hệ thống Phần mềm.
Không commit tệp .env chứa thông tin đăng nhập.
