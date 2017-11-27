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
				{
					echo '<p>PANEL LOGOWANIA</p><hr>';

					echo '<form action="zaloguj.php" method="POST">
								Login: <input name="login" type="text" required></input></br>
								Hasło: <input name="pass" type="password" required></input></br>';

					echo '<input type="submit" value="Zaloguj!"></input>
						  </form>';
				}
			?>
		</div>
		<div id="wypozyczalniaMain">
			WYPOŻYCZALNIA
			<hr>
			<?php
				if ($succ_login)
				{
					/*
						przedmioty, wypozycz, mozliwosc zabukowania
						zwrot w terminie, zwrot po terminie
						naliczanie oplat
					*/
					echo '<div class="doWypozyczenia">';
					echo '<table>';

					$selekcik = $db->prepare("SELECT * FROM przedmioty LIMIT 10;");
					$selekcik->execute();
					if ($selekcik->rowCount() > 0)
					{
						$result = $selekcik->fetchAll();
						for ($i = 0; $i < $selekcik->rowCount(); $i++)
						{
							$img = "brak.png";
							if (!empty($result[$i]['obrazek']))
								$img = 'data:image/jpeg;base64,'.base64_encode($result[$i]['obrazek']);

							echo '	<tr>
										<td>
											<img class="wypozyczImage" src="'.$img.'"></img>
										</td>
										<td class="wypozyczNazwa">
											'.$result[$i]['nazwa'].'
										</td>
										<td class="wypozyczOpis">
											'.$result[$i]['opis'].'
										</td>
										<td class="wypozyczDzien">
											'.number_format($result[$i]['koszt'], 2, ',', ' ').' zł.
										</td>
										<td class="wypozyczKara">
											'.number_format($result[$i]['koszt_opoznienia'], 2, ',', ' ').' zł.
										</td>
										<td class="wypozyczHref">
											<a href="wypozycz.php?itemID='.$result[$i]['id'].'">CHCĘ WYPOŻYCZYĆ</a>
										</td>
									</tr>';
						}
					}
					
					echo '</table>';
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
		if (isset($_SESSION['login_err']))
		{
			$tekst = "";
			$good = false;
			if ($_SESSION['login_err'] == 'no data')
				$tekst = 'Brak hasła lub loginu!';
			else if ($_SESSION['login_err'] == 'no connect')
				$tekst = 'Brak połączenia z bazą!';
			else if ($_SESSION['login_err'] == 'Wrong pass')
				$tekst = 'Złe hasło!';
			else if ($_SESSION['login_err'] == 'No login')
				$tekst = 'Nie znaleziono takiego konta!';
			else if ($_SESSION['login_err'] == 'Wylogowano')
			{
				$good = true;
				$tekst = 'Zostałeś pomyślnie wylogowany!';
			}
			else if ($_SESSION['login_err'] == 'okej')
			{
				$good = true;
				$tekst = 'Zalogowano pomyślnie!';
			}
			
			echo '<script> swal({title: "'.($good?'Pomyślnie!':'Nie pomyślnie!').'", text: "'.$tekst.'", icon: "'.($good?'success':'error').'"}); </script>';
		
			unset($_SESSION['login_err']);
		}
	?>
</html>