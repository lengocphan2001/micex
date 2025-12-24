# Hướng dẫn Deploy Micex lên VPS Ubuntu 22.04

Hướng dẫn chi tiết để deploy ứng dụng Laravel Micex lên VPS Ubuntu 22.04 với Nginx, SSL và các cấu hình cần thiết.

## 📋 Yêu cầu hệ thống

- **OS**: Ubuntu 22.04 LTS
- **PHP**: 8.2 hoặc cao hơn
- **Database**: MySQL 8.0+ hoặc MariaDB 10.6+
- **Web Server**: Nginx
- **SSL**: Let's Encrypt (Certbot)
- **Domain**: micex-x.com  (đã trỏ về IP VPS)

## 🚀 Bước 1: Chuẩn bị VPS

### 1.1. Cập nhật hệ thống

```bash
sudo apt update && sudo apt upgrade -y
```

### 1.2. Tạo user mới (khuyến nghị)

```bash
# Tạo user mới
sudo adduser deploy
sudo usermod -aG sudo deploy

# Chuyển sang user mới
su - deploy
```

## 📦 Bước 2: Cài đặt Dependencies

### 2.1. Cài đặt PHP 8.2 và các extension cần thiết

**QUAN TRỌNG**: Laravel 12 yêu cầu PHP 8.2 trở lên. PHP 8.1 không tương thích!

```bash
# Bước 1: Cài đặt các package cần thiết để thêm repository
sudo apt install -y software-properties-common lsb-release ca-certificates apt-transport-https gnupg2

# Bước 2: Thêm GPG key cho repository
sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C

# Bước 3: Thêm repository PPA cho PHP
sudo add-apt-repository ppa:ondrej/php -y

# Bước 4: Update package list
sudo apt update

# Bước 5: Kiểm tra xem repository đã có PHP 8.2 chưa
apt-cache search php8.2 | head -10

# Bước 6: Nếu vẫn không thấy, thử cách khác - thêm repository trực tiếp
echo "deb https://ppa.launchpadcontent.net/ondrej/php/ubuntu $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/ondrej-php.list
sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C
sudo apt update

# Bước 7: Cài đặt PHP 8.2 và các extension
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
    php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
    php8.2-bcmath php8.2-intl php8.2-readline php8.2-sqlite3

# Bước 8: Kiểm tra phiên bản PHP
php -v
php8.2 -v

# Bước 9: Set PHP 8.2 làm default (nếu có nhiều phiên bản PHP)
sudo update-alternatives --set php /usr/bin/php8.2
```

**Nếu vẫn gặp lỗi "Unable to locate package"**, thử các bước sau:

```bash
# Cách 1: Xóa và thêm lại repository
sudo add-apt-repository --remove ppa:ondrej/php -y
sudo rm -f /etc/apt/sources.list.d/ondrej-ubuntu-php-*.list
sudo apt update

# Thêm lại với GPG key
sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Cách 2: Kiểm tra architecture
dpkg --print-architecture
# Nếu là arm64, có thể cần repository khác

# Cách 3: Cài đặt từng package để xem lỗi cụ thể
sudo apt install -y php8.2-fpm
```

**Nếu tất cả đều không được**, có thể VPS không hỗ trợ PPA. Thử cài đặt từ source hoặc dùng Docker:

```bash
# Kiểm tra Ubuntu version
lsb_release -a

# Nếu là Ubuntu 22.04, PHP 8.2 phải có sẵn trong repository
# Nếu không có, có thể cần upgrade Ubuntu hoặc dùng Docker
```

### 2.2. Cài đặt Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 2.3. Cài đặt Node.js và NPM

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2.4. Cài đặt MySQL

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### 2.5. Cài đặt Nginx

```bash
sudo apt install -y nginx
```


## 🔐 Bước 3: Cấu hình Database

### 3.1. Tạo database và user

```bash
sudo mysql -u root -p
```

Trong MySQL console:

