@ECHO OFF

pushd %~dp0\..\..\..
SET PROJECT_DIR=%CD%
popd

SET CURRENT_DIR=%CD%
SET WORKING_DIR=/usr/src/app

SET NODE_HANDLER=/usr/local/bin/node-proxy

:: Parse and set variables from the .env file
FOR /F "tokens=*" %%i IN (%PROJECT_DIR%\.dev\environment.ini) DO SET %%i

:: Check for an existing Docker Image
SET id=
FOR /F "tokens=*" %%i IN ('docker images -q %NODE_IMAGE_ORG%/node:%NODE_IMAGE_TAG%') DO SET id=%%i

:: IF the appropriate Docker Image does NOT exist...
IF "%id%" == "" (
    :: ...THEN build and tag it!
    docker build -t %NODE_IMAGE_ORG%/node:%NODE_IMAGE_TAG% --build-arg "NODE_VERSION=%NODE_IMAGE_TAG%" %PROJECT_DIR%\.dev\node
)

SET DOCKER_ARGS=
for %%s in (
    --interactive
    --tty
    --rm
    --env "WIN_PROJECT_DIR=%PROJECT_DIR%"
    --env "WIN_CURRENT_DIR=%CURRENT_DIR%"
    --env "NODE_WORKING_DIR=%WORKING_DIR%"
    --volume "%PROJECT_DIR%:%WORKING_DIR%"
    --workdir "%WORKING_DIR%"
    --name "NODE-%NODE_IMAGE_TAG%"
) do (
    SET DOCKER_ARGS=!DOCKER_ARGS! %%s
)
