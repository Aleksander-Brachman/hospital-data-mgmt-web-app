<?php
require('../../polacz-baza/polacz-baza-lekarz.php');
require('../../zabezpieczenia/szyfruj-deszyfruj.php');

if(isset($_SESSION['zalogowany_lekarz'])){
    $zapytanie_czy_uzupelniono_dane = $link->prepare("SELECT czy_uzupelniono_dane FROM uzytkownik WHERE id_uzytkownika=?");
    $zapytanie_czy_uzupelniono_dane->bind_param('i', $_SESSION['zalogowany_id']);
    $zapytanie_czy_uzupelniono_dane->execute();
    $wynik_czy_uzupelniono_dane = $zapytanie_czy_uzupelniono_dane->get_result();
    $wynik_czy_uzupelniono_dane = $wynik_czy_uzupelniono_dane->fetch_array();
        
    if($wynik_czy_uzupelniono_dane['czy_uzupelniono_dane'] == 0){
        header('Location: lek-uzupelnij-dane.php');
        exit();
    }
    else{
        $zapytanie_lekarz = $link->prepare("SELECT imie, nazwisko, data_ostatniej_zmiany_hasla FROM lekarz WHERE id_lekarza=?");
        $zapytanie_lekarz->bind_param('i', $_SESSION['zalogowany_id']);
        $zapytanie_lekarz->execute();
        $wynik_lekarz = $zapytanie_lekarz->get_result();
        if(mysqli_num_rows($wynik_lekarz) == 0){
            unset($_SESSION['zalogowany_lekarz']);
            unset($_SESSION['zalogowany_id']);
            header('Location: ../logowanie.php');
            exit();
        }
        $wynik_lekarz = $wynik_lekarz->fetch_array();

        $imie_lekarza = deszyfruj($wynik_lekarz['imie']);
        $nazwisko_lekarza = deszyfruj($wynik_lekarz['nazwisko']);
        if($wynik_lekarz['data_ostatniej_zmiany_hasla'] == NULL){
            $data_ostatniej_zmiany_hasla = 'Brak zmian hasła';
        }
        else{
            $data_ostatniej_zmiany_hasla = deszyfruj($wynik_lekarz['data_ostatniej_zmiany_hasla']);
        }
    }
}
else{
    header('Location: ../logowanie.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Zmień hasło</title>    
    <link href='lek-style.css' rel='stylesheet'>
    <script type='text/javascript' src='jquery-3.7.1.min.js'></script>
</head>
<body>
<header>
    <h1 class='panel'>Panel lekarz</h1>
    <p class='zalogowal-sie'>Zalogował się lekarz: <?php echo $imie_lekarza?> <?php echo $nazwisko_lekarza?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-zmien-haslo'>
<p class='strona-glowna'><a href='lek-strona-glowna.php'>Wróć do strony głównej</a></p>
<div class='informacje-zmien-haslo'>
    <p class='ostatnia-zmiana-hasla'><b>Ostatnia zmiana hasła: <?php echo $data_ostatniej_zmiany_hasla?></b></p>
</div>
<div class='komunikat-blad-zmiany-hasla'>
<?php
if(isset($_SESSION['blad_zmien_haslo'])){
        ?><style>
        .komunikat-blad-zmiany-hasla{
            border: 1px solid black;
        }
        </style>
        <p><b>Nie udało się zaktualizować hasła. Zwróć uwagę na poprawność danych i spróbuj ponownie.</b></p><?php
        unset($_SESSION['blad_zmien_haslo']);
}
?>
</div>
<div class='secondary-container-zmien-haslo'>
<div class='wymogi-zmiany-hasla'>
<p>Jeżeli chcesz zmienić swoje hasło, skorzystaj z formularza obok.</p>
    <p><b>Hasło powinno:</b></p>
    <ul>
        <li><p id='dlugosc' class='nie-spelnia'><b>Zawierać co najmniej osiem znaków i maksymalnie dwadzieścia cztery znaki</p></b></li>
        <li><p id='wielka_litera' class='nie-spelnia'><b>Zawierać co najmniej jedną wielką literę</p></b></li>
        <li><p id='cyfra' class='nie-spelnia'><b>Zawierać co najmniej jedną cyfrę</p></b></li>
        <li><p id='znak_specjalny' class='nie-spelnia'><b>Zawierać co najmniej jeden znak specjalny (!, ?, &, *)</p></b></li>
    </ul>
</div>
<div class='zmien-haslo-formularz'>
    <form action='lek-wpisz-zmien-haslo.php' method='post'>
    <label>Wprowadź obecne hasło</label><input type='password' name='obecne_haslo' required>
    <label>Wprowadź nowe hasło</label><input type='password' id='nowe_haslo' name='nowe_haslo' pattern='^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!?&*]).{8,24}$' title='Co najmniej jedna wielka litera, jedna cyfra, jeden znak specjalny i między 8, a 24 znaki' required>           
    <label>Powtórz nowe hasło</label><input type='password' name='nowe_haslo_potwierdz' pattern='^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!?&*]).{8,24}$' title='Co najmniej jedna wielka litera, jedna cyfra, jeden znak specjalny i między 8, a 24 znaki' required>
    <button type='submit' name='lek_zmien_haslo'>Zmień hasło</button>
    </form>
</div>
</div>
</div>
</main>
</body>
<script>
        var haslo = document.getElementById('nowe_haslo');
        var wielka_litera = document.getElementById('wielka_litera');
        var znak_specjalny = document.getElementById('znak_specjalny');
        var cyfra = document.getElementById('cyfra');
        var dlugosc = document.getElementById('dlugosc');
        
        haslo.onkeyup = function() {

        var znaki_specjalne = /[!?&*]/g;
        if(haslo.value.match(znaki_specjalne)) {
            znak_specjalny.classList.remove('nie-spelnia');
            znak_specjalny.classList.add('spelnia');
        } else {
            znak_specjalny.classList.remove('spelnia');
            znak_specjalny.classList.add('nie-spelnia');
        }

        var wielkie_litery = /[A-Z]/g;
        if(haslo.value.match(wielkie_litery)) {
            wielka_litera.classList.remove('nie-spelnia');
            wielka_litera.classList.add('spelnia');
        } else {
            wielka_litera.classList.remove('spelnia');
            wielka_litera.classList.add('nie-spelnia');
        }

        var cyfry = /[0-9]/g;
        if(haslo.value.match(cyfry)) {
            cyfra.classList.remove('nie-spelnia');
            cyfra.classList.add('spelnia');
        } else {
            cyfra.classList.remove('spelnia');
            cyfra.classList.add('nie-spelnia');
        }

        if(haslo.value.length >= 8 && haslo.value.length < 24) {
            dlugosc.classList.remove('nie-spelnia');
            dlugosc.classList.add('spelnia');
        } else {
            dlugosc.classList.remove('spelnia');
            dlugosc.classList.add('nie-spelnia');
        }
}
</script>
</html>
<?php
$link->close();
?>
