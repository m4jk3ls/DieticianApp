<?php
session_start();

if(!isset($_COOKIE['adminLogged']))
{
	header("Location: index.php");
	exit();
}

function showYears()
{
	$year = date("Y") - 18;
	for ($i = 0; $i < 100; $i++)
		echo '<option>' . $year-- . '</option>';
}

function showMonths()
{
	echo
	'<option>Styczeń</option>
	<option>Luty</option>
	<option>Marzec</option>
	<option>Kwiecień</option>
	<option>Maj</option>
	<option>Czerwiec</option>
	<option>Lipiec</option>
	<option>Sierpień</option>
	<option>Wrzesień</option>
	<option>Październik</option>
	<option>Listopad</option>
	<option>Grudzień</option>';
}

// Funkcja do generowania soli
function generateSalt()
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < 10; $i++)
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	return $randomString;
}

// Walidacja imienia
function firstName()
{
	$GLOBALS['firstName'] = $_POST['firstName'];
	if(strlen($GLOBALS['firstName']) < 1)
	{
		$GLOBALS['everythingOK'] = false;
		$_SESSION['firstNameError'] = "Nie podałeś imienia!";
	}
	// Zapisuje wartosc, aby przy niepoprawnej walidacji nie wpisywac jej od nowa
	$_SESSION['firstNameSaved'] = $GLOBALS['firstName'];
}

// Walidacja nazwiska
function lastName()
{
	$GLOBALS['lastName'] = $_POST['lastName'];
	if(strlen($GLOBALS['lastName']) < 1)
	{
		$GLOBALS['everythingOK'] = false;
		$_SESSION['lastNameError'] = "Nie podałeś nazwiska!";
	}
	// Zapisuje wartosc, aby przy niepoprawnej walidacji nie wpisywac jej od nowa
	$_SESSION['lastNameSaved'] = $GLOBALS['lastName'];
}

function dateOfTheBirth()
{
	if(!isset($_POST['year']) || $_POST['year'] == "---rok---" ||
		!isset($_POST['month']) || $_POST['month'] == "---miesiąc---" ||
		!isset($_POST['day']) || $_POST['day'] == "---dzień---"
	)
	{
		$GLOBALS['everythingOK'] = false;
		$_SESSION['dateError'] = "Podaj prawidłową datę urodzenia!";
	}
}

function CheckPESEL($str)
{
	if(!preg_match('/^[0-9]{11}$/', $str)) //sprawdzamy czy ciąg ma 11 cyfr
		return false;

	$arrSteps = array(1, 3, 7, 9, 1, 3, 7, 9, 1, 3); // tablica z odpowiednimi wagami
	$intSum = 0;

	for ($i = 0; $i < 10; $i++)
		$intSum += $arrSteps[$i] * $str[$i]; //mnożymy każdy ze znaków przez wagć i sumujemy wszystko

	$int = 10 - $intSum % 10; //obliczamy sumć kontrolną
	$intControlNr = ($int == 10) ? 0 : $int;

	if($intControlNr == $str[10]) //sprawdzamy czy taka sama suma kontrolna jest w ciągu
		return true;
	return false;
}

function pesel()
{
	$GLOBALS['pesel'] = $_POST['pesel'];
	if(!CheckPESEL($GLOBALS['pesel']))
	{
		$GLOBALS['everythingOK'] = false;
		$_SESSION['peselError'] = "Numer PESEL jest niepoprawny!";
	}
	// Zapisuje wartosc, aby przy niepoprawnej walidacji nie wpisywac jej od nowa
	$_SESSION['peselSaved'] = $GLOBALS['pesel'];
}

//Walidacja loginu
function login()
{
	$GLOBALS['login'] = $_POST['login'];
	if((strlen($GLOBALS['login']) < 3) || (strlen($GLOBALS['login']) > 20))
	{
		$GLOBALS['everythingOK'] = false;
		$_SESSION['loginError'] = "Login musi posiadać od 3 do 20 znaków!";
	}
	if(!ctype_alnum($GLOBALS['login']))
	{
		$GLOBALS['everythingOK'] = false;
		$_SESSION['loginError'] = "Login może składać się tylko z liter i cyfr (bez polskich znaków)";
	}
	// Zapisuje wartosc, aby przy niepoprawnej walidacji nie wpisywac jej od nowa
	$_SESSION['loginSaved'] = $GLOBALS['login'];
}

