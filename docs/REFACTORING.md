# TravianZ - Refactoring Roadmap

## ظرفیت کاربری فعلی (Current User Capacity)

با توجه به معماری فعلی، این پروژه به صورت عملی می‌تواند **حدود ۱۵۰۰ تا ۳۰۰۰ کاربر همزمان فعال** را پشتیبانی کند. این عدد به عوامل زیر بستگی دارد:

| عامل | تأثیر | وضعیت فعلی |
|------|-------|-----------|
| **سیستم Cron (Automation)** | بحرانی‌ترین گلوگاه | تمام عملیات بازی (تولید منابع، صف ساخت، حرکات نیرو، نبردها) در یک حلقه تک‌نخی پردازش می‌شود |
| **حافظه درخواستی (Per-Request Cache)** | بالا | هر درخواست AJAX از کلاینت، همه دیتا را دوباره از دیتابیس می‌خواند |
| **سایز نقشه (WORLD_MAX)** | بالا | نقشه ۱۰۰×۱۰۰ (۱۰,۰۰۰ خانه) بدون مشکل. نقشه ۱۰۰۰×۱۰۰۰ (۱ میلیون خانه) باعث slowdown شدید می‌شود |
| **MySQL InnoDB** | متوسط | ایندکس‌گذاری منطقی، ولی نبود foreign key و normalization ضعیف در برخی جداول |
| **Apache + PHP-FPM** | متوسط | هر درخواست یک پروسس PHP جداگانه می‌خواهد |
| **AJAX Polling** | بالا | کلاینت‌ها هر چند ثانیه یکبار سرور را Poll می‌کنند |

### گلوگاه‌های اصلی

1. **Cron تک‌نخی**: فایل `Automation.php` با قفل فایلی (`file_put_contents`) تمام پردازش‌ها را سریال انجام می‌دهد.
2. **نبود کش خارجی**: کش‌ها در static property های PHP نگهداری می‌شوند (هیچ Redis/Memcached)
3. **AJAX Polling**: به جای WebSocket، درخواست‌های مکرر HTTP زده می‌شود
4. **جداول غیرنرمال**: جدول `enforcement` با ۹۰ ستون برای نیروها، جدول `fdata` با ۸۰ ستون

---

## نقشه راه بازنویسی کامل (Full Rewrite Roadmap)

### 🎯 هدف
یک **API سرورساید مدرن** + **اپلیکیشن چند پلتفرمی** (وب، موبایل، دسکتاپ)

### 📋 استک پیشنهادی

| لایه | تکنولوژی | دلیل انتخاب |
|------|----------|------------|
| **Backend API** | **NestJS (Node.js + TypeScript)** یا **Go** | NestJS: معماری ماژولار عالی برای بازی‌های بزرگ. Go: کارایی فوق‌العاده برای پردازش‌های همزمان |
| **گزینه دوم بکند** | **Laravel + Octane (Swoole)** | اگر بخواهید در PHP بمانید. برنامه را در RAM نگه می‌دارد |
| **Web Framework** | **React (Next.js)** | رایج‌ترین، جامعه بزرگ، قابلیت SSR |
| **Mobile** | **React Native / Expo** | اشتراک کد با وب |
| **Desktop** | **Tauri** | سبک، امن، Rust-based |
| **Database** | **PostgreSQL** | PostGIS برای نقشه، JSONB برای دیتای انعطاف‌پذیر، پارتیشن‌بندی جداول |
| **Cache** | **Redis** | کش، Session Store، Pub/Sub، صف‌ها |
| **Message Queue** | **BullMQ (Redis)** یا **RabbitMQ** | پردازش رویدادهای زمان‌بندی شده به جای Cron |
| **WebSocket** | **Socket.io** | چت، اعلان‌های زنده، آپدیت منابع |
| **Object Storage** | **MinIO / S3** | ذخیره آواتارها، عکس‌ها |
| **CI/CD** | **GitHub Actions** | تست، build، deploy اتوماتیک |

---

### 🏗️ معماری پیشنهادی

#### الگوی کلی: Modular Monolith + Event-Driven

**میکروسرویس نکنید!** بازی‌های استراتژی نیاز به تراکنش‌های ACID strict دارند:
- برداشت منابع + شروع ساخت ساختمان ← باید در یک تراکنش باشند
- حرکت نیرو + برداشت از دهکده مبدا ← باید atomic باشند

در عوض یک **Modular Monolith** با Domain‌های مجزا:

