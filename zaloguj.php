<?php
session_start();

$_SESSION['zalogowany'] = false;
if ((!isset($_POST['login'])) || (!isset($_POST['pass'])))
{
	$_SESSION['login_err'] = 'no data';
	header('Location: index.php');
}

try
{
    $db = new PDO('mysql:host=localhost;dbname=test', 'root', '');
}
catch (PDOException $e)
{
    print "Błąd połączenia z bazą!: " . $e->getMessage() . "<br/>";

	$_SESSION['login_err'] = 'no connect';
	header('Location: index.php');
}

$check = $db->prepare("SELECT id, password FROM account WHERE login = :login LIMIT 1;");
$check->bindParam(":login", $_POST['login']);
$check->execute();
if ($check->rowCount() > 0)
{
	$check = $check->fetch(PDO::FETCH_ASSOC);
	$db_pass = $check['password'];
	if ($db_pass == $_POST['pass'])
	{
		$_SESSION['login_id'] = $check['id'];
		$_SESSION['zalogowany'] = true;
		$_SESSION['login_err'] = 'okej';
		header('Location: index.php');
	}
	else
	{
		$_SESSION['login_err'] = 'Wrong pass';
		header('Location: index.php');
	}
}
else
{
	$_SESSION['login_err'] = 'No login';
	header('Location: index.php');
}
?>