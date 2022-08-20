@ECHO OFF
SETLOCAL ENABLEDELAYEDEXPANSION
CALL %~dp0.include.bat

:: Passes all arguments presented to the container's entrypoint.
docker run %DOCKER_ARGS% --entrypoint /bin/bash %IMAGE_ORG%/php:%IMAGE_TAG% %*
