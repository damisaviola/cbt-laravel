@echo off
title SmartCBT School Edition Server
echo ========================================================
echo    SmartCBT School Edition - Server Lokal Sekolah
echo    Tech Stack: Laravel 13, Vue 3, Inertia, IndexedDB
echo ========================================================
echo.
set PHPRC=%~dp0
set PATH=D:\App\laragon\bin\php\php-8.5.5-Win32-vs17-x64;%PATH%
echo [1/2] Memastikan database siap...
call "%~dp0artisan.bat" migrate --force
echo.
echo [2/2] Memulai server CBT di http://127.0.0.1:8000 ...
echo Tekan Ctrl+C untuk menghentikan server.
echo.
"D:\App\laragon\bin\php\php-8.5.5-Win32-vs17-x64\php.exe" "%~dp0artisan" serve --port=8000
