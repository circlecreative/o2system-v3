<?php
$access[ 'login' ][ 'attempts' ] = 5;
$access[ 'login' ][ 'match_ip' ] = FALSE;
$access[ 'login' ][ 'match_agent' ] = TRUE;

$access[ 'password' ][ 'hash' ] = 'MD5';
$access[ 'password' ][ 'salt' ] = FALSE;
$access[ 'password' ][ 'rehash' ] = FALSE;

$access[ 'register' ][ 'from_email' ] = 'no-reply@yukbisnis.com';
$access[ 'register' ][ 'from_name' ] = 'Yukbisnis Indonesia';
$access[ 'register' ][ 'subject' ] = 'YukBisnis Registration';

$access[ 'sso' ][ 'access' ] = TRUE;
$access[ 'sso' ][ 'name' ] = 'sso';
$access[ 'sso' ][ 'lifetime' ] = 259200;
$access[ 'sso' ][ 'server' ] = 'yukbisnis.com';
