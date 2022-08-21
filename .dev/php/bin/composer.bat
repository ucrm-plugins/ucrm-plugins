@ECHO OFF
SETLOCAL ENABLEDELAYEDEXPANSION
CALL %~dp0.include.bat

:: Passes all arguments presented to the container's PHP command.
docker run %DOCKER_ARGS% %PHP_IMAGE_ORG%/php:%PHP_IMAGE_TAG% %PHP_HANDLER% composer --ansi %*
