@echo off
REM ========================================
REM DataBuilderIMSCPPlugin - Setup Script
REM Copy source files to make plugin standalone
REM ========================================

echo.
echo ========================================
echo DataBuilderIMSCPPlugin - Setup
echo ========================================
echo.

set SOURCE_DIR=%~dp0..\..
set PLUGIN_DIR=%~dp0

echo Source: %SOURCE_DIR%
echo Plugin: %PLUGIN_DIR%
echo.

REM Check if source exists
if not exist "%SOURCE_DIR%\src" (
    echo ERROR: Source directory not found: %SOURCE_DIR%\src
    pause
    exit /b 1
)

REM Copy src directory
echo [1/5] Copying src/ ...
xcopy /E /I /Y "%SOURCE_DIR%\src" "%PLUGIN_DIR%\src"

REM Copy modules directory
echo [2/5] Copying modules/ ...
if exist "%SOURCE_DIR%\modules" (
    xcopy /E /I /Y "%SOURCE_DIR%\modules" "%PLUGIN_DIR%\modules"
) else (
    echo SKIP: modules/ not found
)

REM Copy tenants directory
echo [3/5] Copying tenants/ ...
if exist "%SOURCE_DIR%\tenants" (
    xcopy /E /I /Y "%SOURCE_DIR%\tenants" "%PLUGIN_DIR%\tenants"
) else (
    echo SKIP: tenants/ not found
)

REM Copy themes (except base which we created)
echo [4/5] Copying themes/ ...
if exist "%SOURCE_DIR%\themes" (
    xcopy /E /I /Y "%SOURCE_DIR%\themes\custom" "%PLUGIN_DIR%\themes\custom" 2>nul
    xcopy /E /I /Y "%SOURCE_DIR%\themes\base\template" "%PLUGIN_DIR%\themes\base\template" 2>nul
) else (
    echo SKIP: themes/ not found
)

REM Copy config (merge with existing)
echo [5/5] Copying config/ ...
if exist "%SOURCE_DIR%\config" (
    xcopy /E /I /Y "%SOURCE_DIR%\config\*.xml" "%PLUGIN_DIR%\config\"
) else (
    echo SKIP: config/ not found
)

echo.
echo ========================================
echo Setup complete!
echo.
echo Now you can zip the plugin folder:
echo   DataBuilderIMSCPPlugin.zip
echo.
echo And install via i-MSCP interface:
echo   Plugins ^> Plugin Management ^> Add
echo ========================================
pause
