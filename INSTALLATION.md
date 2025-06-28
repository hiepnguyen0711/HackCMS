# 📋 Hướng Dẫn Cài Đặt - Universal Penetration Testing Platform v4.0

## 🚀 Cài Đặt Nhanh

### 1. Giải Nén Files
```bash
# Giải nén tất cả files vào thư mục XAMPP htdocs
cp -r Universal_Penetration_Testing_Platform_v4.0/* /xampp/htdocs/your-project/
```

### 2. Thiết Lập Quyền
```bash
# Thiết lập quyền phù hợp
chmod 755 /xampp/htdocs/your-project/
chmod 644 /xampp/htdocs/your-project/*.php
chmod 755 /xampp/htdocs/your-project/logs/
```

### 3. Truy Cập Giao Diện
```
http://localhost/your-project/penetration_tester_simple.php
```

## 🎯 Hướng Dẫn Sử Dụng Nhanh

### 1. Tự Động Phát Hiện Target
- Click "🎯 Discover All Targets" để tìm tất cả localhost projects
- Chọn target từ dropdown hoặc nhập thủ công

### 2. Upload Shell
- Chọn phương thức upload (khuyên dùng Admin Upload)
- Upload shell tùy chỉnh hoặc dùng advanced shell mặc định
- Truy cập shell tại URL được tạo

### 3. Bypass Authentication
- Dùng SQL injection: `admin' OR '1'='1' --`
- Thử nhiều phương thức bypass nếu cần
- Kết hợp với shell upload để có quyền truy cập tối đa

## 📁 Mô Tả Files

### Files Chính
- `penetration_tester_simple.php` - Giao diện testing chính
- `enhanced_shell_uploader.php` - Universal shell uploader (5 phương thức)
- `target_discovery.php` - Engine tự động phát hiện

### Công Cụ Nâng Cao
- `debug_interface.php` - Phân tích target sâu
- `test_bypass_api.php` - Testing bypass authentication
- `integrated_auth_bypass_api.php` - Hệ thống bypass hoàn chỉnh

### Tài Liệu
- `README.md` - Tài liệu toàn diện
- `INSTALLATION.md` - Hướng dẫn cài đặt này

## 🔧 Khắc Phục Sự Cố

### Lỗi Thường Gặp
1. **Upload Failed**: Kiểm tra quyền thư mục
2. **Shell Không Truy Cập Được**: Xác minh cài đặt .htaccess
3. **Discovery Failed**: Kiểm tra cấu trúc XAMPP
4. **Bypass Failed**: Thử các phương thức khác

### Lệnh Debug
```bash
chmod 755 /upload/directory
chmod 644 /upload/files
```

## ⚠️ Cảnh Báo Bảo Mật

**⚠️ CHỈ DÀNH CHO TESTING ĐƯỢC PHÉP**
- Chỉ sử dụng trên hệ thống của bạn
- Mục đích giáo dục only
- Luôn dọn dẹp sau khi testing
- Tuân thủ luật pháp địa phương

## 📊 Hiệu Suất Hệ Thống

- Phát hiện Target: 2-5 giây
- Upload Shell: 1-3 giây
- Auth Bypass: 2-10 giây
- Tỷ lệ thành công: 80-98% tùy phương thức

## 🛠️ Yêu Cầu Hệ Thống

### Môi Trường
- XAMPP/WAMP/LAMP
- PHP 7.4+ (khuyên dùng PHP 8.0+)
- Apache với mod_rewrite
- MySQL (tùy chọn)

### Thư Mục Hỗ Trợ
- `sources/` - Template directories
- `admin/` - Admin panels
- `uploads/` - Upload directories
- `wp-content/` - WordPress content

## 📞 Hỗ Trợ

Nếu gặp vấn đề hoặc có câu hỏi:
1. Kiểm tra logs trong thư mục `/logs/`
2. Sử dụng debug interface để phân tích sâu
3. Xem README.md để khắc phục sự cố
4. Đảm bảo XAMPP đang chạy và có quyền write

## 🎉 Tips Sử Dụng Hiệu Quả

### Tối Ưu Hóa Performance
- Dùng Admin Upload cho tỷ lệ thành công cao nhất
- Kết hợp multiple methods cho kết quả tốt nhất
- Sử dụng auto-discovery để tiết kiệm thời gian
- Check logs thường xuyên để monitor hoạt động

### Bảo Mật Tốt Nhất
- Backup data trước khi test
- Sử dụng trong environment isolated
- Không test trên production systems
- Remove shells sau khi hoàn thành testing

---
**Phiên Bản**: 4.0 Multi-Target Edition  
**Phát Triển Bởi**: Hiệp Nguyễn  
**Ngày**: 28/06/2025 04:48:14  
**License**: Educational Use Only

**🔒 Sử dụng có trách nhiệm và chỉ trên hệ thống được phép!**
