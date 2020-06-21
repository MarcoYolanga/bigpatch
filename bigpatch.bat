@echo off
echo.
set /P versione=Nuova versione di Yomenu:
set of=Z:\tmp\patch_yomenu_%versione%
@mkdir %of%
set of=%of%\files
@mkdir %of%
php Z:\bin\bigpatch\run.php Z:\virtual\www\very_secret %of%
rem php [Your install path]\run.php [Input folder] [Output folder]
tree /F "%of%"
