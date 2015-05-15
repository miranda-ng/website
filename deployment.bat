@echo off
REM if "%PHPBIN%" == "" set PHPBIN=D:\Programy\UniServerZ\core\php56\php.exe
REM "%PHPBIN%" vendor/dg/ftp-deployment/Deployment/deployment.php deployment.ini

REM PHP 5.6 doesn't have php_ssh2.dll library
"D:\Programy\UniServerZ\core\php54\php.exe" vendor/dg/ftp-deployment/Deployment/deployment.php deployment.ini