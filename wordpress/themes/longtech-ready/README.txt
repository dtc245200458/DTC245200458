LONGTECH READY
Cài đặt: Giao diện > Thêm Giao diện > Tải giao diện lên > chọn longtech-ready.zip (không giải nén) > Cài đặt ngay > Kích hoạt.
Mở địa chỉ gốc website để xem trang đầu. Template front-page.html được dùng cho trang đầu.
Chỉnh nội dung: Giao diện > Trình thiết kế > Mẫu trang > Trang đầu. Chỉnh khối rồi Lưu. Đầu trang/chân trang là các phần mẫu riêng.
Menu dẫn tới các phần cùng trang, không tạo bốn trang độc lập.
Nội dung hiện tại là giới thiệu ba nhóm sản phẩm. Chưa có dữ liệu mẫu máy/giá cụ thể; thông tin liên hệ cần bạn bổ sung. Không có form gửi tin nhắn.
Thiết bị là minh họa CSS, không phải ảnh sản phẩm thật. Không tải font/ảnh từ bên ngoài.
Theme không tự sửa/xóa trang nháp, tài khoản, database hay Docker. Trang chủ nháp cũ vẫn được giữ; trang đầu mới dùng nội dung của mẫu Trang đầu.
Quay lại giao diện cũ: kích hoạt Twenty Twenty-Five.
Kiểm tra sau cài: trang chủ trên máy tính/điện thoại, các liên kết menu và nút. Gửi nguyên lỗi nếu có.
Mã nguồn trong ZIP có thể đưa vào repo. Không chứa mật khẩu hoặc cấu hình Docker.
Đây chỉ là phần giao diện. Prometheus/Grafana, Loki/Promtail, hardening, báo cáo và minh chứng môn học cần hoàn thiện riêng.

BẢN 1.1: Ba màu xanh đậm/xanh lá/cam, hai nền trắng ngà/xanh nhạt; viền thẻ, bóng, hover, nút rõ hơn. Tải ZIP lên như lần đầu và chọn thay thế phiên bản đang cài. Giữ nguyên thư mục longtech-ready. Sau cập nhật nhấn Ctrl+F5.


PHIEN BAN 1.2.0: Cam dam, cam tuoi, cam nhat; nen trang/kem; duong mach trang tri; the san pham vien cam. Tai ZIP len Giao dien > Them giao dien > Tai giao dien len. Chon thay the ban hien tai. Tai lai trang bang Ctrl+F5.

LONGTECH 1.3.0 — BỘ SẢN PHẨM
1. WordPress > Giao diện > Thêm giao diện > Tải giao diện lên.
2. Chọn longtech-ready.zip, Cài đặt ngay, Thay thế giao diện hiện tại.
3. Vào Sản phẩm LongTech > Nhập bộ sản phẩm > Thêm 6 sản phẩm vào website.
   Thành công: Đã thêm 6 sản phẩm. Lỗi: 0.
   Bấm lại: Đã thêm 0 sản phẩm; nội dung đã nhập không bị ghi đè.
4. Mở trang chủ, Ctrl+F5, kéo xuống Bộ sưu tập. Bấm Xem chi tiết.
5. Sửa tên, mô tả, thông số tại Sản phẩm LongTech. Sửa mô tả thẻ ở Trích đoạn;
   thay ảnh tại Ảnh đại diện; chọn nhóm tại Nhóm sản phẩm.

Sản phẩm lưu trong cơ sở dữ liệu WordPress. Chức năng đăng ký loại sản phẩm
hiện nằm trong giao diện LongTech: đổi sang giao diện khác sẽ ẩn mục quản lý,
không xóa các bản ghi. Kích hoạt LongTech để dùng lại.
Ảnh mặc định nằm trong assets; có nguồn trong IMAGE-SOURCES.json.
Ảnh có thể minh họa nhiều màu/kích thước cùng dòng; cấu hình xem nội dung.
Thông số có đường dẫn nhà sản xuất ở từng trang; không có giá bán, giỏ hàng.
Nếu đã tùy chỉnh mẫu Trang chủ trong Trình sửa giao diện, bản lưu trong
WordPress có thể ghi đè mẫu từ ZIP. Không xóa tùy chỉnh vội: gửi ảnh để kiểm tra.

