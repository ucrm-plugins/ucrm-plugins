@ECHO OFF
SETLOCAL ENABLEDELAYEDEXPANSION
CALL %~dp0.include.bat

:: Passes all arguments presented to the container's entrypoint.
docker run %DOCKER_ARGS% --entrypoint /bin/bash %PHP_IMAGE_ORG%/php:%PHP_IMAGE_TAG% %*
