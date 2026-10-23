# Truy vấn log LongTech

Trong Grafana: Explore → Loki → Code → chọn thời gian → Run query.

## 1. Log Nginx

```logql
{job="longtech",service="nginx"}
```

## 2. Lượt truy cập kiểm chứng

```logql
{job="longtech",service="nginx"} |= "longtech_log_check=demo01"
```

Đã kiểm chứng dòng GET chứa dấu nhận diện và HTTP 200.
Chọn khoảng thời gian có lượt truy cập đó; ngoài khoảng sẽ không có kết quả.

Có thể tạo lượt truy cập mới trên Ubuntu:

```bash
curl -sS -L --max-time 20 -o /dev/null \
  -w 'HTTP=%{http_code}\n' \
  'http://localhost/?longtech_log_check=demo01'
```

## 3. Số dòng log từng dịch vụ trong cửa sổ 5 phút

```logql
sum by (service) (count_over_time({job="longtech"}[5m]))
```

Đây là số dòng log, không phải số người truy cập.
Dịch vụ không phát sinh log trong khoảng có thể không có kết quả.
