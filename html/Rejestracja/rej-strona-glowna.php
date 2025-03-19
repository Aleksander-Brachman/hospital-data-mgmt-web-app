<?php
require('../../polacz-baza/polacz-baza-rejestracja.php');
require('../../zabezpieczenia/szyfruj-deszyfruj.php');

if(isset($_SESSION['sukces_logowania'])){
    unset($_SESSION['sukces_logowania']);
    $_SESSION['zalogowany_rejestrator'] = 1;

    $zapytanie_czy_uzupelniono_dane = $link->prepare("SELECT id_typu_uzytkownika, czy_uzupelniono_dane FROM uzytkownik WHERE id_uzytkownika=?");
    $zapytanie_czy_uzupelniono_dane->bind_param('i', $_SESSION['zalogowany_id']);
    $zapytanie_czy_uzupelniono_dane->execute();

    $wynik_czy_uzupelniono_dane = $zapytanie_czy_uzupelniono_dane->get_result();
    $wynik_czy_uzupelniono_dane = $wynik_czy_uzupelniono_dane->fetch_array();
    if($wynik_czy_uzupelniono_dane['czy_uzupelniono_dane'] == 0 && $wynik_czy_uzupelniono_dane['id_typu_uzytkownika'] == 'RS'){
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
    if(isset($_SESSION['zalogowany_rejestrator'])){
        $zapytanie_czy_uzupelniono_dane = $link->prepare("SELECT id_typu_uzytkownika, czy_uzupelniono_dane FROM uzytkownik WHERE id_uzytkownika=?");
        $zapytanie_czy_uzupelniono_dane->bind_param('i', $_SESSION['zalogowany_id']);
        $zapytanie_czy_uzupelniono_dane->execute();

        $wynik_czy_uzupelniono_dane = $zapytanie_czy_uzupelniono_dane->get_result();
        $wynik_czy_uzupelniono_dane = $wynik_czy_uzupelniono_dane->fetch_array();
        if($wynik_czy_uzupelniono_dane['czy_uzupelniono_dane'] == 0 && $wynik_czy_uzupelniono_dane['id_typu_uzytkownika'] == 'RS'){
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
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Rejestracja</title>
    <link href='rej-style.css' rel='stylesheet'>
</head>
<body>

<header>
    <h1 class='panel'>Panel rejestracja</h1>
    <p class='zalogowal-sie'>Zalogował się rejestrator: <?php echo $imie_rejestratora?> <?php echo $nazwisko_rejestratora?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-strona-glowna'>
<div class='komunikat'>
    <?php 
    if(isset($_SESSION['sukces_uzupelnienia_danych'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Uzupełnianie danych zakończyło się pomyślnie.</b></p><?php
        unset($_SESSION['sukces_uzupelnienia_danych']);
    }
    if(isset($_SESSION['blad_uzupelnienia_danych'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Dane osobowe i kontaktowe podane w procesie uzupełniania danych nie zostały zapisane. Skontaktuj się z Administratorem.</b></p><?php
        unset($_SESSION['blad_uzupelnienia_danych']);
    }
    if(isset($_SESSION['sukces_zmien_haslo'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Twoje hasło zostało pomyślnie zaktualizowane.</b></p><?php
        unset($_SESSION['sukces_zmien_haslo']);
    }
    if(isset($_SESSION['sukces_zmien_haslo_bez_daty'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Twoje hasło zostało pomyślnie zaktualizowane, ale bez zaktualizowania daty ostatniej zmiany hasła.</b></p><?php
        unset($_SESSION['sukces_zmien_haslo_bez_daty']);
    }
    if(isset($_SESSION['sukces_zmiana_danych'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Twoje dane zostały pomyślnie zaktualizowane.</b></p><?php
        unset($_SESSION['sukces_zmiana_danych']);
    }
    if(isset($_SESSION['blad_dodanie_daty_logowania'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Błąd: Data i godzina obecnego zalogowania nie zostały dodane do listy ostatnich logowań.</b></p><?php
        unset($_SESSION['blad_dodanie_daty_logowania']);
    }
    ?>
</div>
<nav class='lista-stron'>
    <ul>
        <li><a href='rej-dodaj-pacjenta.php'>Dodaj nowego pacjenta</a></li>
        <li><a href='rej-lista-pacjentow.php'>Lista pacjentów</a></li>
        <li><a href='rej-lista-lekarzy.php'>Lista lekarzy</a></li>
        <li><a href='rej-wyswietl-dane.php'>Wyświetl i zmień swoje dane</a></li>
        <li><a href='rej-lista-logowan.php'>Wyświetl daty ostatnich logowań</a></li>
        <li><a href='rej-zmien-haslo.php'>Zmień hasło</a></li>
    </ul>   
</nav>
</div>
</main>
</body>
</html>
<?php
$link->close();
?>