```
backend/
├── src/
│   ├── modules/
│   │   ├── auth/            # ثبت‌نام، ورود، احراز هویت
│   │   ├── village/         # دهکده‌ها، منابع، ساختمان‌ها
│   │   ├── military/        # نیروها، نبردها، حرکات
│   │   ├── map/             # نقشه، Oasis
│   │   ├── alliance/        # اتحادها، فروم، دیپلماسی
│   │   ├── hero/            # هیرو، آیتم‌ها، ماجراجویی
│   │   ├── market/          # بازار، تجارت
│   │   ├── messages/        # پیام‌ها، گزارش‌ها
│   │   ├── rankings/        # رتبه‌بندی‌ها
│   │   ├── world/           # WW, Natars, Artifacts
│   │   └── admin/           # پنل ادمین
│   ├── common/
│   │   ├── database/        # اتصال DB، Repository pattern
│   │   ├── cache/           # Redis wrapper
│   │   ├── queue/           # Job definitions
│   │   ├── websocket/       # WebSocket gateway
│   │   └── config/          # Configuration
│   └── main.ts
├── tests/
└── docker-compose.yml
```

#### حذف Cron سنتی → معماری Event-Driven

❌ **روش قدیم**: هر ۶۰ ثانیه کل بازی را اسکن کن، ببین چی تموم شده...
✅ **روش جدید**:

```typescript
// وقتی کاربر ساختمانی را شروع می‌کند:
@Injectable()
class BuildingService {
  async startConstruction(userId, villageId, buildingType) {
    // 1. برداشت منابع (atomic transaction)
    await this.deductResources(villageId, cost);
    
    // 2. ایجاد Job با delay = زمان ساخت
    await this.buildQueue.add('building-complete', {
      userId, villageId, buildingType,
      targetLevel
    }, { delay: constructionTime * 1000 });
    
    // 3. اطلاع‌رسانی WebSocket
    this.wsGateway.emit('building-started', { userId, villageId, buildingType });
  }
}
```

هر اکشن بازی تبدیل به یک **Job** با delay می‌شود. این یعنی:
- **Scalable**: می‌توان Worker های متعدد داشت
- **دقیق**: هر job در زمان خودش اجرا می‌شود، بدون اسکن کل دیتابیس
- **مقاوم به خطا**: Redis persistence، تلاش مجدد در صورت شکست

---

### 🗄️ استراتژی دیتابیس

#### مهاجرت از MySQL به PostgreSQL

| نیاز | MySQL فعلی | PostgreSQL پیشنهادی |
|------|-----------|-------------------|
| نقشه ۱M خانه | `wdata` جدول ساده | **PostGIS** + spatial indexing |
| نیروها (۹۰ ستون) | `enforcement.u1-u90` | **JSONB** column |
| تایم‌لاین رویدادها | `int(11)` unix timestamps | `TIMESTAMPTZ` |
| Full-text search | ندارد | `tsvector` برای جستجوی پیام‌ها |
| پارتیشن‌بندی | ندارد | جدول `movement` پارتیشن‌بندی شده بر اساس زمان |

#### Schema Optimization

```sql
-- جایگزین enforcement.u1-u90:
CREATE TABLE enforcement (
    id SERIAL PRIMARY KEY,
    from_village_id INT,
    to_village_id INT,
    troops JSONB NOT NULL DEFAULT '{}',  -- {"u1": 100, "u2": 50, ...}
    hero BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_enforcement_troops ON enforcement USING GIN (troops);
```

```sql
-- نقشه با PostGIS:
CREATE TABLE map_tiles (
    id SERIAL PRIMARY KEY,
    geom GEOMETRY(Point, 4326),
    tile_type VARCHAR(20),  -- 'village', 'oasis', 'empty'
    occupied_by INT,
    wood_bonus INT,
    clay_bonus INT,
    iron_bonus INT,
    crop_bonus INT
);

CREATE INDEX idx_map_geom ON map_tiles USING GIST (geom);
```

#### لایه Cache با Redis

```typescript
// منابع دهکده: محاسبه زنده + کش ۵ ثانیه
async getVillageResources(villageId: number): Promise<Resources> {
  const cacheKey = `village:${villageId}:resources`;
  
  let resources = await this.redis.get(cacheKey);
  if (!resources) {
    resources = await this.calculateLiveResources(villageId);
    await this.redis.set(cacheKey, resources, 'EX', 5);
  }
  
  return resources;
}
```

---

### 🔄 Real-Time Features (WebSocket)

جایگزینی AJAX Polling با WebSocket:

| Feature | روش قدیم | روش جدید |
|---------|---------|---------|
| آپدیت منابع | هر ۳ ثانیه AJAX | Push با WebSocket |
| اعلان حمله | Polling | Instant push |
| چت اتحاد | Polling | WebSocket room |
| صف ساخت | Polling | Event push |
| نقشه | AJAX scroll | WebSocket + canvas rendering |

```
Client (Web/Mobile/Desktop)
    ↕ WebSocket (Socket.io)
        ├── resource:update       (هر ثانیه برای دهکده فعال)
        ├── attack:incoming       (فقط موقع حمله)
        ├── chat:alliance         (اتاق اتحاد)
        ├── queue:building        (تغییر وضعیت صف)
        └── notification:new      (اعلان‌های جدید)

Client (Web/Mobile/Desktop)
    ↕ REST API (HTTP)
        ├── POST /auth/login
        ├── POST /villages/:id/build
        ├── POST /military/attack
        └── GET /map/:x/:y
```