//Walidacja i sanityzacja email'a
function email()
{
	$GLOBALS['email'] = $_POST['email'];
	$emailB = filter_var($GLOBALS['email'], FILTER_SANITIZE_EMAIL);
	if(!filter_var($emailB, FILTER_VALIDATE_EMAIL) || ($emailB != $GLOBALS['email']))
	{
		$GLOBALS['everythingOK'] = false;
		$_SESSION['emailError'] = "Podaj poprawny adres email!";
	}
	// Zapisuje wartosc, aby przy niepoprawnej walidacji nie wpisywac jej od nowa
	$_SESSION['emailSaved'] = $GLOBALS['email'];
}

//Walidacja hasel
function passwds()
{
	$GLOBALS['passwd1'] = $_POST['passwd1'];
	$passwd2 = $_POST['passwd2'];
	if(strlen($GLOBALS['passwd1']) < 8 || strlen($GLOBALS['passwd1']) > 20)
	{
		$GLOBALS['everythingOK'] = false;
		$_SESSION['passwdError'] = "Hasło musi posiadać od 8 do 20 znaków!";
	}
	if($GLOBALS['passwd1'] != $passwd2)
	{
		$GLOBALS['everythingOK'] = false;
		$_SESSION['passwdError'] = "Podane hasła nie są identyczne!";
	}
	// Zapisuje wartosci, aby przy niepoprawnej walidacji nie wpisywac ich od nowa
	$_SESSION['passwd1Saved'] = $GLOBALS['passwd1'];
	$_SESSION['passwd2Saved'] = $passwd2;
}

// Sprawdzanie liczby tozsamych adresow email z podanym w formularzu
function howManyEmails($result)
{
	$howMany = $result->num_rows;
	if($howMany > 0)
	{
		$GLOBALS['everythingOK'] = false;
		$_SESSION['emailError'] = "Istnieje już użytkownik o takim adresie email!";
	}
}

// Sprawdzanie liczby tozsamych loginow z podanym w formularzu
function howManyLogins($result)
{
	$howMany = $result->num_rows;
	if($howMany > 0)
	{
		$GLOBALS['everythingOK'] = false;
		$_SESSION['loginError'] = "Istnieje już użytkownik o takim loginie! Wybierz inny.";
	}
}

function isTherePesel($result)
{
	$howMany = $result->num_rows;
	if($howMany > 0)
	{
		$GLOBALS['everythingOK'] = false;
		$_SESSION['peselError'] = "To nie jest Twój numer PESEL!";
	}
}

function convertMonthToNumber($month)
{
	switch ($month)
	{
		case "Styczeń":
			return "01";
		case "Luty":
			return "02";
		case "Marzec":
			return "03";
		case "Kwiecień":
			return "04";
		case "Maj":
			return "05";
		case "Czerwiec":
			return "06";
		case "Lipiec":
			return "07";
		case "Sierpień":
			return "08";
		case "Wrzesień":
			return "09";
		case "Październik":
			return "10";
		case "Listopad":
			return "11";
		case "Grudzień":
			return "12";
		default:
			return null;
	}
}

// Transakcja, ktora ostatecznie wprowadza dane nowego uzytkownika-pacjenta do bazy
function saveNewUser($passwdHash, $salt)
{
	global $firstName, $lastName, $login, $email, $pesel;
	$dateOfTheBirth = $_POST['year'] . '-' . convertMonthToNumber($_POST['month']) . '-' . $_POST['day'];
	$pathToImage = "img/" . $firstName . ' ' . $lastName . '.png';

	$GLOBALS['connection']->query("START TRANSACTION");
	if($GLOBALS['connection']->query("insert into user values (null, '$firstName', '$lastName', '$email', '$login', '$passwdHash', '$salt')") &&
		$GLOBALS['connection']->query("insert into dietician values (null, LAST_INSERT_ID(), '$pesel', '$dateOfTheBirth', '$pathToImage')")
	)
		return true;
	else
		return false;
}

