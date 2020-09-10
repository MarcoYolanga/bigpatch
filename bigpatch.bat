@echo off
echo.
echo Bigpatch
rem don't modify this laucher, goto projects folder to do add your project
echo.
SET mypath=%~dp0
set INSTALL_DIR=%mypath:~0,-1%
echo %mypath:~0,-1%
%mypath:~0,2%
rem changed disk unit
cd %INSTALL_DIR%\projects
:asking_project
echo Your projects:
echo ----------------------------------------------
dir /b
echo ----------------------------------------------
set /P project=Name of the project (ending with .bat): 
if exist %INSTALL_DIR%\projects\%project% (
    goto :inside_project
) else (
    echo.
    echo ERROR: Project %INSTALL_DIR%\projects\%project% doesn't exists
    goto :asking_project
)
echo Project script %project% ended execution
rem you have to quit this with ctrl+c
goto :asking_project
:inside_project
call %INSTALL_DIR%\projects\%project%
set /p version="New version name (eg. 1.0.1): " 
set of=%output_patch_dir%\patch_%project_name%_%version%
@mkdir %of%
set of=%of%\files
@mkdir %of%
php %INSTALL_DIR%\run.php %input_project_dir% %of%
rem php [Your install path]\run.php [Input folder] [Output folder]
goto :asking_project
