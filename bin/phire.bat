@echo off
REM
REM Phire CMS 2.0 BASH CLI script
REM

SET SCRIPT_DIR=%~dp0
php %SCRIPT_DIR%phire.php %*
