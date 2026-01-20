# البنية التقنية - MultiSaaS Bundle

## 🏛️ البنية المعمارية التفصيلية

### 1. Multi-Tenancy Architecture

#### النمط المستخدم: Database Per Tenant
كل مستأجر له:
- قاعدة بيانات منفصلة تماماً
- نطاق فرعي أو مخصص
- ملفات منفصلة (اختياري)
- Cache منفصل

#### Bootstrappers المستخدمة:
```php
- DatabaseTenancyBootstrapper    // قاعدة بيانات منفصلة
- CacheTenancyBootstrapper       // Cache منفصل
- FilesystemTenancyBootstrapper  // ملفات منفصلة
- QueueTenancyBootstrapper       // Queue منفصل
```

---

## 📁 هيكل المجلدات التفصيلي

### core/app/
```
app/
├── Actions/                    # Actions Pattern
│   ├── PaymentGateways.php
│   └── Tenant/                 # Tenant-specific Actions
├── Console/                    # Artisan Commands
│   └── Commands/
├── Enums/                      # PHP Enums
├── Events/                     # Event Classes
├── Exceptions/                 # Exception Handlers
├── Facades/                    # Custom Facades
├── Helpers/                    # Helper Functions
│   ├── DataTableHelpers/
│   ├── EmailHelpers/
│   ├── Payment/
│   ├── SeederHelpers/
│   └── TenantHelper/
├── Http/
│   ├── Controllers/
│   │   ├── Landlord/          # Central Admin Controllers
│   │   └── Tenant/            # Tenant Controllers
│   ├── Middleware/            # Custom Middleware
│   ├── Requests/              # Form Requests
│   └── Services/              # Service Classes
├── Jobs/                       # Queue Jobs
├── Listeners/                  # Event Listeners
├── Mail/                       # Mail Classes
├── Models/                     # Eloquent Models
├── Observers/                  # Model Observers
├── Providers/                 # Service Providers
├── Traits/                     # Reusable Traits
└── ...
```

### core/Modules/
كل وحدة تحتوي على:
```
ModuleName/
├── Config/                     # إعدادات الوحدة
├── Database/
│   ├── Migrations/            # Migrations
│   └── Seeders/              # Seeders
├── Entities/                  # Models
├── Http/
│   ├── Controllers/
│   │   ├── Landlord/         # Central Admin
│   │   ├── Tenant/           # Tenant Admin
│   │   └── Frontend/         # Frontend
│   ├── Middleware/
│   └── Requests/
├── Providers/
│   └── ModuleServiceProvider.php
├── Resources/
│   ├── views/                # Blade Templates
│   ├── lang/                 # Translations
│   └── assets/               # CSS, JS, Images
├── Routes/
│   ├── web.php
│   ├── admin.php
│   └── api.php
├── composer.json
├── module.json
└── package.json
```

---

## 🔄 تدفق الطلبات (Request Flow)

### Landlord (Central) Request:
```
1. Request → index.php
2. → Bootstrap Laravel
3. → Check .env exists
4. → Load core/vendor/autoload.php
5. → Load core/bootstrap/app.php
6. → Route Middleware:
   - landlord_glvar (Global Variables)
   - maintenance_mode
   - setlang (Language)
7. → Route Handler
8. → Controller
9. → Response
```

### Tenant Request:
```
1. Request → index.php
2. → Bootstrap Laravel
3. → Tenant Middleware:
   - InitializeTenancyByDomainCustomisedMiddleware
   - PreventAccessFromCentralDomains
   - tenant_glvar
   - setlang
   - package_expire
   - maintenance_mode
4. → Switch to Tenant Database
5. → Route Handler
6. → Controller
7. → Response
```

---

## 🗄️ قاعدة البيانات

### Central Database (Landlord)
```sql
-- الجداول الرئيسية
tenants              -- معلومات المستأجرين
domains              -- النطاقات
price_plans          -- خطط الأسعار
orders               -- الطلبات
payment_logs         -- سجلات الدفع
themes               -- القوالب
languages            -- اللغات
pages                -- الصفحات
menus                -- القوائم
widgets              -- الويدجت
static_options       -- الإعدادات العامة
static_options_central -- إعدادات مركزية
```

### Tenant Database (لكل مستأجر)
```sql
-- الجداول الأساسية لكل مستأجر
users                -- مستخدمي الموقع
admins              -- مدراء الموقع
-- + جداول الوحدات المفعّلة
```

---

## 🔐 نظام المصادقة

### Guards المستخدمة:
```php
'web'    => Laravel Session Guard (للمستخدمين العاديين)
'admin'  => Custom Guard (للمدراء)
```

