@ECHO OFF
SETLOCAL ENABLEDELAYEDEXPANSION
CALL %~dp0.include.bat

:: Passes all arguments presented to the container's PHP command.
docker run %DOCKER_ARGS% %IMAGE_ORG%/php:%IMAGE_TAG% %PHP_HANDLER% robo --ansi %*