// Glowna funkcja, obslugujaca polaczenie z baza danych
function dbConnection()
{
	global $login, $pesel, $email, $passwd1, $host, $db_user, $db_password, $db_name;

	if($GLOBALS['everythingOK'])
	{
		require_once "connect.php";
		mysqli_report(MYSQLI_REPORT_STRICT);
		try
		{
			// Proba polaczenia sie z baza
			$GLOBALS['connection'] = new mysqli($host, $db_user, $db_password, $db_name);
			$GLOBALS['connection']->set_charset('utf8');

			// Jesli powyzsza proba zawiedzie, to rzuc wyjatkiem
			if($GLOBALS['connection']->connect_errno != 0)
				throw new Exception($GLOBALS['connection']->connect_error);
			else
			{
				// Poszukaj, czy w bazie istnieje juz podany adres email
				$result = $GLOBALS['connection']->query("select email from user where email = '$email'");
				if(!$result) throw new Exception($GLOBALS['connection']->error);
				howManyEmails($result);

				// Poszukaj, czy w bazie istnieje juz podany login
				$result = $GLOBALS['connection']->query("select userID from user where login = '$login'");
				if(!$result) throw new Exception($GLOBALS['connection']->error);
				howManyLogins($result);

				// Poszukaj, czy w bazie istnieje juz podany numer PESEL
				$result = $GLOBALS['connection']->query("select personalIdentityNumber from dietician where personalIdentityNumber = '$pesel'");
				if(!$result) throw new Exception($GLOBALS['connection']->error);
				isTherePesel($result);

				//Jesli do tej pory wszystko przebieglo pomyslnie...
				if($GLOBALS['everythingOK'])
				{
					try
					{
						// ...to wygeneruj sol, hash'uj haslo i wprowadz dane do bazy za pomoca transakcji
						$salt = generateSalt();
						$passwdHash = sha1($passwd1 . $salt);
						if(saveNewUser($passwdHash, $salt))
							$GLOBALS['connection']->query("COMMIT");
						else
							throw new Exception($GLOBALS['connection']->error);

						// Ustaw prawdziwosc zmiennej 'udana_rejestracja' i prowadz do strony powitalnej
						$_SESSION['registrationIsOK'] = true;
						header('Location: dieticiansManager.php');
					}
					catch (Exception $e)
					{
						$GLOBALS['connection']->query("ROLLBACK");
						header("Location: html_files/serverError_goToLogout.html");
						//echo '<br/>Informacja developerska: '.$e;
					}
				}
				$GLOBALS['connection']->close();
			}
		}
		catch (Exception $e)
		{
			header("Location: html_files/serverError_goToLogout.html");
			//echo '<br/>Informacja developerska: '.$e;
		}
	}
}

// Glowna funkcja walidacyjna (uruchamia walidacje wszystkich pol)
function validation()
{
	firstName();
	lastName();
	dateOfTheBirth();
	pesel();
	login();
	email();
	passwds();
	dbConnection();
}

/**************************ZABEZPIECZENIE PRZED MULTI CLICK'IEM**************************/

require_once('multiClickPrevent.php');

// Sprawdzenie, czy formularz zostal wyslany
$postedToken = filter_input(INPUT_POST, 'token');
if(!empty($postedToken))
{
	if(isTokenValid($postedToken))
	{
		// Wszystko w porzadku, mozna przystapic do walidacji
		$GLOBALS['everythingOK'] = true;
		validation();
	}
	else
	{
		$helper_login = $_POST['login'];
		if(strlen($helper_login) >= 3 && strlen($helper_login) <= 20 && ctype_alnum($helper_login))
			$_SESSION['login'] = $helper_login;
		header("Location: multiclickError_signIn.php");
		exit();
	}
}

