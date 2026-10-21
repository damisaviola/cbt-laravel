$env:PHPRC = $PSScriptRoot
$env:PATH = "D:\App\laragon\bin\php\php-8.5.5-Win32-vs17-x64;" + $env:PATH
& "D:\App\laragon\bin\php\php-8.5.5-Win32-vs17-x64\php.exe" "$PSScriptRoot\artisan" @args