### Middleware:
```php
- auth:web          // مصادقة المستخدمين
- auth:admin        // مصادقة المدراء
- userMailVerify    // التحقق من البريد
- Google2FA         // المصادقة الثنائية
- role:Super Admin  // صلاحيات محددة
```

---

## 💳 نظام الدفع

### Payment Gateway Flow:
```
1. User selects plan
2. → Create Order
3. → Select Payment Gateway
4. → Redirect to Gateway
5. → Payment Processing
6. → IPN Callback (Instant Payment Notification)
7. → Verify Payment
8. → Update Order Status
9. → Activate Tenant Subscription
10. → Send Confirmation Email
```

### Payment Logs:
- كل عملية دفع تُسجل في `payment_logs`
- IPN Callbacks تُعالج في `PaymentLogController`
- دعم Manual Payment Approval

---

## 🎨 نظام القوالب

### Theme Structure:
```
resources/views/themes/{theme_name}/
├── theme.json              # معلومات القالب
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── page-layout/       # Layouts للصفحات
└── views/                 # Blade Templates
```

### Theme Functions:
- `renderPrimaryThemeScreenshot()` - عرض لقطة القالب
- `theme_assets()` - الوصول لملفات القالب
- `get_theme_via_ajax()` - جلب القوالب عبر AJAX

---

## 📦 نظام الوحدات

### Module Registration:
```php
// في modules_statuses.json
{
  "ModuleName": true/false  // تفعيل/تعطيل الوحدة
}
```

### Module Loading:
```php
// في ModuleServiceProvider
- Register Routes
- Register Views
- Register Translations
- Register Migrations
- Register Commands
```

---

## 🔄 Events & Listeners

### Events الرئيسية:
```php
TenantRegisterEvent          // عند تسجيل مستأجر جديد
TenantNotificationEvent      // إشعارات المستأجر
TenantCronjobEvent          // Cronjobs للمستأجرين
SupportMessage              // رسائل الدعم
```

### Listeners:
```php
TenantDataSeedListener       // تهيئة بيانات المستأجر
TenantDomainCreate          // إنشاء النطاق
TenantNotificationListener  // معالجة الإشعارات
TenantCronjobListener       // معالجة Cronjobs
```

---

## 📊 Data Flow

### Tenant Registration Flow:
```
1. User fills registration form
2. → Validate subdomain availability
3. → Create Tenant record
4. → Create Domain record
5. → Create Tenant Database
6. → Run Migrations
7. → Seed Default Data
8. → Create Admin User
9. → Send Credentials Email
10. → Fire TenantRegisterEvent
```

### Order Processing Flow:
```
1. User selects Price Plan
2. → Create Order (pending)
3. → Redirect to Payment Gateway
4. → Payment Success/Cancel
5. → IPN Callback
6. → Verify Payment
7. → Update Order Status
8. → Assign Subscription to Tenant
9. → Update Tenant Expiry Date
10. → Send Confirmation Email
```

---

## 🛠️ Service Providers

### Custom Providers:
```php
AppServiceProvider           // إعدادات عامة
TenancyServiceProvider      // إعدادات Multi-Tenancy
RouteServiceProvider        // إعدادات المسارات
BladeDirectiveServiceProvider // Blade Directives
MacroServiceProvider        // Macros
EventServiceProvider        // Events & Listeners
```

---

## 🔍 Helpers & Facades

### Custom Facades:
```php
EmailTemplate               // قوالب البريد
GlobalLanguage             // اللغة العامة
LandlordAdminMenu          // قائمة المدير
ModuleDataFacade           // بيانات الوحدات
ThemeDataFacade            // بيانات القوالب
```

### Helper Functions:
```php
// في app/Helpers/
- funtions.php            // دوال عامة
- module-helper.php       // دوال الوحدات
- theme-helper.php        // دوال القوالب
- Payment/*               // دوال الدفع
- TenantHelper/*          // دوال المستأجرين
```

---

## 📧 نظام البريد

### Mail Classes:
```php
BasicMail                  // بريد أساسي
BasicDynamicTemplateMail   // قالب ديناميكي
TenantCredentialMail       // بيانات اعتماد المستأجر
PlaceOrder                 // تأكيد الطلب
ProductOrderEmail          // بريد طلب المنتج
AppointmentMail            // بريد الموعد
EventMail                  // بريد الفعالية
DonationMail               // بريد التبرع
```

### SMTP Configuration:
- إعدادات SMTP في General Settings
- دعم Send Test Mail
- قوالب قابلة للتخصيص

---

## 🎯 Middleware Stack

### Global Middleware:
```php
- \Illuminate\Foundation\Http\Middleware\ValidatePostSize
- \App\Http\Middleware\TrimStrings
- \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull
```

