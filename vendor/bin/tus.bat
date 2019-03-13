@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../ankitpokhrel/tus-php/bin/tus
php "%BIN_TARGET%" %*
