@echo off
echo Testing cache headers with custom router...
echo.
echo Starting PHP server with cache router...
start /B php -S localhost:8001 -t public router.php

timeout /t 3 /nobreak >nul

echo.
echo Testing image cache headers:
echo =============================
curl -I http://localhost:8001/images/lspu_logo_better.webp 2>nul | findstr /i "cache expires"

echo.
echo Testing CSS cache headers:
echo ==========================
curl -I http://localhost:8001/build/assets/app.BzZpR12L.css 2>nul | findstr /i "cache expires"

echo.
echo Testing JS cache headers:
echo =========================
curl -I http://localhost:8001/build/assets/app.Cbkz82ah.js 2>nul | findstr /i "cache expires"

echo.
echo.
echo ✅ Check the output above for "Cache-Control" and "Expires" headers
echo.
echo Press any key to stop the test server...
pause >nul

taskkill /F /FI "WINDOWTITLE eq php -S*" 2>nul
