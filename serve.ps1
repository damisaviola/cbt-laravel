$env:PHPRC = $PSScriptRoot
$env:PATH = "D:\App\laragon\bin\php\php-8.5.5-Win32-vs17-x64;" + $env:PATH
Write-Host "Starting SmartCBT on http://127.0.0.1:8000 using PHP 8.5.5..." -ForegroundColor Cyan
& "D:\App\laragon\bin\php\php-8.5.5-Win32-vs17-x64\php.exe" "$PSScriptRoot\artisan" serve --port=8000
