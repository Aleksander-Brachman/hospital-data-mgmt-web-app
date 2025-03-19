<?php
require('../../polacz-baza/polacz-baza-rejestracja.php');
require('../../zabezpieczenia/szyfruj-deszyfruj.php');

if(isset($_SESSION['zalogowany_rejestrator'])){
    $zapytanie_czy_uzupelniono_dane = $link->prepare("SELECT czy_uzupelniono_dane FROM uzytkownik WHERE id_uzytkownika=?");
    $zapytanie_czy_uzupelniono_dane->bind_param('i', $_SESSION['zalogowany_id']);
    $zapytanie_czy_uzupelniono_dane->execute();
    $wynik_czy_uzupelniono_dane = $zapytanie_czy_uzupelniono_dane->get_result();
    $wynik_czy_uzupelniono_dane = $wynik_czy_uzupelniono_dane->fetch_array();

    if($wynik_czy_uzupelniono_dane['czy_uzupelniono_dane'] == 0){
        header('Location: rej-uzupelnij-dane.php');
        exit();
    }
    else{
        $zapytanie_rejestrator = $link->prepare("SELECT imie, nazwisko FROM rejestrator WHERE id_rejestratora=?");
        $zapytanie_rejestrator->bind_param('i', $_SESSION['zalogowany_id']);
        $zapytanie_rejestrator->execute();
        $wynik_rejestrator = $zapytanie_rejestrator->get_result();
        if(mysqli_num_rows($wynik_rejestrator) == 0){
            unset($_SESSION['zalogowany_rejestrator']);
            unset($_SESSION['zalogowany_id']);
            header('Location: ../logowanie.php');
            exit();
        }
        $wynik_rejestrator = $wynik_rejestrator->fetch_array();
        $imie_rejestratora = deszyfruj($wynik_rejestrator['imie']);
        $nazwisko_rejestratora = deszyfruj($wynik_rejestrator['nazwisko']);
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
    <title>Dodaj pacjenta</title>
    <link href='rej-style.css' rel='stylesheet'>
    <script type='text/javascript' src='jquery-3.7.1.min.js'></script>
    <script>
        function zmianaObywatelstwa(checkbox) { 
            if (checkbox.checked && checkbox.id === 'inne_obywatelstwo') {
                document.getElementById('obywatelstwo').removeAttribute('disabled');
                document.getElementById('obywatelstwo').removeAttribute('value');
                document.getElementById('obywatelstwo').setAttribute('placeholder', 'Wpisz obywatelstwo');
                document.getElementById('pesel').setAttribute('disabled', true);
            } else {
                document.getElementById('obywatelstwo').setAttribute('disabled', true);
                document.getElementById('obywatelstwo').setAttribute('value', 'polskie');
                document.getElementById('obywatelstwo').removeAttribute('placeholder');
                document.getElementById('pesel').removeAttribute('disabled');
            }
        }
    </script>
</head>
<body>
<header>
    <h1 class='panel'>Panel rejestracja</h1>
    <p class='zalogowal-sie'>Zalogował się rejestrator: <?php echo $imie_rejestratora?> <?php echo $nazwisko_rejestratora?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-dodaj-pacjenta'>
<p class='strona-glowna'><a href='rej-strona-glowna.php'>Wróć do strony głównej</a></p>
<div class='informacje-dodaj-pacjenta'>
    <p>
    Wypełnij formularz o dane osobowe i kontaktowe nowego pacjenta.<br>
    Dane kontaktowe będą mogły być zmienione później w zakładce <i>Wyświetl i zmień dane pacjenta</i> na liście pacjentów, dostępnej z strony głównej.
    </p>
</div>
<div class='komunikat'>
    <?php
    if(isset($_SESSION['blad_dodaj_pacjenta'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Dodanie nowego pacjenta nie powiodło się. Spróbuj ponownie.</b></p><?php
        unset($_SESSION['blad_dodaj_pacjenta']);
    }
    if(isset($_SESSION['pesel_znajduje_sie_w_systemie'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Pacjent o podanym PESEL znajduje się już w systemie. ID tego pacjenta to: <?php echo $_SESSION['powtorzony_pesel_id_pacjenta']?>. Spróbuj ponownie.</b></p><?php
        unset($_SESSION['pesel_znajduje_sie_w_systemie']);
        unset($_SESSION['powtorzony_pesel_id_pacjenta']);
    }
    ?>     
</div>
<div class='formularz-dodaj-pacjenta'>
<div class='secondary-container-dodaj-pacjenta'>
<div class='dane-osobowe-pacjenta'>
    <form action='rej-wpisz-nowego-pacjenta.php' method='post'>
        <h3>Dane osobowe pacjenta</h3>
        <p>Pola z gwiazdką są obowiązkowe.</p>
            <label>Imię* </label><input type='text' name='imie' autocomplete='off' maxlength='40' required>
            <label>Nazwisko* </label><input type='text' name='nazwisko' autocomplete='off' maxlength='40' required>
            <label>Płeć* </label><select name='plec'><option>Kobieta</option><option>Mężczyzna</option></select>
            <div class='checkbox-obywatelstwo'>
            <input type='checkbox' id='inne_obywatelstwo' onclick='zmianaObywatelstwa(this)'><label>Inne obywatelstwo niż polskie</label>
            </div>
            <label>Obywatelstwo </label><input  type='text' id='obywatelstwo' name='obywatelstwo' value='polskie'  autocomplete='off' maxlength='40' disabled>
            <label>PESEL* </label><input  type='text' id='pesel' name='pesel' pattern='^\d*'  minlength='11' maxlength='11' autocomplete='off' required> 
            <label>Data urodzenia* </label><input type='date' name='data_urodzenia' max='<?php echo date('Y-m-d'); ?>' required>
            <label>Miejsce urodzenia* </label><input type='text' name='miejsce_urodzenia' autocomplete='off' required>
</div>
<div class='dane-adresowe-kontaktowe-pacjenta'>
        <h3>Dane kontaktowe pacjenta</h3>
        <p>Pola z gwiazdką są obowiązkowe.</p>
            <label>Ulica* </label><input type='text' name='ulica' autocomplete='off' maxlength='40' required>
            <label>Nr domu* </label><input type='number' name='nr_domu' autocomplete='off' maxlength='4' required>
            <label>Nr mieszkania (jeżeli dotyczy) </label><input type='number' name='nr_mieszkania' autocomplete='off' maxlength='4'>
            <label>Miasto* </label><input type='text' name='miasto' autocomplete='off'  maxlength='40' required>
            <label>Województwo* </label><select name='wojewodztwo'><option value='nie_wybrano'>Wybierz województwo</option><option>Zachodnio-Pomorskie</option><option>Lubuskie</option><option>Dolnośląskie</option><option>Opolskie</option>
            <option>Śląskie</option><option>Małopolskie</option><option>Podkarpackie</option><option>Świętokrzyskie</option><option>Łódzkie</option>
            <option>Kujawsko-Pomorskie</option><option>Mazowieckie</option><option>Warmińsko-Mazurskie</option><option>Podlaskie</option><option>Lubelskie</option>
            <option>Wielkopolskie</option><option>Pomorskie</option></select>
            <label>Kod pocztowy* </label><input type='text' name='kod_pocztowy' placeholder='00-000' pattern='^\d{2}-\d{3}$' autocomplete='off' maxlength='6' required>
            <label>Adres e-mail </label><input type='email' name='email' placeholder='przykladowy@email.com' pattern='^[a-z0-9._]+@[a-z0-9.-]+\.[a-z]{2,4}$' autocomplete='off'  maxlength='40'>
            <label>Telefon komórkowy </label><input type='tel' name='telefon' placeholder='123 456 789' pattern='[0-9]{3} [0-9]{3} [0-9]{3}|[0-9]{9}' autocomplete='off' maxlength='11'>
</div>
</div>
<button type='submit' name='rej_dodaj_pacjenta'>Dodaj pacjenta</button>
</form>
</div>
</div>
</main>
</body>
</html>
<?php
$link->close();
?>
