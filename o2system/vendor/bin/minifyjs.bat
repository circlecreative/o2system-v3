@ECHO OFF
SET BIN_TARGET=%~dp0/../matthiasmullie/minify/bin/minifyjs
php "%BIN_TARGET%" %*
