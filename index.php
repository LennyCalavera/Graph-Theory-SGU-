<?php

if (!isset($_COOKIE['uid'])) {
	$id = uniqid('uid_');
	setcookie('uid', $id);
}

echo file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/html/main.html');