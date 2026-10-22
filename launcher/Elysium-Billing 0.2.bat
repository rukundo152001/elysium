@echo off
title Laravel Billing System Launcher V 0.2


REM ===============================
REM CONFIGURATION (EDIT THESE)
REM ===============================
set PROJECT_PATH=C:\myProjects\finalelysium


set PHP_PATH=C:\php-8.5.1\php.exe

set NPM_PATH=C:\Program Files\nodejs\npm.cmd
set URL=http://127.0.0.1:8000

REM ===============================
REM MOVE TO PROJECT
REM ===============================
cd /d %PROJECT_PATH%

echo ===============================
echo STARTING BILLING SYSTEM...
echo ===============================


REM ===============================
REM BACKUP AND ZIP LARAVEL LOG
REM ===============================

REM === Create Windows-safe timestamp ===
for /f %%a in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd_HH-mm-ss"') do set datetime=%%a

REM === If laravel.log exists ===
if exist "storage\logs\laravel.log" (

    echo Backing up laravel.log...

    REM Rename current log
    move "storage\logs\laravel.log" "storage\logs\laravel_%datetime%.log"

    REM Zip the backup
    powershell -NoProfile -Command "Compress-Archive -Path 'storage/logs/laravel_%datetime%.log' -DestinationPath 'storage/logs/laravel_%datetime%.zip'"

    REM Delete uncompressed log
    del /Q "storage\logs\laravel_%datetime%.log"
)

REM === Create fresh log file ===
type nul > "storage\logs\laravel.log"

REM === Keep only latest 5 backups ===
for /f "skip=5 delims=" %%f in ('dir /b /o-d "storage\logs\laravel_*.zip"') do (
    del "storage\logs\%%f"
)


REM ===============================
REM LARAVEL CACHE CLEARING COMMANDS
REM ===============================

%PHP_PATH% artisan optimize:clear     
%PHP_PATH% artisan config:cache       
%PHP_PATH% artisan route:clear        
%PHP_PATH% artisan view:clear         
%PHP_PATH% artisan cache:clear        
%PHP_PATH% artisan event:clear        
%PHP_PATH% artisan clear-compiled     
%PHP_PATH% artisan config:cache       
%PHP_PATH% artisan route:cache        
%PHP_PATH% artisan view:cache         

REM ===============================
REM START NPM DEV (BACKGROUND)
REM ===============================
start "NPM DEV" cmd /k call "%NPM_PATH%" run dev

REM ===============================
REM START ELYSIUM SERVER (BACKGROUND)
REM ===============================
start "ELYSIUM BILLING SERVER" cmd /k "%PHP_PATH%" artisan serve


REM ===============================
REM WAIT FOR SERVER
REM ===============================
timeout /t 5 /nobreak >nul

REM ===============================
REM OPEN SYSTEM IN BROWSER
REM ===============================
start %URL%

echo ===============================
echo BILLING SYSTEM IS RUNNING
echo DO NOT CLOSE THIS WINDOW
echo ===============================

REM ===============================
REM KEEP CMD ALIVE
REM ===============================
pause
