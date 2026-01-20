# دليل البدء السريع - MultiSaaS Bundle

## 🚀 خطوات البدء

### 1. متطلبات النظام

```bash
✅ PHP ^8.1
✅ Composer
✅ Node.js & NPM
✅ MySQL 5.7+ أو PostgreSQL 10+
✅ Apache/Nginx مع mod_rewrite
✅ Extension: GD, JSON
```

### 2. تثبيت المشروع

#### أ. نسخ المشروع
```bash
# المشروع موجود في: D:\www\multisaas-bundle
cd D:\www\multisaas-bundle
```

#### ب. تثبيت التبعيات PHP
```bash
cd core
composer install
```

#### ج. تثبيت التبعيات JavaScript
```bash
npm install
```

### 3. إعداد ملف البيئة (.env)

#### أ. نسخ ملف المثال
```bash
# إذا كان موجود
cp .env.example .env

# أو إنشاء ملف جديد
```

#### ب. إعدادات مهمة في .env
```env
APP_NAME="MultiSaaS Bundle"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# قاعدة البيانات المركزية (Landlord)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multisaas_central
DB_USERNAME=root
DB_PASSWORD=

# النطاق المركزي
CENTRAL_DOMAIN=localhost

# البريد الإلكتروني
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. إعداد قاعدة البيانات

#### أ. إنشاء قاعدة البيانات المركزية
```sql
CREATE DATABASE multisaas_central CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### ب. تشغيل Migrations
```bash
cd core
php artisan migrate
```

#### ج. تشغيل Seeders (اختياري)
```bash
php artisan db:seed
```

### 5. توليد مفتاح التطبيق
```bash
php artisan key:generate
```

### 6. إنشاء Storage Link
```bash
php artisan storage:link
```

### 7. تجميع Assets
```bash
# للتطوير
npm run dev

# للإنتاج
npm run production
```

### 8. إعداد الخادم

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ index.php [L]
</IfModule>
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 9. الوصول للمشروع

#### Landlord (Central) Admin
```
URL: http://localhost/admin-home
Default: قد تحتاج إنشاء حساب مدير أول
```

#### Tenant Registration
```
URL: http://localhost/register
```

---

## 📁 أين تعمل؟

### للوصول عبر المتصفح:
```
المجلد الجذر: D:\www\multisaas-bundle
Document Root: D:\www\multisaas-bundle
```

### لأوامر التطوير:
```bash
cd D:\www\multisaas-bundle\core
# ثم نفّذ الأوامر
php artisan ...
composer ...
npm ...
```

---

## 🔧 الأوامر المفيدة

### Artisan Commands
```bash
# قائمة الأوامر
php artisan list

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrations
php artisan migrate
php artisan migrate:refresh
php artisan migrate:rollback

# Create
php artisan make:controller ControllerName
php artisan make:model ModelName
php artisan make:migration create_table_name
```

### Module Commands
```bash
# إنشاء وحدة جديدة
php artisan module:make ModuleName

# تفعيل/تعطيل وحدة
php artisan module:enable ModuleName
php artisan module:disable ModuleName
```

---

## 🗂️ هيكل العمل

### عند تعديل الكود:

#### Controllers
```
core/app/Http/Controllers/
├── Landlord/        # لوحة تحكم المركزية
└── Tenant/          # لوحة تحكم المستأجرين
```

#### Models
```
core/app/Models/     # النماذج المركزية
core/Modules/{Module}/Entities/  # نماذج الوحدات
```

#### Views
```
core/resources/views/           # Views عامة
core/Modules/{Module}/Resources/views/  # Views الوحدات
```

#### Routes
```
core/routes/
├── web.php          # مسارات الويب
├── admin.php        # مسارات المدير
├── tenant.php       # مسارات المستأجر
└── api.php          # مسارات API
```

---

## 🧪 الاختبار

### تشغيل Tests
```bash
php artisan test
# أو
phpunit
```

---

## 🐛 حل المشاكل الشائعة

### 1. خطأ "Please install the script first"
**الحل**: تأكد من وجود ملف `.env` في `core/`

### 2. خطأ Database Connection
**الحل**: 
- تحقق من إعدادات `.env`
- تأكد من تشغيل MySQL
- تحقق من صلاحيات المستخدم

### 3. خطأ Permission Denied
**الحل**:
```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache

# Windows: تأكد من صلاحيات الكتابة
```

### 4. خطأ Class Not Found
**الحل**:
```bash
composer dump-autoload
```

### 5. Assets لا تعمل
**الحل**:
```bash
npm install
npm run dev
```

---

## 📚 مصادر مفيدة

### الوثائق الرسمية:
- [Laravel Docs](https://laravel.com/docs/10.x)
- [Tenancy Package](https://tenancyforlaravel.com/docs/v3/)
- [Laravel Modules](https://nwidart.com/laravel-modules/v8)

### ملفات المشروع:
- `PROJECT_ANALYSIS.md` - تحليل شامل للمشروع
- `TECHNICAL_ARCHITECTURE.md` - البنية التقنية

---

## 🎯 الخطوات التالية

1. ✅ إعداد المشروع
2. ✅ إنشاء حساب مدير أول
3. ✅ إنشاء خطة أسعار تجريبية
4. ✅ تسجيل مستأجر تجريبي
5. ✅ استكشاف الوحدات المتاحة
6. ✅ تخصيص القوالب
7. ✅ إعداد بوابات الدفع

---

## 💡 نصائح مهمة

1. **احتفظ بنسخة احتياطية** من قاعدة البيانات قبل أي تعديلات كبيرة
2. **استخدم Git** لتتبع التغييرات
3. **اقرأ الوثائق** قبل تعديل الكود الأساسي
4. **اختبر في بيئة تطوير** قبل النشر
5. **راقب Logs** في `storage/logs/`

---

## 🔐 الأمان

### قبل النشر:
- [ ] غيّر `APP_DEBUG=false`
- [ ] غيّر `APP_ENV=production`
- [ ] استخدم HTTPS
- [ ] غيّر كلمات المرور الافتراضية
- [ ] فعّل Firewall
- [ ] راجع صلاحيات الملفات

---

**ملاحظة**: هذا دليل سريع. للتفاصيل الكاملة، راجع ملفات التحليل الأخرى.



