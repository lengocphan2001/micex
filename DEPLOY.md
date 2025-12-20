# Hướng dẫn Deploy Micex lên VPS Ubuntu 22.04

Hướng dẫn chi tiết để deploy ứng dụng Laravel Micex lên VPS Ubuntu 22.04 với Nginx, SSL và các cấu hình cần thiết.

## 📋 Yêu cầu hệ thống

- **OS**: Ubuntu 22.04 LTS
- **PHP**: 8.2 hoặc cao hơn
- **Database**: MySQL 8.0+ hoặc MariaDB 10.6+
- **Web Server**: Nginx
- **SSL**: Let's Encrypt (Certbot)
- **Domain**: mon88.click (đã trỏ về IP VPS)

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

### 2.6. Cài đặt Redis (cho queue và cache)

```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
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
APP_URL=https://mon88.click

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=micex
DB_USERNAME=micex_user
DB_PASSWORD=password

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=mon88.click
REVERB_PORT=443
REVERB_SCHEME=https

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
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
    server_name mon88.click www.mon88.click;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name mon88.click www.mon88.click;
    
    root /var/www/micex/public;
    index index.php index.html;

    # SSL Configuration (sẽ được cấu hình bởi Certbot)
    ssl_certificate /etc/letsencrypt/live/mon88.click/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/mon88.click/privkey.pem;
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
    client_max_body_size 20M;

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
sudo certbot --nginx -d mon88.click -d www.mon88.click
```

Certbot sẽ tự động:
- Tạo SSL certificate
- Cấu hình Nginx để sử dụng SSL
- Thiết lập auto-renewal

### 6.3. Test auto-renewal

```bash
sudo certbot renew --dry-run
```

## 🔄 Bước 7: Cấu hình Reverb (WebSocket)

### 7.1. Tạo systemd service cho Reverb

```bash
sudo nano /etc/systemd/system/reverb.service
```

Nội dung:

```ini
[Unit]
Description=Laravel Reverb Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/micex
ExecStart=/usr/bin/php /var/www/micex/artisan reverb:start --host=0.0.0.0 --port=8080
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

### 7.2. Enable và start Reverb service

```bash
sudo systemctl daemon-reload
sudo systemctl enable reverb
sudo systemctl start reverb
sudo systemctl status reverb
```

### 7.3. Cấu hình Nginx proxy cho Reverb

Cập nhật file Nginx config:

```bash
sudo nano /etc/nginx/sites-available/micex
```

Thêm vào trong block `server` (sau location /):

```nginx
    # Reverb WebSocket proxy
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
    }
```

Reload Nginx:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

## ⚙️ Bước 8: Cấu hình Queue Worker

### 8.1. Tạo systemd service cho Queue

```bash
sudo nano /etc/systemd/system/micex-queue.service
```

Nội dung:

```ini
[Unit]
Description=Micex Queue Worker
After=network.target redis.service mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/micex
ExecStart=/usr/bin/php /var/www/micex/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

### 8.2. Enable và start Queue service

```bash
sudo systemctl daemon-reload
sudo systemctl enable micex-queue
sudo systemctl start micex-queue
sudo systemctl status micex-queue
```

## ⏰ Bước 9: Cấu hình Cron Jobs

### 9.1. Cấu hình Laravel Scheduler

```bash
sudo crontab -e -u www-data
```

Thêm dòng:

```
* * * * * cd /var/www/micex && php artisan schedule:run >> /dev/null 2>&1
```

## 🔧 Bước 10: Cấu hình Firewall

### 10.1. Cấu hình UFW

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

## 📝 Bước 11: Cấu hình Log Rotation

### 11.1. Tạo logrotate config

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

## 🔍 Bước 12: Kiểm tra và Test

### 12.1. Kiểm tra services

```bash
# Check PHP-FPM
sudo systemctl status php8.2-fpm

# Check Nginx
sudo systemctl status nginx

# Check MySQL
sudo systemctl status mysql

# Check Redis
sudo systemctl status redis-server

# Check Reverb
sudo systemctl status reverb

# Check Queue
sudo systemctl status micex-queue
```

### 12.2. Test website

```bash
# Test từ server
curl -I https://mon88.click

# Kiểm tra SSL
openssl s_client -connect mon88.click:443
```

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

### Reverb không kết nối được

```bash
# Kiểm tra Reverb service
sudo systemctl status reverb
sudo journalctl -u reverb -f

# Kiểm tra port
sudo netstat -tulpn | grep 8080
```

### Queue không chạy

```bash
# Kiểm tra Queue service
sudo systemctl status micex-queue
sudo journalctl -u micex-queue -f

# Test queue manually
cd /var/www/micex
php artisan queue:work redis --once
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
sudo systemctl restart reverb
sudo systemctl restart micex-queue
```

## 📊 Monitoring

### Xem logs

```bash
# Laravel logs
tail -f /var/www/micex/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/micex-access.log
tail -f /var/log/nginx/micex-error.log

# Reverb logs
sudo journalctl -u reverb -f

# Queue logs
sudo journalctl -u micex-queue -f
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

