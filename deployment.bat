@echo off
if "%PHPBIN%" == "" set PHPBIN=D:\Programy\Wamp\bin\php\php5.4.16\php.exe
"%PHPBIN%" vendor/dg/ftp-deployment/Deployment/deployment.php deployment.ini