---

### 📱 استراتژی چند پلتفرمی

```
shared/                          # کد مشترک
├── types/                       # TypeScript interfaces
├── api/                         # API client (fetch wrapper)
├── hooks/                       # React hooks مشترک
├── utils/                       # منطق مشترک (محاسبات بازی)
└── constants/                   # ثابت‌های بازی

web/                             # Next.js - Web App
├── pages/, components/, styles/

mobile/                          # React Native / Expo
├── screens/, components/

desktop/                         # Tauri + React
├── src-tauri/ (Rust shell)
└── src/ (React renderer)
```

> **توصیه**: React Native Web را بررسی کنید — یک کدبیس برای وب و موبایل!

---

### 🐳 Docker Deployment (Production)

```yaml
# docker-compose.prod.yml
services:
  api:
    build: ./backend
    ports: ["3000:3000"]
    depends_on: [postgres, redis]
    deploy:
      replicas: 3              # Horizontal scaling
    
  worker:
    build: ./backend
    command: node dist/worker.js
    deploy:
      replicas: 2              # Queue workers
    
  websocket:
    build: ./backend
    command: node dist/websocket.js
    ports: ["3001:3001"]
    
  postgres:
    image: postgis/postgis:16
    volumes: [pgdata:/var/lib/postgresql/data]
    
  redis:
    image: redis:7-alpine
    command: redis-server --appendonly yes
    
  web:
    build: ./web
    ports: ["80:3000"]
    
  nginx:
    image: nginx:alpine
    ports: ["443:443"]
    volumes: [./nginx.conf:/etc/nginx/nginx.conf]
```

---

### 📈 استراتژی مهاجرت تدریجی (Strangler Fig Pattern)

بازنویسی یکباره کل پروژه (Big Bang) خطرناک است. به جاش:

```
فاز ۱: پاکسازی (هفته ۱-۲)
├── رفع آسیب‌پذیری‌های بحرانی (SQLi، XSS، CSRF)
├── حذف backdoor ادمین
├── اصلاح error_reporting
└── جداسازی config.php از web root

فاز ۲: API Facade (هفته ۳-۶)
├── ساخت API Gateway (NestJS) متصل به دیتابیس موجود
├── پیاده‌سازی REST endpoints موازی با GameEngine قدیم
├── استفاده از Redis برای کش
└── اجرای همزمان API جدید و PHP قدیم روی یک DB

فاز ۳: Frontend جدید (هفته ۷-۱۴)
├── ساخت React SPA
├── ارتباط با API جدید
├── جایگزینی تدریجی صفحات (dorf1 → /village)
└── اجرای همزمان MooTools قدیم و React جدید

فاز ۴: انتقال منطق بازی (هفته ۱۵-۲۴)
├── انتقال صف‌های ساخت به BullMQ
├── انتقال سیستم نبرد به سرویس جدید
├── انتقال سیستم Alliance
└── غیرفعال کردن تدریجی بخش‌های Automation.php

فاز ۵: مهاجرت دیتابیس (هفته ۲۵-۳۰)
├── ایجاد schema جدید PostgreSQL
├── Script انتقال داده
├── تست همزمان روی هر دو DB
└── Cut-over نهایی

فاز ۶: اپلیکیشن‌های بومی (هفته ۳۱-۳۶)
├── React Native mobile app
├── Tauri desktop app
└── PWA برای fallback
```

---

### 💰 هزینه تخمینی (توسعه)

| فاز | زمان تخمینی | تعداد Developer |
|-----|-----------|----------------|
| پاکسازی | ۲ هفته | ۱ Backend |
| API Facade | ۴ هفته | ۲ Backend |
| Frontend جدید | ۸ هفته | ۲ Frontend + ۱ Designer |
| انتقال منطق بازی | ۱۰ هفته | ۳ Backend |
| مهاجرت DB | ۶ هفته | ۱ Backend + ۱ DBA |
| اپلیکیشن‌ها | ۶ هفته | ۱ Mobile + ۱ Desktop |

**مجموع**: حدود **۳۶ هفته** با تیم ۴-۶ نفره

---

### ✅ جمع‌بندی

| سوال | جواب |
|------|------|
| کاربران فعلی | ۱,۵۰۰-۳,۰۰۰ همزمان |
| کاربران بعد از ریفکتور | ۱۰,۰۰۰-۵۰,۰۰۰+ همزمان |
| بهترین زبان بکند | NestJS (Node/TS) یا Go |
| بهترین فریمورک فرانت | React (Next.js) + React Native |
| دیتابیس | PostgreSQL + PostGIS + Redis |
| معماری | Modular Monolith + Event Sourcing |
| زمان بازنویسی کامل | ۸-۹ ماه با تیم ۴-۶ نفره |
| روش مهاجرت | تدریجی (Strangler Fig) |
