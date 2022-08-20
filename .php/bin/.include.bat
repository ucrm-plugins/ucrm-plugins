@ECHO OFF

pushd %~dp0\..\..
SET PROJECT_DIR=%CD%
popd

SET CURRENT_DIR=%CD%
SET WORKING_DIR=/opt/project

::SET PHP_HANDLER=/opt/project/.php/proxy.php
SET PHP_HANDLER=/usr/local/bin/php-proxy

:: Parse and set variables from the .env file
FOR /F "tokens=*" %%i IN (%PROJECT_DIR%\.php\environment.ini) DO SET %%i

:: Check for an existing Docker Image
SET id=
FOR /F "tokens=*" %%i IN ('docker images -q %IMAGE_ORG%/php:%IMAGE_TAG%') DO SET id=%%i

:: IF the appropriate Docker Image does NOT exist...
IF "%id%" == "" (
    :: ...THEN build and tag it!
    docker build -t %IMAGE_ORG%/php:%IMAGE_TAG% --build-arg "PHP_VERSION=%IMAGE_TAG%" %PROJECT_DIR%\.php
)

SET DOCKER_ARGS=
for %%s in (
    --interactive
    --tty
    --rm
    --env "WIN_PROJECT_DIR=%PROJECT_DIR%"
    --env "WIN_CURRENT_DIR=%CURRENT_DIR%"
    --env "PHP_WORKING_DIR=%WORKING_DIR%"
    --volume "%PROJECT_DIR%:%WORKING_DIR%"
    --workdir "%WORKING_DIR%"
    --name "PHP-%IMAGE_TAG%"
) do (
    SET DOCKER_ARGS=!DOCKER_ARGS! %%s
)