```sql
CREATE DATABASE micex CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'micex_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON micex.* TO 'micex_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 📥 Bước 4: Deploy Code

### 4.1. Clone repository hoặc upload code

```bash
# Tạo thư mục project
sudo mkdir -p /var/www/micex
sudo chown -R $USER:$USER /var/www/micex

# Nếu dùng Git
cd /var/www/micex
git clone <your-repository-url> .

# Hoặc upload code qua SCP/SFTP
```

### 4.2. Cài đặt dependencies

```bash
cd /var/www/micex

# Cài đặt PHP dependencies
composer install --optimize-autoloader --no-dev

# Cài đặt Node.js dependencies
npm install

# Build assets
npm run build
```

### 4.3. Cấu hình environment

```bash
# Copy file .env
cp .env.example .env

# Generate application key
php artisan key:generate

# Cấu hình .env
nano .env
```

Cấu hình `.env`:

```env
APP_NAME=Micex
APP_ENV=production
APP_KEY=base64:... (đã generate ở trên)
APP_DEBUG=false
APP_URL=https://micex-x.com 

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=micex
DB_USERNAME=micex_user
DB_PASSWORD=password

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

### 4.4. Chạy migrations và seeders

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 4.5. Tạo storage link và set permissions

```bash
php artisan storage:link

# Set permissions
sudo chown -R www-data:www-data /var/www/micex/storage
sudo chown -R www-data:www-data /var/www/micex/bootstrap/cache
sudo chmod -R 775 /var/www/micex/storage
sudo chmod -R 775 /var/www/micex/bootstrap/cache
```

### 4.6. Optimize Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🌐 Bước 5: Cấu hình Nginx

### 5.1. Tạo Nginx configuration

```bash
sudo nano /etc/nginx/sites-available/micex
```

