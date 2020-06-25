@echo off
echo.
echo Bigpatch launcher
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
    call %INSTALL_DIR%\projects\%project%
) else (
    echo.
    echo ERROR: Project %INSTALL_DIR%\projects\%project% doesn't exists
    goto :asking_project
)
echo Project script %project% ended execution
goto :asking_project
rem you have to quit this with ctrl+c
