@ECHO OFF
SETLOCAL DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/upm
SET COMPOSER_RUNTIME_BIN_DIR=%~dp0/../vendor/bin
php "%BIN_TARGET%" --ansi %*
