@echo off
cd /d E:\Project\Programmer\Doing\my-website-filament3
start cmd /k "php artisan serve"
timeout /t 3 >nul
start http://127.0.0.1:8000