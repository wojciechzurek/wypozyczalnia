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
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"></meta>
		<title>Wypożyczalnia</title>
		<link rel="stylesheet" type="text/css" href="style.css">
		<link rel="stylesheet" href="jq/jquery-ui.css">
		<script src="jq/jquery-1.12.4.js"></script>
		<script src="jq/jquery-ui.js"></script>
		<script> $( function() { $( ".datepicker" ).datepicker(); } ); </script>
		<script src="js/sweetalert.min.js"></script>
	</head>
	<body>
		<div id="gora">
			<a href="index.php"><img vspace="2px" src="images/logo.png"></img></a>
		</div>
		<div id="panelUzytkownika">
			<?php
				$succ_login = false;
				if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'])
				{
					$check = $db->prepare("SELECT login FROM account WHERE id = :id LIMIT 1;");
					$check->bindParam(":id", $_SESSION['login_id']);
					$check->execute();
					if ($check->rowCount() > 0)
					{
						$check = $check->fetch(PDO::FETCH_ASSOC);
						$login = $check['login'];
							
						echo '<p>Zalogowany jako: '.$login.' <a href="wyloguj.php">(Wyloguj)</a></p>';
						echo '<hr>';
						$succ_login = true;
						
						echo '<a href="dodajPrzedmiot.php"><button>Dodaj przedmiot</button></a></br></br></br>';
						echo '<a href="index.php"><button>Wypożyczalnia</button></a></br>';
						echo '<a href="mojeWypozyczenia.php"><button>Moje wypożyczenia i rezerwacje</button></a></br>';
					}
				}
				if (!$succ_login)
					header('Location: index.php');
			?>
		</div>
		<div id="wypozyczalniaMain">
			<a href="index.php"><button style="float: left"><<</button></a>
			<span style="clear: both">WYPOŻYCZALNIA</span>
			<hr>
			<?php
				if ($succ_login)
				{
					/*
						przedmioty, wypozycz, mozliwosc zabukowania
						zwrot w terminie, zwrot po terminie
						naliczanie oplat
					*/
					$selekcik = $db->prepare("SELECT * FROM przedmioty WHERE id = :id LIMIT 1;");
					$selekcik->bindParam(':id', $_GET['itemID']);
					$selekcik->execute();
					if ($selekcik->rowCount() > 0)
					{
						$result = $selekcik->fetch(PDO::FETCH_ASSOC);
						
						$img = "brak.png";
						if (!empty($result['obrazek']))
							$img = 'data:image/jpeg;base64,'.base64_encode($result['obrazek']);

						echo '<div class="doWypozyczenia" style="text-align: center">';
						echo '<img class="wypozyczImageBigger" src="'.$img.'"></img></br></br>';
						echo '<p id="itemName">Nazwa przedmiotu: '.$result['nazwa'].'</p>';
						echo '<p id="itemDesc">Opis przedmiotu: '.$result['opis'].'</p></br>';
						echo '<p id="itemCost">Koszt: '.number_format($result['koszt'], 2, ',', ' ').' zł.</p>';
						echo '<p id="itemCostPunish">Koszt opóźnienia: '.number_format($result['koszt_opoznienia'], 2, ',', ' ').' zł.</p></br>';
						echo "<form method='POST' action='wypozyczAkcja.php'>";
						echo '<input name="przedmiotID" type="text" value="'.$_GET['itemID'].'" hidden>';
						echo 'Od kiedy: <input name="odkiedy" type="text" class="datepicker"></br>';
						echo 'Do kiedy: <input name="dokiedy" type="text" class="datepicker"></br></br>';
						
						echo "<input name='wypozycz' class='button' type='submit' value='Wypożycz'></input>";
						echo "<input name='rezerwuj' class='button' type='submit' value='Zarezerwuj'></input>";
						echo '</form></br></br>';
						
						$selekcik = $db->prepare("SELECT * FROM wypozyczenia WHERE itemID = :id ORDER BY date_from;");
						$selekcik->bindParam(':id', $_GET['itemID']);
						$selekcik->execute();
						if ($selekcik->rowCount() > 0)
						{
							echo 'AKTYWNE WYPOZYCZENIA I REZERWACJE:</br><div style="overflow-y:auto; max-height: 50px;">';
							
							while ($result = $selekcik->fetch(PDO::FETCH_ASSOC))
							{
								echo ($result['type'] == 'WYPOZYCZONY'?'Wypożyczony':'Zarezerwowany').' od '.$result['date_from'].' do '.$result['date_to'].'</br>';
							}
							
							echo '</div>';
						}
					}
					else
						echo '<p>Brak takiego przedmiotu!</p>';

					echo '</div>';
				}
				else
					echo '</br>Zaloguj się!</br>';
			?>
		</div>
		<div id="stopka">
			&copy; Stworzone przez Wojciech Żurek 30.10.2017
		</div>
	</body>
	<?php
		if (isset($_SESSION['wypozyczStatus']))
		{
			echo '<script> swal({title: "'.($_SESSION['wypozyczStatus']?'Pomyślnie!':'Nie pomyślnie!').'", text: "'.$_SESSION['wypozyczErr'].'", icon: "'.($_SESSION['wypozyczStatus']?'success':'error').'"}); </script>';
		
			unset($_SESSION['wypozyczErr']);
			unset($_SESSION['wypozyczStatus']);
		}
	?>
</html>