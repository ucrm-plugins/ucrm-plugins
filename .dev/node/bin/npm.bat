@ECHO OFF
SETLOCAL ENABLEDELAYEDEXPANSION
CALL %~dp0.include.bat

:: Passes all arguments presented to the container's PHP command.
docker run %DOCKER_ARGS% %NODE_IMAGE_ORG%/node:%NODE_IMAGE_TAG% %NODE_HANDLER% npm %*
