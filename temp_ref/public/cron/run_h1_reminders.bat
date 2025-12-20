@echo off
REM H-1 Reminder Task Scheduler Batch File
REM This file runs the H-1 reminder script using Laragon's PHP

REM Set paths
SET PHP_PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe
SET SCRIPT_PATH=C:\laragon\www\FinalProject\public\cron\send_h1_reminders.php
SET LOG_PATH=C:\laragon\www\FinalProject\public\cron\logs\h1_reminders.log

REM Create log directory if it doesn't exist
if not exist "C:\laragon\www\FinalProject\public\cron\logs" mkdir "C:\laragon\www\FinalProject\public\cron\logs"

REM Run the script and append output to log file
echo ========================================== >> "%LOG_PATH%"
echo Running H-1 Reminder Script >> "%LOG_PATH%"
echo Started at: %date% %time% >> "%LOG_PATH%"
echo ========================================== >> "%LOG_PATH%"

"%PHP_PATH%" "%SCRIPT_PATH%" >> "%LOG_PATH%" 2>&1

echo. >> "%LOG_PATH%"
echo Completed at: %date% %time% >> "%LOG_PATH%"
echo. >> "%LOG_PATH%"
