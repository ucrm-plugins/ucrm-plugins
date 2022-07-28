@ECHO OFF
SETLOCAL DISABLEDELAYEDEXPANSION
SET ROOT_DIR=%~dp0/..
SET KEY_FILE=%ROOT_DIR%/.vagrant/machines/default/virtualbox/private_key

ssh -i "%KEY_FILE%" -p 2222 vagrant@127.0.0.1 %*