Nội dung file:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name micex-x.com  www.micex-x.com ;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name micex-x.com  www.micex-x.com ;
    
    root /var/www/micex/public;
    index index.php index.html;

    # SSL Configuration (sẽ được cấu hình bởi Certbot)
    ssl_certificate /etc/letsencrypt/live/micex-x.com /fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/micex-x.com /privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Logging
    access_log /var/log/nginx/micex-access.log;
    error_log /var/log/nginx/micex-error.log;

    # Max upload size
    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Deny access to sensitive files
    location ~ /\.env {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

**Lưu ý**: Config trên chỉ có HTTP (port 80). Sau khi cài SSL certificate ở bước 6, Certbot sẽ tự động cập nhật config này để thêm HTTPS.

### 5.2. Enable site và test configuration

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/micex /etc/nginx/sites-enabled/

# Test Nginx configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

## 🔒 Bước 6: Cài đặt SSL với Let's Encrypt

### 6.1. Cài đặt Certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
```

### 6.2. Lấy SSL certificate

```bash
sudo certbot --nginx -d micex-x.com  -d www.micex-x.com 
```

Certbot sẽ tự động:
- Tạo SSL certificate
- Cấu hình Nginx để sử dụng SSL
- Thiết lập auto-renewal

### 6.3. Test auto-renewal

```bash
sudo certbot renew --dry-run
```

## ⚙️ Bước 7: Cấu hình Round Timer (Background Process)

### 7.1. Tạo systemd service cho Round Timer

**QUAN TRỌNG**: Round timer phải chạy mỗi giây ở background để xử lý rounds tự động!

```bash
sudo nano /etc/systemd/system/micex-round-timer.service
```

Nội dung:

```ini
[Unit]
Description=Micex Round Timer (runs every second)
After=network.target mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/micex
ExecStart=/usr/bin/php /var/www/micex/artisan round:process-loop
Restart=always
RestartSec=1

[Install]
WantedBy=multi-user.target
```

### 7.2. Enable và start Round Timer service

```bash
sudo systemctl daemon-reload
sudo systemctl enable micex-round-timer
sudo systemctl start micex-round-timer
sudo systemctl status micex-round-timer
```

**Lưu ý**: 
- Round timer chạy hoàn toàn ở server-side
- Không phụ thuộc vào client/browser
- User có thể đóng tab, round vẫn tiếp tục chạy
- Bets sẽ được xử lý tự động khi round finish
- Commission được tính tự động

### 7.3. Fix Permission Issues cho Round Timer

Nếu gặp lỗi "Permission denied" khi ghi log:

```bash
# Thêm user deploy vào group www-data
sudo usermod -a -G www-data deploy

# Set quyền cho storage và bootstrap/cache
sudo chown -R www-data:www-data /var/www/micex/storage
sudo chown -R www-data:www-data /var/www/micex/bootstrap/cache
sudo chmod -R 775 /var/www/micex/storage
sudo chmod -R 775 /var/www/micex/bootstrap/cache

# Đảm bảo thư mục logs có quyền ghi
sudo chmod -R 775 /var/www/micex/storage/logs
sudo touch /var/www/micex/storage/logs/laravel.log
sudo chown www-data:www-data /var/www/micex/storage/logs/laravel.log
sudo chmod 664 /var/www/micex/storage/logs/laravel.log

# Hoặc nếu muốn user deploy có thể ghi trực tiếp
sudo chown -R deploy:www-data /var/www/micex/storage
sudo chown -R deploy:www-data /var/www/micex/bootstrap/cache
sudo chmod -R 775 /var/www/micex/storage
sudo chmod -R 775 /var/www/micex/bootstrap/cache

# Logout và login lại để áp dụng group mới
# Hoặc chạy: newgrp www-data
```

**Lưu ý**: Sau khi thay đổi group, cần logout và login lại, hoặc chạy `newgrp www-data` để áp dụng group mới.

## ⏰ Bước 8: Cấu hình Cron Jobs

### 8.1. Cấu hình Laravel Scheduler

Laravel scheduler sẽ tự động chạy command `commission:notify-available` mỗi giờ để gửi thông báo hoa hồng cho users.

**Cách 1: Setup Cron Job (Khuyến nghị)**

```bash
# Chỉnh sửa crontab cho user www-data
sudo crontab -e -u www-data
```

Thêm dòng sau (thay `/var/www/micex` bằng đường dẫn thực tế của project):

```
* * * * * cd /var/www/micex && php artisan schedule:run >> /dev/null 2>&1
```

**Lưu ý**: 
- Cron job này chạy mỗi phút và gọi `schedule:run`
- `schedule:run` sẽ kiểm tra và chạy các scheduled tasks (như `commission:notify-available` mỗi giờ)
- Round timer được xử lý bởi RoundTimerLoop service (bước 7)

**Cách 2: Test Command Thủ Công**

Để test command trước khi setup cron:

```bash
# SSH vào VPS
ssh user@your-vps-ip

# Chuyển đến thư mục project
cd /var/www/micex

# Chạy command thủ công để test
php artisan commission:notify-available
```

**Cách 3: Kiểm Tra Scheduler Có Chạy Không**

```bash
# Xem log của scheduler
tail -f storage/logs/laravel.log

# Hoặc kiểm tra cron job có chạy không
sudo crontab -l -u www-data

# Kiểm tra xem có process nào đang chạy schedule:run không
ps aux | grep "schedule:run"
```

**Cách 4: Chạy Scheduler Trong Background (Tạm thời)**

Nếu chưa setup cron, có thể chạy scheduler trong screen/tmux:

```bash
# Sử dụng screen
screen -S scheduler
cd /var/www/micex
php artisan schedule:work

# Hoặc sử dụng tmux
tmux new -s scheduler
cd /var/www/micex
php artisan schedule:work
```

**Lưu ý**: `schedule:work` sẽ chạy scheduler liên tục trong foreground. Nhấn `Ctrl+A+D` (screen) hoặc `Ctrl+B+D` (tmux) để detach.

## 🔧 Bước 9: Cấu hình Firewall

### 9.1. Cấu hình UFW

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

## 📝 Bước 10: Cấu hình Log Rotation

### 10.1. Tạo logrotate config

```bash
sudo nano /etc/logrotate.d/micex
```

Nội dung:

```
/var/www/micex/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        /usr/bin/php /var/www/micex/artisan log:clear
    endscript
}
```

## 🔍 Bước 11: Kiểm tra và Test

### 11.1. Kiểm tra services

```bash
# Check PHP-FPM
sudo systemctl status php8.2-fpm