$token = getToken();
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset="utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
	<title>Panel admina NaturHouse</title>
	<link rel="stylesheet" href="css_files/basic.css" type="text/css"/>
	<link href="css_files/card.css" rel="stylesheet" type="text/css"/>
	<link href="css_files/newDietician.css" rel="stylesheet" type="text/css"/>
	<link href="css_files/submitButton.css" rel="stylesheet" type="text/css"/>
	<link href="css_files/contentCenter.css" rel="stylesheet" type="text/css"/>
	<link href="https://fonts.googleapis.com/css?family=Great+Vibes|Playfair+Display:400,700&amp;subset=latin-ext"
		  rel="stylesheet">
	<script src="javascript_files/jquery-3.1.1.min.js"></script>
	<script src="javascript_files/ajax/firstName.js"></script>
	<script src="javascript_files/ajax/lastName.js"></script>
	<script src="javascript_files/ajax/pesel.js"></script>
	<script src="javascript_files/ajax/signIn_login.js"></script>
	<script src="javascript_files/ajax/email.js"></script>
	<script src="javascript_files/ajax/signIn_passwords.js"></script>
	<script type="text/javascript" src="javascript_files/ajax/dayInSelectTag.js"></script>
	<script type="text/javascript" src="javascript_files/stickyMenu.js"></script>
	<link rel="stylesheet" type="text/css"
		  href="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.css"/>
	<script src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.js"></script>
	<script src="javascript_files/cookiesBanner.js"></script>
	<noscript><div id="infoAboutNoScript">Twoja przeglądarka nie obsługuje skryptów JavaScript!</div></noscript>
	<style>
		.menu > li:first-child
		{
			width: 25%;
		}

		.menu > li:first-child + li
		{
			width: 25%;
		}

		.menu > li:first-child + li + li
		{
			width: 25%;
		}

		.menu > li:first-child + li + li + li
		{
			width: 25%;
		}
	</style>
</head>

