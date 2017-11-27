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
		<style>
			.swal-text
			{
				text-align: center;
			}
			.swal-footer
			{
				text-align: center;
			}
		</style>
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

						if (!$_SESSION['is_admin'])
						{
							$_SESSION['login_err'] = 'Nie admin';
							header('Location: index.php');
							return;
						}

						echo '<a href="dodajPrzedmiot.php"><button>Dodaj przedmiot</button></a></br>';
						echo '<a href="zatwierdzOddanie.php"><button>Potwierdź oddanie</button></a></br></br></br>';

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

					if (isset($_SESSION['login_err']))
					{
						if ($_SESSION['login_err'] == 'no data')
							echo 'Brak hasła lub loginu!';
						else if ($_SESSION['login_err'] == 'no connect')
							echo 'Brak połączenia z bazą!';
						else if ($_SESSION['login_err'] == 'Wrong pass')
							echo 'Złe hasło!';
						else if ($_SESSION['login_err'] == 'No login')
							echo 'Nie znaleziono takiego konta!';
						
						echo '<br>';
					}

					echo '<input type="submit" value="Zaloguj!"></input>
						  </form>';
				}
			?>
		</div>
		<div id="wypozyczalniaMain">
			<a href="index.php"><button style="float: left"><<</button></a>
			ZATWIERDŹ ODDANIE PRZEDMIOTÓW
			<hr>
			<?php
				if ($succ_login)
				{
					echo '<div class="doWypozyczenia">';
					echo '<center>WYPOŻYCZENIA:<table border="1" style="text-align: center">';
					echo '	<tr>
								<td class="wypozyczNazwa">
									Nazwa użytkownika
								</td>
								<td>
									Zdjęcie
								</td>
								<td class="wypozyczNazwa">
									Nazwa przedmiotu
								</td>
								<td class="wypozyczDzien">
									Data początkowa
								</td>
								<td class="wypozyczDzien">
									Data końcowa
								</td>
								<td class="wypozyczDzien">
									Data oddania
								</td>
								<td class="wypozyczDzien">
									Akcja
								</td>
							</tr>';

					$selekcik = $db->prepare("SELECT a.login, w.id AS idx, p.obrazek, p.nazwa, w.type, w.date_from, w.date_to, w.oddano, w.data_oddania FROM ((wypozyczenia AS w INNER JOIN przedmioty AS p ON w.itemID = p.id) INNER JOIN account AS a ON a.ID = w.userID) WHERE w.oddano = '1' ORDER BY a.login, w.date_from;");
					$selekcik->bindParam(':userID', $_SESSION['login_id']);
					$selekcik->execute();
					if ($selekcik->rowCount() > 0)
					{
						$result = $selekcik->fetchAll();
						for ($i = 0; $i < $selekcik->rowCount(); $i++)
						{
							if ($result[$i]['type'] != "WYPOZYCZONY")
								continue;
							
							$img = "brak.png";
							if (!empty($result[$i]['obrazek']))
								$img = 'data:image/jpeg;base64,'.base64_encode($result[$i]['obrazek']);

							$now = strtotime($result[$i]['data_oddania']);
							$date_to = strtotime($result[$i]['date_to']);
							$datediff = $date_to - $now;
							$rest_day = floor($datediff / (60 * 60 * 24));

							echo '	<tr>
										<td class="wypozyczNazwa">
											'.$result[$i]['login'].'
										</td>
										<td>
											<img class="wypozyczImage" src="'.$img.'"></img>
										</td>
										<td class="wypozyczNazwa">
											'.$result[$i]['nazwa'].'
										</td>
										<td class="wypozyczDzien">
											'.$result[$i]['date_from'].'
										</td>
										<td class="wypozyczDzien">
											'.$result[$i]['date_to'].'
										</td>
										<td class="wypozyczDzien">
											'.$result[$i]['data_oddania'].'
										</td>
										<td class="wypozyczDzien">
										<p '.($rest_day<0?'style="color:#E90000"':'').'>'.($rest_day<0?'Dni opóźnienia':'Pozostało dni').': '.abs($rest_day).'</p></br>
											<form action="oddajPrzedmiotAdmin.php" method="post" enctype="multipart/form-data">
											<input type="text" name="itemID" value="'.$result[$i]['idx'].'" hidden></input>
											<input type="submit" value="Potwierdź oddanie"></input>
										</form>
										</td>
									</tr>';
						}
					}
					
					echo '</table></br></br>REZERWACJE:';
					
					echo '<table border="1" style="text-align: center">';
					echo '	<tr>
								<td class="wypozyczNazwa">
									Nazwa użytkownika
								</td>
								<td>
									Zdjęcie
								</td>
								<td class="wypozyczNazwa">
									Nazwa przedmiotu
								</td>
								<td class="wypozyczDzien">
									Data początkowa
								</td>
								<td class="wypozyczDzien">
									Data końcowa
								</td>
								<td class="wypozyczDzien">
									Akcja
								</td>
							</tr>';

					if ($selekcik->rowCount() > 0)
					{
						for ($i = 0; $i < $selekcik->rowCount(); $i++)
						{
							if ($result[$i]['type'] != "ZAREZERWOWANY")
								continue;

							$img = "brak.png";
							if (!empty($result[$i]['obrazek']))
								$img = 'data:image/jpeg;base64,'.base64_encode($result[$i]['obrazek']);

							echo '	<tr>
										<td class="wypozyczNazwa">
											'.$result[$i]['user'].'
										</td>
										<td>
											<img class="wypozyczImage" src="'.$img.'"></img>
										</td>
										<td class="wypozyczNazwa">
											'.$result[$i]['nazwa'].'
										</td>
										<td class="wypozyczDzien">
											'.$result[$i]['date_from'].'
										</td>
										<td class="wypozyczDzien">
											'.$result[$i]['date_to'].'
										</td>
										<td class="wypozyczDzien">
											<form action="anulujRezerwacje.php" method="post" enctype="multipart/form-data">
												<input type="text" name="itemID" value="'.$result[$i]['idx'].'" hidden></input>
												<input type="submit" value="Anuluj rezerwacje"></input>
											</form>
										</td>
									</tr>';
						}
					}
					
					echo '</table></br></br>';
					
					echo '</center>';
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
			echo '<script> swal({title: "'.($_SESSION['wypozyczStatus']?'Pomyślnie!':'Nie pomyślnie!').'", text: "'.$_SESSION['wypozyczErr'].'", icon: "'.($_SESSION['wypozyczStatus']?'success':'error').'"}) '.($selekcik->rowCount() <= 0?'.then(() => {window.location.href = "index.php";})':';').'; </script>';
		
			unset($_SESSION['wypozyczErr']);
			unset($_SESSION['wypozyczStatus']);
		}
		else if ($selekcik->rowCount() <= 0)
			echo '<script> swal({title: "Brak", text: "Brak przedmiotów do zatwierdzenia!", icon: "info"}) .then(() => {window.location.href = "index.php";})</script>';
	?>
</html>