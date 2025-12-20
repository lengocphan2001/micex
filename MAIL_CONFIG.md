# Cấu hình SMTP Mail cho Micex

## Cấu hình trong file .env

Thêm các dòng sau vào file `.env` của bạn:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Micex"
```

## Cấu hình với Gmail

1. **Bật 2-Step Verification:**
   - Vào Google Account Settings
   - Security → 2-Step Verification → Enable

2. **Tạo App Password:**
   - Vào: https://myaccount.google.com/apppasswords
   - Chọn "Mail" và "Other (Custom name)"
   - Nhập tên: "Micex Laravel"
   - Copy App Password (16 ký tự)
   - Sử dụng App Password này làm `MAIL_PASSWORD`

3. **Cấu hình .env:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx  # App Password từ Google
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Micex"
```

## Cấu hình với Mailtrap (Testing)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@micex.com
MAIL_FROM_NAME="Micex"
```

## Cấu hình với SMTP khác

### Outlook/Hotmail:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

### Yahoo:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mail.yahoo.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yahoo.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

## Sau khi cấu hình

Chạy lệnh sau để clear cache:

```bash
php artisan config:clear
php artisan cache:clear
```

## Test email

Sau khi cấu hình, bạn có thể test bằng cách:
1. Vào trang đổi mật khẩu quỹ
2. Click "Gửi mã"
3. Kiểm tra email inbox

## Troubleshooting

### Lỗi "Connection could not be established"
- Kiểm tra `MAIL_HOST` và `MAIL_PORT` đúng chưa
- Kiểm tra firewall có chặn port không
- Với Gmail, đảm bảo đã bật "Less secure app access" hoặc dùng App Password

### Lỗi "Authentication failed"
- Kiểm tra `MAIL_USERNAME` và `MAIL_PASSWORD` đúng chưa
- Với Gmail, phải dùng App Password, không dùng mật khẩu thường

### Email không đến
- Kiểm tra spam folder
- Kiểm tra logs: `storage/logs/laravel.log`
- Test với Mailtrap trước để đảm bảo code hoạt động

