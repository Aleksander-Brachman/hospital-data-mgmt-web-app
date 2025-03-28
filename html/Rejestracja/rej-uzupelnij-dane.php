<?php
require('../../polacz-baza/polacz-baza-rejestracja.php');

if(isset($_SESSION['zalogowany_rejestrator'])){
    $zapytanie_czy_uzupelniono_dane = $link->prepare("SELECT czy_uzupelniono_dane, id_typu_uzytkownika FROM uzytkownik WHERE id_uzytkownika=?");
    $zapytanie_czy_uzupelniono_dane->bind_param('i', $_SESSION['zalogowany_id']);
    $zapytanie_czy_uzupelniono_dane->execute();

    $wynik_czy_uzupelniono_dane = $zapytanie_czy_uzupelniono_dane->get_result();
    $wynik_czy_uzupelniono_dane = $wynik_czy_uzupelniono_dane->fetch_array();
    if($wynik_czy_uzupelniono_dane['id_typu_uzytkownika'] != "RS"){
        unset($_SESSION['zalogowany_rejestrator']);
        unset($_SESSION['zalogowany_id']);
        header('Location: ../logowanie.php');
        exit();
    }
    if($wynik_czy_uzupelniono_dane['czy_uzupelniono_dane'] == 1){
        header('Location: rej-strona-glowna.php');
        exit();
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
    <link href='rej-uzupelnij-dane-style.css' rel='stylesheet'>
    <title>Uzupełnij dane</title>
</head>
<body>
<header>
    <h1 class='panel'>Panel rejestracja</h1>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-uzupelnij-dane'>
<div class='informacje-uzupelnij-dane'>
    <p>Na początku uzupełnij swoje dane, które zostaną zapisane w systemie.<br>
    Po prawidłowym uzupełnieniu danych, ta strona po zalogowaniu <b>nie będzie się już wyświetlać.</b><br>
    Swoje dane będziesz mógł zaktualizować w każdym momencie, po wybraniu odpowiedniej opcji w panelu Rejestracji.</p>
</div>
<div class='komunikat'>
    <?php
    if(isset($_SESSION['ponowne_uzupelnienie_danych'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Uzupełnianie danych nie powiodło się. Spróbuj ponownie.</b></p><?php
        unset($_SESSION['ponowne_uzupelnienie_danych']);
    }
    ?>
</div>
<div class='formularz-uzupelnij-dane'>
<div class='secondary-container-uzupelnij-dane'>
<div class='dane-osobowe-zawodowe'>
    <form action='rej-wpisz-uzupelnij-dane.php' method='post'>
    <h3>Dane osobowe i zawodowe</h3>
    <p>Pola z gwiazdką są obowiązkowe.</p>
    <label>Imię* </label><input type='text' name='imie' autocomplete='off' maxlength='40' required>
    <label>Nazwisko* </label><input type='text' name='nazwisko' autocomplete='off' maxlength='40' required>
    <label>Płeć* </label><select name='plec'><option>Kobieta</option><option>Mężczyzna</option></select>
    <label>PESEL* </label><input type='text' name='pesel' pattern='^\d*' minlength='11' maxlength='11' autocomplete='off' required>
    <label>Data urodzenia* </label><input type='date' name='data_urodzenia' max='<?php echo date('Y-m-d'); ?>' required>
    <label>Miejsce urodzenia* </label><input type='text' name='miejsce_urodzenia' autocomplete='off' required>
</div>
<div class='dane-adresowe-kontaktowe'>
    <h3>Dane kontaktowe</h3>
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
    <label>Adres e-mail* </label><input type='email' name='email' placeholder='przykladowy@email.com' pattern='^[a-z0-9._]+@[a-z0-9.-]+\.[a-z]{2,4}$' autocomplete='off' required>
    <label>Telefon komórkowy* </label><input type='tel' name='telefon' placeholder='123 456 789' pattern='[0-9]{3} [0-9]{3} [0-9]{3}|[0-9]{9}' autocomplete='off' maxlength='11' required>
</div>
</div>
<button type='submit' name='rej_uzupelnij_dane'>Uzupełnij dane</button>
</form>
</div>
</div>
</main>
</body>
</html>
<?php
$link->close();
?>