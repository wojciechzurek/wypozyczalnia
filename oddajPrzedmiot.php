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
	header('Location: mojeWypozyczenia.php');
	return;
}

if (!$_POST['itemID'])
{
	$_SESSION['wypozyczErr'] = 'Brak przedmiotu!';
	$_SESSION['wypozyczStatus'] = false;
	header('Location: mojeWypozyczenia.php');
	return;
}

$selekcik = $db->prepare("SELECT * FROM wypozyczenia WHERE id = :id;");
$selekcik->bindParam(':id', $_POST['itemID']);
$selekcik->execute();
if ($selekcik->rowCount() > 0)
{
	$result = $selekcik->fetch(PDO::FETCH_ASSOC);
	if ($result['userID'] != $_SESSION['login_id'])
	{
		$_SESSION['wypozyczErr'] = 'Nie masz wypożyczonego tego przedmiotu!';
		$_SESSION['wypozyczStatus'] = false;
		header('Location: mojeWypozyczenia.php');
		return;
	}
	$date = date('Y-m-d');

	//$usuwanie = $db->prepare("DELETE FROM wypozyczenia WHERE id = :id;");
	//$usuwanie->bindParam(':id', $_POST['itemID']);
	//$usuwanie->execute();
	$zmiana = $db->prepare("UPDATE wypozyczenia SET oddano = '1', data_oddania = NOW() WHERE id = :id;");
	$zmiana->bindParam(':id', $_POST['itemID']);
	$zmiana->execute();
	
	if ($date >= $result['date_from'] && $date <= $result['date_to'])
	{
		$_SESSION['wypozyczErr'] = 'Pomyślnie oddałeś przedmiot w terminie!';
		$_SESSION['wypozyczStatus'] = true;
	}
	else
	{
		$_SESSION['wypozyczErr'] = 'Nie oddałeś przedmiotu w terminie! Będą naliczone dodatkowe opłaty!';
		$_SESSION['wypozyczStatus'] = false;
	}
	
	header('Location: mojeWypozyczenia.php');
	return;
}
else
{
	$_SESSION['wypozyczErr'] = 'Nie znaleziono przedmiotu!';
	$_SESSION['wypozyczStatus'] = false;
	header('Location: mojeWypozyczenia.php');
	return;
}
?>