<body>
<div id="container">
	<div id="logo"><img id="logo-img" src="img/logo.jpg"/></div>
	<ol class="menu">
		<li><a href="adminPanel.php">Strona główna</a></li>
		<li><a href="dieticiansManager.php">Dietetycy</a></li>
		<li><a href="patientsManager.php">Pacjenci</a></li>
		<li><a href="logOut.php">Wyloguj</a></li>
	</ol>
	<div id="topbarPackage">
		<div id="topbar">
			<div id="topbarL"><img id="topbarL-img" src="img/admin.png"/></div>
			<div id="topbarR">
				<div id="quotation">„Jesteśmy tym, co wciąż powtarzamy. Doskonałość nie jest zatem aktem, ale nawykiem".</div>
				<div id="signature">Arystoteles</div>
			</div>
		</div>
	</div>
	<div id="content">
		<h1>Dodajemy nowego dietetyka</h1>
		<div id="signInForm">
			<form method="post">
				<!--Imie-->
				<input type="text" id="firstNameID" name="firstName" placeholder="imię" value="<?php
				if(isset($_SESSION['firstNameSaved']))
				{
					echo $_SESSION['firstNameSaved'];
					unset($_SESSION['firstNameSaved']);
				}
				?>"/>
				<div class="errorFromAjax" id="firstNameError"></div>
				<?php
				if(isset($_SESSION['firstNameError']))
				{
					echo '<div class="errorAfterSubmit" id="firstName_errorAfterSubmit">' . $_SESSION['firstNameError'] . '</div>';
					unset($_SESSION['firstNameError']);
				}
				?>

				<!--Nazwisko-->
				<input type="text" id="lastNameID" name="lastName" placeholder="nazwisko" value="<?php
				if(isset($_SESSION['lastNameSaved']))
				{
					echo $_SESSION['lastNameSaved'];
					unset($_SESSION['lastNameSaved']);
				}
				?>"/>
				<div class="errorFromAjax" id="lastNameError"></div>
				<?php
				if(isset($_SESSION['lastNameError']))
				{
					echo '<div class="errorAfterSubmit" id="lastName_errorAfterSubmit">' . $_SESSION['lastNameError'] . '</div>';
					unset($_SESSION['lastNameError']);
				}
				?>

				<!--Data urodzenia-->
				<div id="dateOfTheBirth">
					<div id="dateHeadline">Data urodzenia</div>
					<select title="year_title" name="year">
						<option>---rok---</option>
						<?php showYears(); ?>
					</select>
					<select title="month_title" name="month">
						<option>---miesiąc---</option>
						<?php showMonths(); ?>
					</select>
					<select title="day_title" name="day">
						<option>---dzień---</option>
					</select>
				</div>
				<?php
				if(isset($_SESSION['dateError']))
				{
					echo '<div class="errorAfterSubmit">' . $_SESSION['dateError'] . '</div>';
					unset($_SESSION['dateError']);
				}
				?>

				<!--Numer PESEL-->
				<input type="text" id="peselID" name="pesel" placeholder="pesel" value="<?php
				if(isset($_SESSION['peselSaved']))
				{
					echo $_SESSION['peselSaved'];
					unset($_SESSION['peselSaved']);
				}
				?>"/>
				<div class="errorFromAjax" id="peselError"></div>
				<?php
				if(isset($_SESSION['peselError']))
				{
					echo '<div class="errorAfterSubmit" id="pesel_errorAfterSubmit">' . $_SESSION['peselError'] . '</div>';
					unset($_SESSION['peselError']);
				}
				?>

				<!--Login-->
				<input type="text" id="loginID" name="login" placeholder="login" value="<?php
				if(isset($_SESSION['loginSaved']))
				{
					echo $_SESSION['loginSaved'];
					unset($_SESSION['loginSaved']);
				}
				?>"/>
				<div class="errorFromAjax" id="loginError"></div>
				<?php
				if(isset($_SESSION['loginError']))
				{
					echo '<div class="errorAfterSubmit" id="login_errorAfterSubmit">' . $_SESSION['loginError'] . '</div>';
					unset($_SESSION['loginError']);
				}
				?>

				<!--Email-->
				<input type="text" id="emailID" name="email" placeholder="e-mail" value="<?php
				if(isset($_SESSION['emailSaved']))
				{
					echo $_SESSION['emailSaved'];
					unset($_SESSION['emailSaved']);
				}
				?>"/>
				<div class="errorFromAjax" id="emailError"></div>
				<?php
				if(isset($_SESSION['emailError']))
				{
					echo '<div class="errorAfterSubmit" id="email_errorAfterSubmit">' . $_SESSION['emailError'] . '</div>';
					unset($_SESSION['emailError']);
				}
				?>

				<!--Haslo-->
				<input type="password" id="passwd1ID" name="passwd1" placeholder="hasło" value="<?php
				if(isset($_SESSION['passwd1Saved']))
				{
					echo $_SESSION['passwd1Saved'];
					unset($_SESSION['passwd1Saved']);
				}
				?>"/>

				<!--Powtorzone haslo-->
				<input type="password" id="passwd2ID" name="passwd2" placeholder="powtórz hasło" value="<?php
				if(isset($_SESSION['passwd2Saved']))
				{
					echo $_SESSION['passwd2Saved'];
					unset($_SESSION['passwd2Saved']);
				}
				?>"/>
				<div class="errorFromAjax" id="passwdError"></div>
				<?php
				if(isset($_SESSION['passwdError']))
				{
					echo '<div class="errorAfterSubmit" id="passwd_errorAfterSubmit">' . $_SESSION['passwdError'] . '</div>';
					unset($_SESSION['passwdError']);
				}
				?>

				<!--Submit zatwierdzajacy-->
				<input type="submit" value="Rejestruj"
					   onclick="this.disabled=true; this.value='Wczytuję...'; this.form.submit();"/>

				<!--Input przechowujacy token, ktory zapobiega multiclick'owi-->
				<input type="hidden" name="token" value="<?php echo $token; ?>"/>
			</form>
		</div>
	</div>
	<div id="footer">NaturHouse - Twój osobisty dietetyk. Strona w sieci od 2017 r. &copy;
					 Wszelkie prawa zastrzeżone</div>
</div>
</body>
</html>