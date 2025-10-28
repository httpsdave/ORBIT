@echo off
REM Cache Headers Verification Script
REM Tests if cache headers are properly configured

echo ========================================
echo Cache Headers Verification Script
echo ========================================
echo.

REM Check if curl is available
where curl >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: curl is not installed
    echo.
    echo Please install curl or use Git Bash
    echo Download from: https://curl.se/windows/
    pause
    exit /b 1
)

echo Testing cache headers for production assets...
echo.

REM Production URL
set BASE_URL=https://orbit-production.up.railway.app

echo ========================================
echo Testing Build Assets (JavaScript)
echo ========================================
curl -I %BASE_URL%/build/assets/app.B8wPsEKO.js 2>nul | findstr /i "cache-control expires"
echo.

echo ========================================
echo Testing Build Assets (CSS)
echo ========================================
curl -I %BASE_URL%/build/assets/app.mSa5hnaP.css 2>nul | findstr /i "cache-control expires"
echo.

echo ========================================
echo Testing Images
echo ========================================
curl -I %BASE_URL%/images/lspu_logo_better.png 2>nul | findstr /i "cache-control expires"
echo.

echo ========================================
echo Testing Component Assets
echo ========================================
curl -I %BASE_URL%/build/assets/Logz.Che7SY_w.js 2>nul | findstr /i "cache-control expires"
echo.

echo ========================================
echo Expected Headers:
echo ========================================
echo Cache-Control: public, max-age=31536000, immutable
echo Expires: [date 1 year from now]
echo.
echo If you see these headers, caching is working!
echo If not, nginx configuration may not be applied.
echo.
pause
