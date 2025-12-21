# Hướng dẫn chạy round:process-loop trên VPS

## 🚀 Cách nhanh nhất: Dùng Screen

### Bước 1: SSH vào VPS
```bash
ssh user@your-vps-ip
```

### Bước 2: Di chuyển vào thư mục project
```bash
cd /path/to/your/project
# Ví dụ: cd /var/www/micex
```

### Bước 3: Dừng process cũ (nếu có)
```bash
# Tìm và dừng process cũ
pkill -f "round:process-loop"
# Hoặc
ps aux | grep "round:process-loop"
kill <PID>
```

### Bước 4: Chạy với Screen
```bash
# Cài đặt screen (nếu chưa có)
sudo apt install screen -y

# Chạy command trong screen session
screen -dmS round-process-loop bash -c "cd $(pwd) && php artisan round:process-loop"
```

### Bước 5: Kiểm tra
```bash
# Kiểm tra screen session
screen -ls

# Kiểm tra process
ps aux | grep "round:process-loop"
```

### Các lệnh hữu ích:

**Xem logs real-time:**
```bash
screen -r round-process-loop
# Để thoát: Nhấn Ctrl+A, sau đó D
```

**Dừng process:**
```bash
screen -S round-process-loop -X quit
# Hoặc
pkill -f "round:process-loop"
```

**Restart:**
```bash
# Dừng
screen -S round-process-loop -X quit

# Chạy lại
screen -dmS round-process-loop bash -c "cd $(pwd) && php artisan round:process-loop"
```

---

## 🔄 Script tự động (Khuyến nghị)

Sử dụng script `restart-round-process-simple.sh`:

```bash
# 1. Upload script lên VPS (hoặc tạo mới)
nano restart-round-process-simple.sh
# Copy nội dung từ file restart-round-process-simple.sh

# 2. Cấp quyền
chmod +x restart-round-process-simple.sh

# 3. Chạy
./restart-round-process-simple.sh
```

---

## 📝 Các phương pháp khác

### Nohup (Đơn giản)
```bash
cd /path/to/your/project
nohup php artisan round:process-loop > storage/logs/round-process.log 2>&1 &
```

### Supervisor (Production)
Xem hướng dẫn chi tiết trong `DEPLOY.md` phần "Phương pháp 4: Supervisor"

### Systemd (Production)
Xem hướng dẫn chi tiết trong `DEPLOY.md` phần "Phương pháp 5: Systemd Service"

---

## ✅ Kiểm tra Process đang chạy

```bash
# Kiểm tra process
ps aux | grep "round:process-loop"

# Kiểm tra screen
screen -ls

# Xem logs
tail -f storage/logs/round-process.log
```

---

## 🛠️ Troubleshooting

**Process không chạy:**
```bash
# Kiểm tra PHP
php -v

# Kiểm tra Laravel
php artisan --version

# Kiểm tra quyền
ls -la storage/logs/
```

**Process bị dừng:**
```bash
# Kiểm tra logs
tail -100 storage/logs/laravel.log

# Kiểm tra memory
free -h

# Restart lại
./restart-round-process-simple.sh
```

