# Розгортання reNaI3es

## Вимоги
- PHP 8.2+ з розширеннями: `pdo_mysql`, `mbstring`, `json`, `curl`, `openssl`, `fileinfo`
- Опціонально: `sockets` (для Source Query), `sodium` (для шифрування секретів)
- MySQL 8 / MariaDB 10.5+
- nginx + php-fpm

## 1. Підготовка БД
```sql
CREATE DATABASE renai3es CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'renai3es'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL ON renai3es.* TO 'renai3es'@'localhost';
```

## 2. Завантаження схеми
```bash
php database/migrate.php
```

## 3. Конфігурація
```bash
cp config/config.example.php config/config.php
chmod 600 config/config.php
nano config/config.php
```
Заповніть:
- `db.*` — доступи до БД
- `crypto_key` — 32 байти hex: `php -r "echo bin2hex(random_bytes(32));"`
- `ip_hmac_secret` — випадковий рядок: `php -r "echo bin2hex(random_bytes(32));"`
- `cookie_secure` — `true` якщо HTTPS

## 4. nginx-конфіг

### Варіант А: піддомен
```nginx
server {
    listen 443 ssl http2;
    server_name team.example.com;
    root /var/www/renai3es/public;
    index index.php;

    location / {
        try_files $uri /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Заборонити доступ до конфігів
    location ~ ^/(config|storage|app|views|database|cron|docs|tests)/ {
        deny all;
    }
}
```

### Варіант Б: підпапка
```nginx
location /team/ {
    alias /var/www/renai3es/public/;
    try_files $uri $uri/ /team/index.php?$query_string;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 5. Права на теки
```bash
chmod -R 750 storage/
chown -R www-data:www-data storage/
```

## 6. Перший запуск
1. Відкрийте `https://team.example.com/install.php`
2. Створіть обліковий запис Owner
3. **Видаліть** `install.php` з сервера!
4. Увійдіть, налаштуйте 2FA

## 7. Cron
```bash
crontab -e
# Додати:
*/5 * * * * php /var/www/renai3es/cron/run.php >> /var/log/renai3es-cron.log 2>&1
```

## 8. Перевірка
```bash
php tests/smoke.php
```