# Check Nginx
sudo systemctl status nginx

# Check MySQL
sudo systemctl status mysql

# Check Round Timer (QUAN TRỌNG!)
sudo systemctl status micex-round-timer
```

### 11.2. Test website

```bash
# Test từ server
curl -I https://micex-x.com 

# Kiểm tra SSL
openssl s_client -connect micex-x.com :443
```

## 🔄 Chạy Round Process Loop trên VPS

Command `round:process-loop` cần chạy liên tục trên VPS để xử lý rounds. Dưới đây là các cách để chạy command này sao cho nó vẫn hoạt động khi bạn disconnect khỏi VPS.

### Phương pháp 1: Screen (Đơn giản nhất, khuyến nghị)

Screen cho phép bạn tạo một session chạy background, có thể attach/detach bất cứ lúc nào.

```bash
# 1. Cài đặt screen (nếu chưa có)
sudo apt install screen -y

# 2. Tạo screen session mới và chạy command
screen -dmS round-process-loop bash -c "cd /var/www/micex && php artisan round:process-loop"

# 3. Kiểm tra session đang chạy
screen -ls

# 4. Attach vào session để xem logs (tùy chọn)
screen -r round-process-loop
# Để detach: Nhấn Ctrl+A, sau đó nhấn D

# 5. Dừng process (nếu cần)
# Tìm PID
ps aux | grep "round:process-loop"
# Hoặc kill từ screen
screen -S round-process-loop -X quit
```

**Ưu điểm:**
- Đơn giản, dễ sử dụng
- Có thể attach để xem logs real-time
- Process vẫn chạy khi disconnect

### Phương pháp 2: Tmux (Tương tự Screen, khuyến nghị)

```bash
# 1. Cài đặt tmux (nếu chưa có)
sudo apt install tmux -y

# 2. Tạo tmux session mới và chạy round timer
tmux new-session -d -s micex 'cd /var/www/micex && php artisan round:process-loop'

# 3. Kiểm tra các tmux sessions đang chạy
tmux ls

# 4. Attach vào session để xem logs
tmux attach -t micex
# Để detach (giữ session chạy): Nhấn Ctrl+B, sau đó nhấn D

# 5. Tạo window mới trong cùng session (nếu cần chạy lệnh khác)
# Trong tmux: Nhấn Ctrl+B, sau đó nhấn C

# 6. Chuyển đổi giữa các windows
# Trong tmux: Nhấn Ctrl+B, sau đó nhấn N (next) hoặc P (previous)

# 7. Dừng process (kill session)
tmux kill-session -t micex

# 8. Restart process (kill và tạo lại)
tmux kill-session -t micex 2>/dev/null; tmux new-session -d -s micex 'cd /var/www/micex && php artisan round:process-loop'
```

**Script helper để quản lý tmux (khuyến nghị):**

```bash
# 1. Upload script lên server (file tmux-micex.sh trong project root)
cd /var/www/micex

# 2. Cấp quyền thực thi
chmod +x tmux-micex.sh

# 3. Sử dụng script
./tmux-micex.sh start      # Tạo và start session
./tmux-micex.sh stop       # Dừng session
./tmux-micex.sh restart    # Restart session
./tmux-micex.sh status     # Kiểm tra status
./tmux-micex.sh attach     # Attach vào session
./tmux-micex.sh logs       # Xem logs
```

**Các lệnh tmux hữu ích:**

```bash
# List tất cả sessions
tmux ls

# Attach vào session
tmux attach -t micex
# hoặc
tmux a -t micex

# Tạo session mới với tên
tmux new -s micex

# Kill session
tmux kill-session -t micex

