<?php
session_start();

if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'])
{
	$_SESSION['login_id'] = 0;
	$_SESSION['zalogowany'] = false;
	$_SESSION['login_err'] = 'Wylogowano';

	session_unset();
	session_destroy();

	session_start();
	$_SESSION['login_err'] = 'Wylogowano';

	header('Location: index.php');
}
?>