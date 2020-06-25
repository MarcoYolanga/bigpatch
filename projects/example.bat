set INPUT_PROJECT_DIR=[YOUR PROJECT'S DIR]
rem the git's project folder
set OUTPUT_PATCH_DIR=[YOUR OUTPUT FOLDER]
rem where do you want the patch files to go?
rem ^^^^^ USE FULL PATHS e.g: C:\Windows\Users\your_name\project ^^^^^

set /P version=New version of this project: 
set of=%OUTPUT_PATCH_DIR%\patch_%project%_%version%
@mkdir %of%
set of=%of%\files
@mkdir %of%
php %INSTALL_DIR%\run.php %INPUT_PROJECT_DIR% %of%
rem php [Your install path]\run.php [Input folder] [Output folder]
