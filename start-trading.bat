@echo off
echo ========================================
echo    TRADING GAME - QUICK START
echo ========================================
echo.

echo [1/3] Starting Laravel Server...
start "Laravel Server" cmd /k "php artisan serve"

timeout /t 2 /nobreak >nul

echo [2/3] Starting Laravel Scheduler...
start "Laravel Scheduler" cmd /k "php artisan schedule:work"

timeout /t 2 /nobreak >nul

echo [3/3] Opening browser...
timeout /t 3 /nobreak >nul
start http://localhost:8000/games/trading

echo.
echo ========================================
echo    Trading Game is running!
echo ========================================
echo.
echo Laravel Server: http://localhost:8000
echo Trading Game: http://localhost:8000/games/trading
echo.
echo Press any key to exit...
pause >nul
