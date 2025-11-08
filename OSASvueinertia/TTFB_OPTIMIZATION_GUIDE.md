# TTFB (Time To First Byte) Optimization Guide

## 🔴 Current Issue
**TTFB**: 730ms (Target: <600ms, Ideal: <200ms)
**Impact**: 52% of your LCP time

## 🎯 Root Cause Analysis

Your Laravel app is using:
- ❌ `SESSION_DRIVER=database` - Every request hits the database
- ❌ `CACHE_STORE=database` - Cache operations hit the database
- ❌ Multiple middleware in the pipeline
- ❌ No OPcache optimization
- ❌ Potential N+1 query issues

## 🚀 Immediate Fixes (No Infrastructure Change)

### 1. Switch to File-Based Sessions (Quick Win)

**Change in Railway Environment Variables:**
```env
SESSION_DRIVER=file
CACHE_STORE=file
```

**Why?**
- Database sessions = 2-3 queries per request
- File sessions = Fast disk I/O (especially on Railway's NVMe)
- Reduces TTFB by ~200-300ms immediately

### 2. Enable OPcache (Critical for Production)

**Create/Update: `php.ini` or Railway Config**
```ini
[opcache]
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.fast_shutdown=1
```

**For Railway, add to your Nixpacks config:**
```toml
# In nixpacks.toml
[phases.setup]
nixPkgs = ["php82", "php82Extensions.opcache", ...]

[phases.install]
cmds = [
    "echo 'opcache.enable=1' >> /etc/php/8.2/cli/php.ini",
    "echo 'opcache.memory_consumption=256' >> /etc/php/8.2/cli/php.ini",
    # ... other opcache settings
]
```

**Expected Reduction**: 150-250ms

### 3. Cache Configuration Files (Laravel)

**Run these commands locally and commit:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Add to your deployment script:**
```bash
# In your Railway deployment or Procfile
php artisan optimize
```

**Expected Reduction**: 50-100ms

### 4. Optimize Middleware Pipeline

**Remove unused middleware from global stack:**

```php
// app/Http/Kernel.php
protected $middleware = [
    \App\Http\Middleware\TrustProxies::class,
    \Illuminate\Http\Middleware\HandleCors::class,
    \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
    // REMOVE if not needed:
    // \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class, // Only if uploading large files
    \App\Http\Middleware\TrimStrings::class,
    \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
];

protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        // COMMENT OUT if not using shared errors:
        // \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \App\Http\Middleware\HandleInertiaRequests::class,
        // REMOVE preload headers for now - adds overhead:
        // \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        \App\Http\Middleware\HandleInertiaNotifications::class,
    ],
];
```

**Expected Reduction**: 30-50ms

### 5. Database Query Optimization

**Check your HandleInertiaRequests middleware:**

```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            // CACHE this instead of querying every request:
            'user' => $request->user() ? cache()->remember(
                'user_data_' . $request->user()->id, 
                300, // 5 minutes
                fn() => $request->user()->only('id', 'name', 'email')
            ) : null,
        ],
    ];
}
```

**Expected Reduction**: 20-50ms

## 💪 Medium-term Optimizations (Railway-specific)

### 6. Environment Variable Configuration

**Update Railway Environment Variables:**

```env
# Session & Cache
SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file

# Database Optimization
DB_CONNECTION=mysql
DB_SLOW_QUERY_LOG=true
DB_SLOW_QUERY_TIME=100

# PHP Optimization
PHP_OPCACHE_ENABLE=1
PHP_MEMORY_LIMIT=256M
PHP_MAX_EXECUTION_TIME=30

# Laravel Optimization
APP_DEBUG=false
APP_ENV=production
DEBUGBAR_ENABLED=false
LOG_LEVEL=warning
```

### 7. Railway-Specific: Static Asset Optimization

**Update your `nixpacks.toml`:**

```toml
[variables]
PHP_VERSION = "8.2"
NIXPACKS_MEMORY_LIMIT = "1024"

[phases.build]
cmds = [
    "composer install --no-dev --optimize-autoloader --no-interaction",
    "npm ci",
    "npm run build",
    "php artisan optimize"
]

[start]
cmd = "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"
```

### 8. Add Response Caching Middleware

**Create new middleware:**

```bash
php artisan make:middleware CacheResponse
```

```php
// app/Http/Middleware/CacheResponse.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    public function handle(Request $request, Closure $next, int $ttl = 60): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Don't cache authenticated routes (or customize as needed)
        if ($request->user()) {
            return $next($request);
        }

        $key = 'response_cache_' . md5($request->fullUrl());

        return cache()->remember($key, $ttl, function () use ($next, $request) {
            return $next($request);
        });
    }
}
```

**Register in Kernel:**
```php
protected $middlewareAliases = [
    // ... existing aliases
    'cache.response' => \App\Http\Middleware\CacheResponse::class,
];
```

**Use on routes:**
```php
// routes/web.php
Route::get('/login', [AuthController::class, 'login'])
    ->middleware('cache.response:120'); // Cache for 2 minutes
```

## 🏆 Advanced Optimizations (Infrastructure)

### 9. Redis Cache (Best Performance)

**If you add Redis to Railway:**

```env
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_CLIENT=phpredis
REDIS_HOST=redis.railway.internal
REDIS_PORT=6379
```

**Expected TTFB**: ~150-250ms

### 10. Database Connection Pooling

**Update database config:**

```php
// config/database.php
'mysql' => [
    // ... existing config
    'options' => [
        PDO::ATTR_PERSISTENT => true, // Connection pooling
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ],
],
```

### 11. CDN Integration

**For static assets, use Railway's CDN or Cloudflare:**

```php
// .env
ASSET_URL=https://cdn.railway.app/your-app
```

## 📊 Performance Monitoring

### Add Performance Headers

```php
// app/Http/Middleware/AddPerformanceHeaders.php
public function handle($request, Closure $next)
{
    $startTime = microtime(true);
    
    $response = $next($request);
    
    $duration = round((microtime(true) - $startTime) * 1000, 2);
    
    $response->headers->set('Server-Timing', "total;dur={$duration}");
    $response->headers->set('X-Response-Time', "{$duration}ms");
    
    return $response;
}
```

## 🎯 Implementation Priority

### Phase 1 (Do Now - 5 minutes)
1. ✅ Change `SESSION_DRIVER=file` in Railway
2. ✅ Change `CACHE_STORE=file` in Railway
3. ✅ Run `php artisan optimize` and deploy

**Expected Result**: TTFB ~450-500ms (35-40% improvement)

### Phase 2 (This Week)
4. ✅ Enable OPcache in production
5. ✅ Remove unused middleware
6. ✅ Cache shared Inertia data

**Expected Result**: TTFB ~300-350ms (55-60% improvement)

### Phase 3 (Next Sprint)
7. ✅ Add Redis to Railway
8. ✅ Implement response caching
9. ✅ Database query optimization

**Expected Result**: TTFB ~150-250ms (70-80% improvement)

## 🧪 Testing Commands

### Measure TTFB Locally
```bash
curl -o /dev/null -s -w "TTFB: %{time_starttransfer}s\nTotal: %{time_total}s\n" https://orbit-production.up.railway.app/login
```

### Check OPcache Status
```php
// Create: public/opcache-status.php (REMOVE after testing)
<?php
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    echo "OPcache Enabled: " . ($status['opcache_enabled'] ? 'YES' : 'NO') . "\n";
    echo "Memory Used: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
    echo "Hit Rate: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
} else {
    echo "OPcache not available\n";
}
```

### Monitor Database Queries
```php
// In AppServiceProvider::boot()
if (app()->environment('local')) {
    DB::listen(function ($query) {
        if ($query->time > 100) { // Slow queries > 100ms
            Log::warning('Slow Query', [
                'sql' => $query->sql,
                'time' => $query->time,
            ]);
        }
    });
}
```

## 📈 Expected Timeline

| Phase | Time | TTFB Reduction | Cost |
|-------|------|----------------|------|
| Current | - | 730ms | - |
| Phase 1 (File Cache) | 5 min | 450ms | Free |
| Phase 2 (OPcache) | 1 hour | 300ms | Free |
| Phase 3 (Redis) | 1 day | 150ms | ~$5/mo |

## ✅ Success Metrics

### Current
- ❌ TTFB: 730ms (52% of LCP)
- ❌ Session: Database (slow)
- ❌ Cache: Database (slow)
- ❌ OPcache: Likely disabled

### Target (Phase 1-2)
- ✅ TTFB: ~300ms (20% of LCP)
- ✅ Session: File (fast)
- ✅ Cache: File (fast)
- ✅ OPcache: Enabled

### Ideal (Phase 3)
- 🎯 TTFB: ~150ms (10% of LCP)
- 🎯 Session: Redis (fastest)
- 🎯 Cache: Redis (fastest)
- 🎯 OPcache: Optimized

---

**Start with Phase 1 RIGHT NOW - it takes 5 minutes and will cut your TTFB by ~35%!**
