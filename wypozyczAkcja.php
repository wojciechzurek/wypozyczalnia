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

if (!isset($_POST['odkiedy']) || !isset($_POST['dokiedy']))
{
	$_SESSION['wypozyczErr'] = 'Nie wypełniłeś wymaganych pól!';
	$_SESSION['wypozyczStatus'] = false;
	header('Location: wypozycz.php?itemID='.$_POST['przedmiotID']);
	return;
}

if (!$_POST['odkiedy'])
{
	$_SESSION['wypozyczErr'] = 'Nie wybrałeś daty początkowej!';
	$_SESSION['wypozyczStatus'] = false;
	header('Location: wypozycz.php?itemID='.$_POST['przedmiotID']);
	return;
}

if (!$_POST['dokiedy'])
{
	$_SESSION['wypozyczErr'] = 'Nie wybrałeś daty końcowej!';
	$_SESSION['wypozyczStatus'] = false;
	header('Location: wypozycz.php?itemID='.$_POST['przedmiotID']);
	return;
}

$date_from_my = date('Y-m-d', strtotime($_POST['odkiedy']));
$date_to_my = date('Y-m-d', strtotime($_POST['dokiedy']));

if ($date_from_my > $date_to_my)
{
	$_SESSION['wypozyczErr'] = 'Data końca wypożyczenia mniejsza niż początku!';
	$_SESSION['wypozyczStatus'] = false;
	header('Location: wypozycz.php?itemID='.$_POST['przedmiotID']);
	return;
}

$actual_date = date('Y-m-d');
if ($date_from_my < $actual_date || $date_to_my < $actual_date)
{
	$_SESSION['wypozyczErr'] = 'Nie możesz wypożyczać wstecz!';
	$_SESSION['wypozyczStatus'] = false;
	header('Location: wypozycz.php?itemID='.$_POST['przedmiotID']);
	return;
}

$type = "";
$tekscik = "";
if (isset($_POST['wypozycz']))
{
	$type = "WYPOZYCZONY";
	$tekscik = "wypożyczono";
}
else if (isset($_POST['rezerwuj']))
{
	$type = "ZAREZERWOWANY";
	$tekscik = "zarezerwowano";
}

if ($type == "")
{
	$_SESSION['wypozyczErr'] = 'Nie wypełniłeś wymaganych pól!';
	$_SESSION['wypozyczStatus'] = false;
	header('Location: wypozycz.php?itemID='.$_POST['przedmiotID']);
	return;
}

$selekcik = $db->prepare("SELECT * FROM wypozyczenia WHERE itemID = :id;");
$selekcik->bindParam(':id', $_POST['przedmiotID']);
$selekcik->execute();
if ($selekcik->rowCount() > 0)
{
	while ($result = $selekcik->fetch(PDO::FETCH_ASSOC))
	{
		$date_from = date('Y-m-d', strtotime($result['date_from']));
		$date_to = date('Y-m-d', strtotime($result['date_to']));
		$tekst = $result['type'] == "WYPOZYCZONY" ? "wypożyczony" : "zarezerwowany";
		
		if (($date_from_my >= $date_from && $date_from_my <= $date_to) || ($date_to_my >= $date_from && $date_to_my <= $date_to))
		{
			$_SESSION['wypozyczErr'] = 'Ten przedmiot jest już '.$tekst.' od '.$date_from.' do '.$date_to.'!';
			$_SESSION['wypozyczStatus'] = false;
			header('Location: wypozycz.php?itemID='.$_POST['przedmiotID']);
			return;
		}
	}
}

$check = $db->prepare("INSERT INTO wypozyczenia(itemID, userID, type, date_from, date_to) VALUES(:id, :userID, :type, :dateFrom, :dateTo);");
$check->bindParam(":id", $_POST['przedmiotID']);
$check->bindParam(":userID", $_SESSION['login_id']);
$check->bindParam(":type", $type);
$check->bindParam(":dateFrom", $date_from_my);
$check->bindParam(":dateTo", $date_to_my);
$check->execute();

$_SESSION['wypozyczErr'] = 'Pomyślnie '.$tekscik.'!';
$_SESSION['wypozyczStatus'] = true;
header('Location: wypozycz.php?itemID='.$_POST['przedmiotID']);
return;
?>