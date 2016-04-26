@ECHO OFF
SET BIN_TARGET=%~dp0/../matthiasmullie/minify/bin/minifycss
php "%BIN_TARGET%" %*
