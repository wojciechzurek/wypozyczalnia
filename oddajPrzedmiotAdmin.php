<?php
session_start();

try
{
    $db = new PDO('mysql:host=localhost;dbname=test', 'root', '');
}
catch (PDOException $e)
{
    print "Błąd połączenia z bazą!: " . $e->getMessage() . "<br/>";
	die();
}

if (!isset($_POST['itemID']))
{
	$_SESSION['wypozyczErr'] = 'Nie wybrałeś przedmiotu!';
	$_SESSION['wypozyczStatus'] = false;
	header('Location: zatwierdzOddanie.php');
	return;
}

if (!$_POST['itemID'])
{
	$_SESSION['wypozyczErr'] = 'Brak przedmiotu!';
	$_SESSION['wypozyczStatus'] = false;
	header('Location: zatwierdzOddanie.php');
	return;
}

$selekcik = $db->prepare("SELECT * FROM wypozyczenia WHERE id = :id;");
$selekcik->bindParam(':id', $_POST['itemID']);
$selekcik->execute();
if ($selekcik->rowCount() > 0)
{
	$result = $selekcik->fetch(PDO::FETCH_ASSOC);
	$date = date('Y-m-d');

	$zmiana = $db->prepare("UPDATE wypozyczenia SET oddano = '2' WHERE id = :id;");
	$zmiana->bindParam(':id', $_POST['itemID']);
	$zmiana->execute();

	$_SESSION['wypozyczErr'] = 'Pomyślnie zatwierdziłeś oddanie przedmiotu!';
	$_SESSION['wypozyczStatus'] = true;

	header('Location: zatwierdzOddanie.php');
	return;
}
else
{
	$_SESSION['wypozyczErr'] = 'Nie znaleziono przedmiotu!';
	$_SESSION['wypozyczStatus'] = false;
	header('Location: zatwierdzOddanie.php');
	return;
}
?>