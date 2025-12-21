# Hướng dẫn cấu hình Upload 50MB trên VPS

Hướng dẫn chi tiết để cấu hình VPS hỗ trợ upload file lên đến 50MB cho slider.

## 📋 Yêu cầu

- VPS đã cài đặt Nginx và PHP-FPM 8.2
- Quyền sudo/root

## 🔧 Bước 1: Cấu hình PHP-FPM Pool

### 1.1. Chỉnh sửa PHP-FPM pool config

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Tìm và chỉnh sửa các dòng sau (hoặc thêm nếu chưa có):

```ini
; Upload settings
php_admin_value[upload_max_filesize] = 50M
php_admin_value[post_max_size] = 55M
php_admin_value[max_execution_time] = 300
php_admin_value[max_input_time] = 300
php_admin_value[memory_limit] = 256M
```

**Lưu ý**: 
- `post_max_size` phải lớn hơn `upload_max_filesize` (thường +5M)
- Nếu không tìm thấy, thêm vào cuối file trong section `[www]`

### 1.2. Restart PHP-FPM

```bash
sudo systemctl restart php8.2-fpm
sudo systemctl status php8.2-fpm
```

## 🌐 Bước 2: Cấu hình Nginx

### 2.1. Chỉnh sửa Nginx config

```bash
sudo nano /etc/nginx/sites-available/micex
```

Tìm dòng:
```nginx
client_max_body_size 20M;
```

Thay đổi thành:
```nginx
client_max_body_size 50M;
```

### 2.2. Test và restart Nginx

```bash
# Test cấu hình
sudo nginx -t

# Nếu test thành công, restart Nginx
sudo systemctl restart nginx

# Kiểm tra status
sudo systemctl status nginx
```

## ⚙️ Bước 3: Cấu hình PHP.ini (Tùy chọn)

Nếu cấu hình PHP-FPM pool không đủ, có thể cấu hình trực tiếp trong php.ini:

### 3.1. Chỉnh sửa php.ini

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Tìm và chỉnh sửa các dòng sau:

```ini
upload_max_filesize = 50M
post_max_size = 55M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

### 3.2. Restart PHP-FPM

```bash
sudo systemctl restart php8.2-fpm
```

## ✅ Bước 4: Kiểm tra cấu hình

### 4.1. Tạo file PHP test

```bash
sudo nano /var/www/micex/public/test-upload.php
```

Nội dung:

```php
<?php
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "max_input_time: " . ini_get('max_input_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
?>
```

### 4.2. Kiểm tra từ browser

Truy cập: `https://mon88.click/test-upload.php`

Kết quả mong đợi:
```
upload_max_filesize: 50M
post_max_size: 55M
max_execution_time: 300
max_input_time: 300
memory_limit: 256M
```

### 4.3. Xóa file test (QUAN TRỌNG!)

```bash
sudo rm /var/www/micex/public/test-upload.php
```

## 🔍 Bước 5: Kiểm tra logs nếu có lỗi

### 5.1. Kiểm tra Nginx error log

```bash
sudo tail -f /var/log/nginx/micex-error.log
```

### 5.2. Kiểm tra PHP-FPM error log

```bash
sudo tail -f /var/log/php8.2-fpm.log
```

### 5.3. Kiểm tra Laravel log

```bash
tail -f /var/www/micex/storage/logs/laravel.log
```

## 🚨 Troubleshooting

### Lỗi "413 Request Entity Too Large"

**Nguyên nhân**: Nginx `client_max_body_size` chưa đủ lớn

**Giải pháp**: 
1. Kiểm tra lại Nginx config: `sudo nginx -t`
2. Đảm bảo `client_max_body_size 50M;` đã được thêm
3. Restart Nginx: `sudo systemctl restart nginx`

### Lỗi "PostTooLargeException"

**Nguyên nhân**: PHP `post_max_size` hoặc `upload_max_filesize` chưa đủ lớn

**Giải pháp**:
1. Kiểm tra PHP-FPM pool config: `/etc/php/8.2/fpm/pool.d/www.conf`
2. Đảm bảo `php_admin_value[post_max_size] = 55M` và `php_admin_value[upload_max_filesize] = 50M`
3. Restart PHP-FPM: `sudo systemctl restart php8.2-fpm`

### Lỗi "504 Gateway Timeout"

**Nguyên nhân**: `max_execution_time` hoặc `fastcgi_read_timeout` quá nhỏ

**Giải pháp**:
1. Tăng `max_execution_time` trong PHP-FPM pool config
2. Tăng `fastcgi_read_timeout` trong Nginx config (đã có 300s trong DEPLOY.md)

## 📝 Tóm tắt các file cần chỉnh sửa

1. **PHP-FPM Pool Config**: `/etc/php/8.2/fpm/pool.d/www.conf`
   - `php_admin_value[upload_max_filesize] = 50M`
   - `php_admin_value[post_max_size] = 55M`

2. **Nginx Config**: `/etc/nginx/sites-available/micex`
   - `client_max_body_size 50M;`

3. **PHP.ini** (nếu cần): `/etc/php/8.2/fpm/php.ini`
   - `upload_max_filesize = 50M`
   - `post_max_size = 55M`

## ✅ Sau khi cấu hình xong

1. Test upload một file ảnh lớn (khoảng 30-40MB) qua admin panel
2. Kiểm tra logs nếu có lỗi
3. Xóa file test-upload.php nếu đã tạo

---

**Lưu ý**: Sau khi thay đổi cấu hình, luôn nhớ:
- Test cấu hình trước khi restart (`sudo nginx -t`)
- Restart services để áp dụng thay đổi
- Kiểm tra status của services sau khi restart