KIỂM TRA: cấu trúc ZIP, JSON, cân bằng block và ảnh đã kiểm tra cục bộ.
Chưa chạy tích hợp trên WordPress/MySQL của người dùng. Sau cài cần kiểm tra
nhập dữ liệu, mở chi tiết và sửa một sản phẩm rồi tải lại trang.


1.4.0: Trang chi tiết bố cục ảnh/thông tin hai cột, khung nội dung, điều hướng. Chỉ cập nhật giao diện, KHÔNG cần nhập lại 6 sản phẩm. Nội dung đã sửa trong WordPress được giữ nguyên. Chưa kiểm tra tích hợp trên máy chủ người dùng.

1.5.0 — GIỚI THIỆU VÀ LIÊN HỆ
Cập nhật ZIP như các bản trước. Không nhập lại sản phẩm.
Vào Giao diện > Liên hệ LongTech để nhập email công khai, số điện thoại,
địa chỉ/khu vực. Bấm Lưu thông tin liên hệ. Thành công: Đã lưu thông tin liên hệ.
Email hoặc số điện thoại không hợp lệ: báo Chưa lưu, giữ nguyên dữ liệu cũ.
Trường trống sẽ không xuất hiện ngoài website. Khi cả ba trường trống,
hiển thị thông báo đang cập nhật và đường dẫn tới bộ sưu tập.
Email dùng mailto: mở ứng dụng soạn thư của khách. Không có biểu mẫu gửi thư
hoặc dịch vụ gửi email phía máy chủ. Số điện thoại dùng tel:.
Các kênh đã lưu là thông tin công khai. Không tự lấy email quản trị WordPress.
Kiểm tra sau cài: menu Giới thiệu/Liên hệ, lưu một kênh liên hệ, tải lại trang,
kiểm tra link đúng email/số điện thoại; xóa trường nếu không muốn công khai.
PHP được kiểm tra cú pháp bằng php-parser, không phải chạy tích hợp WordPress.

1.6.0 — 24 SẢN PHẨM, MỖI NHÓM 8
1. Tải ZIP lên Giao diện và thay thế phiên bản cũ.
2. Vào Sản phẩm LongTech > Nhập bộ sản phẩm > Bổ sung bộ 24 sản phẩm.
3. Nếu đã có 6 mẫu cũ: mong đợi Đã thêm 18 sản phẩm. Bỏ qua: 6. Lỗi: 0.
   Nếu chưa nhập mẫu nào: Đã thêm 24 sản phẩm. Bỏ qua: 0. Lỗi: 0.
   Bấm lại không tạo bản trùng, không sửa nội dung đã nhập.
4. Tải lại trang chủ bằng Ctrl+F5. Mỗi nhóm hiển thị số sản phẩm thực tế.
Sản phẩm mẫu trong thùng rác cũng được bỏ qua; cần khôi phục thủ công nếu muốn
hiển thị lại. Các sản phẩm tự thêm của bạn không bị xóa, vì vậy tổng thực tế
có thể lớn hơn 24. Ảnh mặc định lưu cùng ZIP, nguồn trong IMAGE-SOURCES.json.
Các đời máy/kích thước là sản phẩm riêng, không khẳng định là đời mới nhất.
Lưới 4 cột trên màn hình rộng, 2 cột <=1000px, 1 cột <=600px.

1.6.1: Tối ưu 24 ảnh thành JPEG tối đa 800px, giảm dung lượng gói tải lên. Không thay dữ liệu sản phẩm.

Bản 1.7.0: 15 sản phẩm, mỗi nhóm 5 mẫu. Giữ 6 mẫu ban đầu, bổ sung 9 mẫu. Sau khi thay thế giao diện, vào Sản phẩm LongTech → nhập bộ sản phẩm và bấm Bổ sung bộ 15 sản phẩm. Cập nhật giao diện không tự nhập dữ liệu. Không xóa sản phẩm đã tồn tại.
