# كيفية تشغيل الموقع - MultiSaaS Bundle

## 🌐 الميثاق (Protocol)

المشروع **يدعم HTTP و HTTPS** حسب الإعدادات.

---

## 🚀 طرق التشغيل

### الطريقة 1: Apache Server (مُوصى بها)

#### المتطلبات:
- Apache مع mod_rewrite مفعّل
- PHP 8.1+
- MySQL

#### الخطوات:

1. **إعداد Virtual Host في Apache**

أضف هذا في ملف `httpd-vhosts.conf` أو `apache2.conf`:

```apache
<VirtualHost *:80>
    ServerName multisaas.local
    DocumentRoot "D:/www/multisaas-bundle"
    
    <Directory "D:/www/multisaas-bundle">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

2. **إضافة في ملف hosts** (C:\Windows\System32\drivers\etc\hosts):
```
127.0.0.1    multisaas.local
```

3. **الوصول للموقع**:
```
http://multisaas.local
```

---

### الطريقة 2: PHP Built-in Server (للتطوير السريع)

#### للتطوير المحلي فقط:

```bash
cd D:\www\multisaas-bundle\core
php -S localhost:8000 -t public ../server.php
```

ثم افتح المتصفح:
```
http://localhost:8000
```

**ملاحظة**: هذه الطريقة للتطوير فقط وليست للإنتاج!

---

### الطريقة 3: XAMPP / WAMP / Laragon

#### إذا كنت تستخدم XAMPP:

1. ضع المشروع في: `C:\xampp\htdocs\multisaas-bundle`
2. افتح: `http://localhost/multisaas-bundle`

#### إذا كنت تستخدم Laragon:

1. ضع المشروع في: `C:\laragon\www\multisaas-bundle`
2. افتح: `http://multisaas-bundle.test`

---

## 🔒 HTTPS (للإنتاج)

### تفعيل HTTPS:

1. **في ملف `.env`**:
```env
APP_URL=https://yourdomain.com
```

2. **إعداد Apache للـ SSL**:
```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot "D:/www/multisaas-bundle"
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    <Directory "D:/www/multisaas-bundle">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. **الكود يتحقق تلقائياً من HTTPS**:
```php
// في index.php - السطر 11
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
    $url = "https://";
} else {
    $url = "http://";
}
```

---

## ⚙️ الإعدادات المطلوبة

### 1. ملف `.env` في `core/.env`:

```env
# للتطوير المحلي (HTTP)
APP_URL=http://localhost
# أو
APP_URL=http://multisaas.local

# للإنتاج (HTTPS)
APP_URL=https://yourdomain.com

# النطاق المركزي
CENTRAL_DOMAIN=localhost
# أو للإنتاج
CENTRAL_DOMAIN=yourdomain.com
```

### 2. قاعدة البيانات:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multisaas_central
DB_USERNAME=root
DB_PASSWORD=
```

---

## 📋 خطوات التشغيل الكاملة

### 1. تأكد من وجود ملف `.env`:
```bash
cd D:\www\multisaas-bundle\core
# إذا لم يكن موجود، انسخ من .env.example
```

### 2. تثبيت التبعيات:
```bash
composer install
npm install
```

### 3. توليد المفتاح:
```bash
php artisan key:generate
```

### 4. إعداد قاعدة البيانات:
```bash
php artisan migrate
```

### 5. إنشاء Storage Link:
```bash
php artisan storage:link
```

### 6. تجميع Assets:
```bash
npm run dev
```

### 7. تشغيل الخادم:
- **Apache**: ابدأ Apache من XAMPP/Laragon
- **PHP Built-in**: `php -S localhost:8000`

### 8. الوصول للموقع:
```
http://localhost
# أو
http://multisaas.local
```

---

## 🔍 التحقق من التشغيل

### إذا ظهرت رسالة "Please install the script first":
✅ هذا طبيعي - يعني المشروع يحتاج تثبيت أولي
- افتح: `http://localhost/install`

### إذا ظهرت صفحة Laravel:
✅ المشروع يعمل بشكل صحيح!

### إذا ظهر خطأ 500:
- تحقق من ملف `.env`
- تحقق من صلاحيات المجلدات
- تحقق من Logs في `core/storage/logs/`

---

## 🌍 URLs المهمة

### بعد التثبيت:

```
Landlord Admin:     http://localhost/admin-home
Landlord Frontend:  http://localhost/
Tenant Registration: http://localhost/register
Tenant Admin:       http://subdomain.localhost/admin
Tenant Frontend:    http://subdomain.localhost/
```

---

## 💡 نصائح مهمة

1. **للتطوير**: استخدم HTTP (`http://localhost`)
2. **للإنتاج**: استخدم HTTPS (`https://yourdomain.com`)
3. **تأكد من**: `mod_rewrite` مفعّل في Apache
4. **تحقق من**: ملف `.htaccess` موجود في المجلد الجذر

---

## 🐛 حل المشاكل

### المشكلة: الموقع لا يعمل
**الحل**:
- تحقق من تشغيل Apache/PHP
- تحقق من ملف `.env`
- تحقق من Logs

### المشكلة: خطأ 404
**الحل**:
- تأكد من تفعيل `mod_rewrite`
- تحقق من ملف `.htaccess`

### المشكلة: Assets لا تعمل
**الحل**:
```bash
npm run dev
php artisan storage:link
```

---

## 📝 ملخص سريع

| البيئة | الميثاق | URL |
|--------|---------|-----|
| تطوير محلي | HTTP | `http://localhost` |
| تطوير محلي (مخصص) | HTTP | `http://multisaas.local` |
| إنتاج | HTTPS | `https://yourdomain.com` |

---

**الخلاصة**: 
- ✅ للتطوير: استخدم **HTTP** (`http://localhost`)
- ✅ للإنتاج: استخدم **HTTPS** (`https://yourdomain.com`)
- ✅ المشروع يدعم الاثنين تلقائياً!