# Kill tất cả sessions
tmux kill-server
```

**Trong tmux session (sau khi attach):**

- `Ctrl+B` sau đó `D`: Detach (giữ session chạy)
- `Ctrl+B` sau đó `C`: Tạo window mới
- `Ctrl+B` sau đó `N`: Chuyển sang window tiếp theo
- `Ctrl+B` sau đó `P`: Chuyển về window trước
- `Ctrl+B` sau đó `[`: Scroll mode (để xem logs cũ)
- `Ctrl+B` sau đó `]`: Paste mode
- `Ctrl+B` sau đó `?`: Xem tất cả shortcuts

# 5. Dừng process
tmux kill-session -t round-process-loop
```

### Phương pháp 3: Nohup (Chạy background)

```bash
# 1. Chạy với nohup
cd /path/to/your/project
nohup php artisan round:process-loop > storage/logs/round-process.log 2>&1 &

# 2. Lưu PID để quản lý sau
echo $! > /tmp/round-process.pid

# 3. Kiểm tra process
ps aux | grep "round:process-loop"

# 4. Xem logs
tail -f storage/logs/round-process.log

# 5. Dừng process
kill $(cat /tmp/round-process.pid)
# Hoặc tìm và kill
pkill -f "round:process-loop"
```

### Phương pháp 4: Supervisor (Chuyên nghiệp, tự động restart)

Supervisor tự động restart process nếu bị crash, phù hợp cho production.

```bash
# 1. Cài đặt Supervisor
sudo apt install supervisor -y

# 2. Tạo config file
sudo nano /etc/supervisor/conf.d/round-process-loop.conf
```

Thêm nội dung sau (thay `/path/to/your/project` bằng đường dẫn thực tế):

```ini
[program:round-process-loop]
process_name=%(program_name)s
command=php /path/to/your/project/artisan round:process-loop
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/round-process.log
stopwaitsecs=3600
```

```bash
# 3. Reload Supervisor config
sudo supervisorctl reread
sudo supervisorctl update

# 4. Start service
sudo supervisorctl start round-process-loop

# 5. Kiểm tra status
sudo supervisorctl status round-process-loop

# 6. Xem logs
sudo tail -f /path/to/your/project/storage/logs/round-process.log

# 7. Dừng/Restart
sudo supervisorctl stop round-process-loop
sudo supervisorctl restart round-process-loop
```

### Phương pháp 5: Systemd Service (Production, khuyến nghị)

Tạo systemd service để quản lý như một service chính thức.

```bash
# 1. Tạo service file
sudo nano /etc/systemd/system/round-process-loop.service
```

Thêm nội dung sau (thay các giá trị phù hợp):

```ini
[Unit]
Description=Micex Round Process Loop
After=network.target mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/project
ExecStart=/usr/bin/php /path/to/your/project/artisan round:process-loop
Restart=always
RestartSec=10
StandardOutput=append:/path/to/your/project/storage/logs/round-process.log
StandardError=append:/path/to/your/project/storage/logs/round-process-error.log

[Install]
WantedBy=multi-user.target
```

```bash
# 2. Reload systemd
sudo systemctl daemon-reload

# 3. Enable service (tự động start khi boot)
sudo systemctl enable round-process-loop.service

# 4. Start service
sudo systemctl start round-process-loop.service

# 5. Kiểm tra status
sudo systemctl status round-process-loop.service

# 6. Xem logs
sudo journalctl -u round-process-loop.service -f
# Hoặc
tail -f /path/to/your/project/storage/logs/round-process.log

# 7. Dừng/Restart
sudo systemctl stop round-process-loop.service
sudo systemctl restart round-process-loop.service
```

### Script tự động dừng và chạy lại

Sử dụng script `restart-round-process-simple.sh` đã tạo sẵn:

```bash
# 1. Upload script lên VPS
# 2. Cấp quyền thực thi
chmod +x restart-round-process-simple.sh

# 3. Chạy script
./restart-round-process-simple.sh
```

### Kiểm tra Process đang chạy

