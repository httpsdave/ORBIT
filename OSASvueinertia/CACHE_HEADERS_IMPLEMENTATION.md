# Cache Headers Implementation for Railway

## Problem
Google PageSpeed Insights reported "Use efficient cache lifetimes" with 1,537 KiB of potential savings. Static assets (images, CSS, JS) had no cache headers, forcing browsers to re-download them on every visit.

## Root Cause
Railway uses `php artisan serve` (PHP's built-in server), which serves static files directly without passing through Laravel middleware. Your existing `.htaccess` cache headers only work with Apache, not PHP's built-in server.

## Solution Implemented

### 1. Custom PHP Router (`router.php`)
Created a custom router that intercepts static file requests and adds proper cache headers:

**Cache Strategy:**
- **Build assets (JS/CSS)**: 1 year cache with `immutable` flag
- **Images (JPG, PNG, WebP, etc.)**: 1 year cache with `immutable` flag  
- **Fonts**: 1 year cache with `immutable` flag + CORS headers
- **Security**: Added `X-Content-Type-Options: nosniff` to all static assets

### 2. Updated Startup Script (`startup.sh`)
Changed the start command from:
```bash
php artisan serve --host=0.0.0.0 --port=${PORT}
```

To:
```bash
php -S 0.0.0.0:${PORT} -t public router.php
```

This uses PHP's built-in server with our custom router that adds cache headers.

## Files Modified

1. **`router.php`** (NEW) - Custom router with cache header logic
2. **`startup.sh`** - Updated to use the new router
3. **`nixpacks.toml`** - Kept simple (no Nginx needed)

## Alternative Files Created (Not Used)

- **`nginx.conf`** - Full Nginx configuration (if you want to switch to Nginx later)
- **`start-server.sh`** - Nginx + PHP-FPM startup script (alternative approach)

## How to Deploy

1. **Commit all changes:**
   ```bash
   git add router.php startup.sh nixpacks.toml
   git commit -m "Add cache headers for static assets"
   git push
   ```

2. **Railway will automatically redeploy** with the new configuration

3. **Verify cache headers work:**
   - Open browser DevTools → Network tab
   - Visit your site: https://orbit-production.up.railway.app
   - Check any image/JS/CSS file
   - Look for `Cache-Control: public, max-age=31536000, immutable`

## Expected Results

✅ **1,537 KiB savings** from browser caching  
✅ **Faster repeat visits** (1 year cache)  
✅ **Better PageSpeed Insights score**  
✅ **No code changes needed** - transparent to your app

## Testing Locally

Test the cache headers locally:

```bash
php -S localhost:8000 -t public router.php
```

Then check headers:
```bash
curl -I http://localhost:8000/images/lspu_logo_better.webp
```

Should see:
```
Cache-Control: public, max-age=31536000, immutable
Expires: Sun, 06 Nov 2026 XX:XX:XX GMT
X-Content-Type-Options: nosniff
```

## Why This Works

1. **PHP router intercepts requests** before serving static files
2. **Checks file extension** (jpg, png, js, css, etc.)
3. **Adds appropriate cache headers** based on file type
4. **Serves the file** with proper headers
5. **Browser caches for 1 year** on repeat visits

## Notes

- The `.htaccess` file still works for local Apache/XAMPP development
- The `AddCacheHeaders` middleware is still in place but won't run on Railway
- This solution is **Railway-specific** but doesn't break local development
- Cache is **immutable** - safe because Vite adds hashes to build assets