### Route Middleware:
```php
- landlord_glvar          // متغيرات عامة للـ Landlord
- tenant_glvar            // متغيرات عامة للـ Tenant
- setlang                 // تعيين اللغة
- maintenance_mode        // وضع الصيانة
- package_expire          // التحقق من انتهاء الخطة
- userMailVerify          // التحقق من البريد
- Google2FA               // المصادقة الثنائية
- adminglobalVariable     // متغيرات المدير
```

---

## 🔐 Security Features

### Protection Layers:
1. **CSRF Protection** - Laravel built-in
2. **XSS Protection** - HTML Purifier (mews/purifier)
3. **SQL Injection** - Eloquent ORM
4. **Password Hashing** - bcrypt
5. **Rate Limiting** - API & Routes
6. **2FA** - Google Authenticator
7. **Activity Logging** - Spatie Activity Log
8. **Permission System** - Spatie Permissions

---

## 📱 API Structure

### Authentication:
- Sanctum Token-based
- API Rate Limiting
- Token Management

### Endpoints:
```
/api/v1/
├── auth/              # المصادقة
├── tenants/           # المستأجرين
├── orders/            # الطلبات
└── ...
```

---

## 🚀 Performance Optimizations

### Caching:
- Route Caching
- Config Caching
- View Caching
- Query Caching (per tenant)

### Queue System:
- Async Jobs for heavy operations
- Email Queue
- File Sync Queue
- Tenant Operations Queue

### Database:
- Indexes on foreign keys
- Eager Loading relationships
- Query Optimization

---

## 📝 Code Patterns Used

### Design Patterns:
- **Repository Pattern** (في بعض الأماكن)
- **Service Pattern** (Services في Http/Services)
- **Action Pattern** (Actions/)
- **Observer Pattern** (Observers/)
- **Facade Pattern** (Custom Facades)
- **Factory Pattern** (Database Factories)

### Laravel Patterns:
- **Form Requests** - للتحقق من البيانات
- **Resource Controllers** - RESTful
- **Policies** - للصلاحيات
- **Events & Listeners** - للتفاعلات
- **Jobs & Queues** - للمهام الثقيلة

---

## 🔄 Cron Jobs

### Scheduled Tasks:
```php
// في app/Console/Kernel.php
- PackageExpireCommand        // فحص انتهاء الخطط
- PackageAutoRenewUsingWallet // تجديد تلقائي
- AccountRemoval              // إزالة الحسابات
- WebsiteHealthChecker        // فحص صحة المواقع
- TenantCleanup               // تنظيف المستأجرين
```

---

## 📊 Monitoring & Logging

### Logging:
- Laravel Log System
- Activity Log (Spatie)
- Cronjob Logs
- Payment Logs
- Tenant Exception Logs

### Health Checks:
- Website Health Checker Command
- Database Connection Check
- Payment Gateway Status

---

## 🌐 Internationalization

### Language System:
- Multi-language support
- RTL Support
- Dynamic Language Switching
- Translation Management
- Language-specific Content

### Language Files:
```
resources/lang/
├── en_US.json
├── ar.json
├── fr_FR.json
└── ... (30+ languages)
```

---

## 🎨 Frontend Architecture

### Assets:
- Laravel Mix for compilation
- Bootstrap 5
- jQuery (في بعض الأماكن)
- Axios for AJAX
- Sass for styling

### Build Process:
```bash
npm run dev          # Development
npm run production   # Production
npm run watch        # Watch mode
```

---

## 🔧 Configuration Files

### Important Configs:
```
config/
├── app.php              # إعدادات التطبيق
├── database.php         # إعدادات قاعدة البيانات
├── tenancy.php          # إعدادات Multi-Tenancy
├── auth.php             # إعدادات المصادقة
├── mail.php             # إعدادات البريد
├── filesystems.php      # إعدادات الملفات
├── cache.php            # إعدادات Cache
├── queue.php            # إعدادات Queue
└── ...
```

---

## 📚 Dependencies Overview

### Core Dependencies:
- Laravel Framework 10.x
- PHP 8.1+
- MySQL/PostgreSQL
- Composer
- Node.js & NPM

### Key Packages:
- Multi-Tenancy: stancl/tenancy
- Modules: nwidart/laravel-modules
- Permissions: spatie/laravel-permission
- Activity Log: spatie/laravel-activitylog
- Payments: xgenious/paymentgateway
- SEO: artesaos/seotools
- PDF: barryvdh/laravel-dompdf

---

**ملاحظة**: هذا تحليل تقني شامل للبنية. للمزيد من التفاصيل، راجع ملفات الكود المصدري.



