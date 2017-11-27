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

$uploadInfo = "";
$uploadOK = false;
if(isset($_POST["submit"]))
{
	if ($_FILES["fileToUpload"]["size"] > 1500000)
	{
		$uploadInfo = "Plik jest zbyt duży!";
	}
	else
	{
		$filePath = $_FILES["fileToUpload"]["name"];
		$imageFileType = pathinfo($filePath, PATHINFO_EXTENSION);

		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" )
		{
			$uploadInfo = "Plik musi mieć rozszerzenie JPG, JPEG, PNG lub GIF!";
		}
		else
			$uploadInfo = "okej";
	}
}

if ($uploadInfo == "okej")
{
	$fp = fopen($_FILES['fileToUpload']['tmp_name'], 'rb');
	$image = addslashes(file_get_contents($_FILES['fileToUpload']['tmp_name']));
	//echo $image;
	
	$check = $db->prepare("INSERT INTO przedmioty(nazwa, opis, koszt, koszt_opoznienia, obrazek) VALUES(:nazwa, :opis, :koszt, :kosztOp, :obrazek);");
	$check->bindParam(":nazwa", $_POST['nazwa']);
	$check->bindParam(":opis", $_POST['opis']);
	$check->bindParam(":koszt", $_POST['koszt']);
	$check->bindParam(":kosztOp", $_POST['koszt_opoznienia']);
	$check->bindParam(":obrazek", $fp, PDO::PARAM_LOB);
	$check->execute();

	$uploadInfo = "Pomyślnie dodano przedmiot!";
	$uploadOK = true;
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
			<span style="clear: both">WYPOŻYCZALNIA - DODAJ PRZEDMIOT</span>
			<hr>
			<br>
				<center>
					<form action="dodajPrzedmiot.php" method="post" enctype="multipart/form-data">
						<table border="1">
							<tr>
								<td align="left"> Nazwa </td>
								<td align="center"> <input type="text" name="nazwa" required/> </td>
							</tr>
							<tr>
								<td align="left"> Opis </td>
								<td align="center"> <input type="text" name="opis" required/> </td>
							</tr>
							<tr>
								<td align="left"> Koszt </td>
								<td align="center"> <input type="number" name="koszt" required/> </td>
							</tr>
							<tr>
								<td align="left"> Koszt opóznienia </td>
								<td align="center"> <input type="number" name="koszt_opoznienia" required/> </td>
							</tr>
							<tr>
								<td align="left"> Obrazek </td>
								<td align="center"> <input type="file" name="fileToUpload" id="fileToUpload" ></td>
							</tr>
						</table>
						<br>
						<input type="reset"></input>
						<input type="submit" name="submit"></input>
					</form>
				</center>
		</div>
		<div id="stopka">
			&copy; Stworzone przez Wojciech Żurek 30.10.2017
		</div>
	</body>
	<?php
		if ($uploadInfo != "")
			echo '<script> swal({title: "'.($uploadOK?'Dobra robota!':'Popraw się!').'", text: "'.$uploadInfo.'", icon: "'.($uploadOK?'success':'error').'"}); </script>';
	?>
</html>