```bash
# Kiểm tra process
ps aux | grep "round:process-loop"

# Kiểm tra port/process chi tiết
pgrep -f "round:process-loop"

# Xem logs real-time
tail -f storage/logs/round-process.log
```

### Khuyến nghị

- **Development/Testing**: Dùng **Screen** hoặc **Tmux** (đơn giản, dễ debug)
- **Production**: Dùng **Supervisor** hoặc **Systemd** (tự động restart, quản lý tốt hơn)

## 🚨 Troubleshooting

### Lỗi "Unable to locate package php8.2-*"

Nếu gặp lỗi này khi cài đặt PHP 8.2:

```bash
# 1. Đảm bảo đã cài đặt các package cần thiết
sudo apt install -y software-properties-common lsb-release ca-certificates apt-transport-https

# 2. Xóa và thêm lại repository
sudo add-apt-repository --remove ppa:ondrej/php -y
sudo add-apt-repository ppa:ondrej/php -y

# 3. Update lại package list
sudo apt update

# 4. Kiểm tra xem repository đã có PHP 8.2 chưa
apt-cache search php8.2 | head -10

# 5. Nếu vẫn không có, thử cài đặt PHP 8.1 (tương thích với Laravel 12)
sudo apt install -y php8.1-fpm php8.1-cli php8.1-common php8.1-mysql \
    php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml \
    php8.1-bcmath php8.1-intl php8.1-readline php8.1-sqlite3

# 6. Nếu dùng PHP 8.1, nhớ thay đổi trong Nginx config:
# fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
```

### Lỗi 502 Bad Gateway

```bash
# Kiểm tra PHP-FPM
sudo systemctl status php8.2-fpm
# Hoặc nếu dùng PHP 8.1:
sudo systemctl status php8.1-fpm

sudo tail -f /var/log/nginx/micex-error.log

# Kiểm tra socket
ls -la /var/run/php/php8.2-fpm.sock
# Hoặc nếu dùng PHP 8.1:
ls -la /var/run/php/php8.1-fpm.sock
```

### Lỗi Permission denied

```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/micex/storage
sudo chown -R www-data:www-data /var/www/micex/bootstrap/cache
sudo chmod -R 775 /var/www/micex/storage
sudo chmod -R 775 /var/www/micex/bootstrap/cache
```

### Round Timer không chạy

```bash
# Kiểm tra Round Timer service
sudo systemctl status micex-round-timer
sudo journalctl -u micex-round-timer -f

# Test command manually
cd /var/www/micex
php artisan round:process

# Kiểm tra xem round có đang chạy không
php artisan tinker
# Trong tinker:
# \App\Models\Round::getCurrentRound();
```

## 🔄 Cập nhật Code

### Khi có code mới

```bash
cd /var/www/micex

# Pull code mới (nếu dùng Git)
git pull origin main

# Cài đặt dependencies mới
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Chạy migrations
php artisan migrate --force

# Clear và cache lại
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Cache lại
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart micex-round-timer
```

## 📊 Monitoring

### Xem logs

```bash
# Laravel logs
tail -f /var/www/micex/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/micex-access.log
tail -f /var/log/nginx/micex-error.log

# Round Timer logs
sudo journalctl -u micex-round-timer -f
```

## 🔐 Security Checklist

- [ ] Đã set `APP_DEBUG=false` trong `.env`
- [ ] Đã set strong password cho database
- [ ] Đã cấu hình firewall (UFW)
- [ ] Đã cài đặt SSL certificate
- [ ] Đã set proper file permissions
- [ ] Đã disable các PHP functions không cần thiết
- [ ] Đã cấu hình security headers trong Nginx
- [ ] Đã tạo backup database thường xuyên

## 📞 Support

Nếu gặp vấn đề, kiểm tra:
1. Logs: `/var/www/micex/storage/logs/laravel.log`
2. Nginx error log: `/var/log/nginx/micex-error.log`
3. Service status: `sudo systemctl status <service-name>`

---

**Lưu ý**: Thay thế các giá trị như password, domain, và các thông tin nhạy cảm khác bằng giá trị thực tế của bạn.

