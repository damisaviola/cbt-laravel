@echo off
set PHPRC=%~dp0
set PATH=D:\App\laragon\bin\php\php-8.5.5-Win32-vs17-x64;%PATH%
"D:\App\laragon\bin\php\php-8.5.5-Win32-vs17-x64\php.exe" "%~dp0artisan" %*
