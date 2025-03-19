<?php
require('../../polacz-baza/polacz-baza-lekarz.php');
require('../../zabezpieczenia/szyfruj-deszyfruj.php');

if(isset($_SESSION['sukces_logowania'])){
    unset($_SESSION['sukces_logowania']);
    $_SESSION['zalogowany_lekarz'] = 1;

    $zapytanie_czy_uzupelniono_dane = $link->prepare("SELECT id_typu_uzytkownika, czy_uzupelniono_dane FROM uzytkownik WHERE id_uzytkownika=?");
    $zapytanie_czy_uzupelniono_dane->bind_param('i', $_SESSION['zalogowany_id']);
    $zapytanie_czy_uzupelniono_dane->execute();
    $wynik_czy_uzupelniono_dane = $zapytanie_czy_uzupelniono_dane->get_result();
    $wynik_czy_uzupelniono_dane = $wynik_czy_uzupelniono_dane->fetch_array();
    
    if($wynik_czy_uzupelniono_dane['czy_uzupelniono_dane'] == 0 && $wynik_czy_uzupelniono_dane['id_typu_uzytkownika'] == 'LK'){
        header('Location: lek-uzupelnij-dane.php');
        exit();
    }
    else{
        $zapytanie_lekarz = $link->prepare("SELECT imie, nazwisko FROM lekarz WHERE id_lekarza=?");
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

        $zapytanie_pacjent = $link->prepare("SELECT id_pacjenta, id_pobytu, imie, nazwisko, pesel, ulica, nr_domu, data_rozpoczecia_ostatniego_pobytu FROM pacjent WHERE id_lekarza=? ORDER BY id_pacjenta");
        $zapytanie_pacjent->bind_param('i', $_SESSION['zalogowany_id']);
        $zapytanie_pacjent->execute();
    }
}
else{
    if(isset($_SESSION['zalogowany_lekarz'])){
        $zapytanie_czy_uzupelniono_dane = $link->prepare("SELECT id_typu_uzytkownika, czy_uzupelniono_dane FROM uzytkownik WHERE id_uzytkownika=?");
        $zapytanie_czy_uzupelniono_dane->bind_param('i', $_SESSION['zalogowany_id']);
        $zapytanie_czy_uzupelniono_dane->execute();
        $wynik_czy_uzupelniono_dane = $zapytanie_czy_uzupelniono_dane->get_result();
        $wynik_czy_uzupelniono_dane = $wynik_czy_uzupelniono_dane->fetch_array();
        
        if($wynik_czy_uzupelniono_dane['czy_uzupelniono_dane'] == 0 && $wynik_czy_uzupelniono_dane['id_typu_uzytkownika'] == 'LK'){
            header('Location: lek-uzupelnij-dane.php');
            exit();
        }
        else{
            $zapytanie_lekarz=$link->prepare("SELECT imie, nazwisko FROM lekarz WHERE id_lekarza=?");
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

            $zapytanie_pacjent=$link->prepare("SELECT id_pacjenta, id_pobytu, imie, nazwisko, pesel, ulica, nr_domu, data_rozpoczecia_ostatniego_pobytu FROM pacjent WHERE id_lekarza=? ORDER BY id_pacjenta");
            $zapytanie_pacjent->bind_param('i', $_SESSION['zalogowany_id']);
            $zapytanie_pacjent->execute();
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
    <title>Lekarz</title>
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
<nav class='lista-stron'>
<ul>
    <li><a href='lek-dodaj-pobyt.php'>Dodaj nowy pobyt</a></li>
    <li><a href='lek-ostatnio-zakonczone-pobyty.php'>Ostatnio zakończone pobyty</a></li>
    <li><a href='lek-lista-lekarzy.php'>Lista lekarzy</a></li>
    <li><a href='lek-wyswietl-dane.php'>Wyświetl i zmień swoje dane</a></li>
    <li><a href='lek-lista-logowan.php'>Wyświetl daty ostatnich logowań</a></li>
    <li><a href='lek-zmien-haslo.php'>Zmień hasło</a></li>
</ul>   
</nav>
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
    </style><p><b>Hasło zostało pomyślnie zaktualizowane.</b></p><?php
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
    if(isset($_SESSION['sukces_zmiana_danych_pacjenta'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
    </style><p><b>Dane pacjenta o nr ID: <?php echo $_SESSION['zmiana_danych_id_pacjenta']?> zostały zmienione pomyślnie.</b></p><?php
        unset($_SESSION['zmiana_danych_id_pacjenta']);
        unset($_SESSION['sukces_zmiana_danych_pacjenta']);
    }
    if(isset($_SESSION['sukces_dodaj_pobyt'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
    </style><p><b>Nowy pobyt pacjenta został dodany pomyślnie. ID Pacjenta: <?php echo $_SESSION['nowy_pacjent_id']?>, ID Pobytu: <?php echo $_SESSION['nowy_pobyt_id']?></b></p><?php
        unset($_SESSION['sukces_dodaj_pobyt']);
        unset($_SESSION['nowy_pacjent_id']);
        unset($_SESSION['nowy_pobyt_id']);
    }
    if(isset($_SESSION['blad_powiazania_pacjent_pobyt'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
    </style><p><b>Dane pobytu zostały poprawnie dodane do systemu, ale nie doszło do ich powiązania z danymi Pacjenta.<br>
        Skontaktuj się z Administratorem podając te numery: ID Pacjenta: <?php echo $_SESSION['nowy_pacjent_id']?>, ID Pobytu: <?php $_SESSION['nowy_pobyt_id']?></b></p><?php
        unset($_SESSION['blad_powiazania_pacjent_pobyt']);
        unset($_SESSION['nowy_pacjent_id']);
        unset($_SESSION['nowy_pobyt_id']);
    }
    if(isset($_SESSION['brak_dostepu_dane_pacjenta'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
    </style><p><b>Brak dostępu do danych pacjenta o nr ID: <?php echo $_SESSION['brak_dostepu_id_pacjenta']?></b></p>
        <?php unset($_SESSION['brak_dostepu_dane_pacjenta']);
        unset($_SESSION['brak_dostepu_id_pacjenta']);
    }
    if(isset($_SESSION['brak_dostepu_pobyt_pacjenta'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
    </style><p><b>Brak dostępu do danych pobytu o nr ID: <?php echo $_SESSION['brak_dostepu_id_pobytu']?></b></p>
        <?php unset($_SESSION['brak_dostepu_pobyt_pacjenta']);
        unset($_SESSION['brak_dostepu_id_pobytu']);
    }
    if(isset($_SESSION['sukces_aktualizacja_pobytu'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
    </style><p><b>Udało się zaktualizować dane pobytu o nr ID: <?php echo $_SESSION['aktualizacja_id_pobytu']?> dla pacjenta nr: <?php echo $_SESSION['aktualizacja_id_pacjenta']?></b></p>
        <?php unset($_SESSION['aktualizacja_id_pacjenta']);
        unset($_SESSION['aktualizacja_id_pobytu']);
        unset($_SESSION['sukces_aktualizacja_pobytu']);
    }
    if(isset($_SESSION['sukces_wypis_pacjenta'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
    </style><p><b>Udało się poprawnie wypisać pacjenta o nr ID: <?php echo $_SESSION['wypis_id_pacjenta']?></b></p>
        <?php unset($_SESSION['sukces_wypis_pacjenta']);
        unset($_SESSION['wypis_id_pacjenta']);
    }
    if(isset($_SESSION['blad_wypis_pacjenta'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
    </style><p><b>Pobyt pacjenta został zakończony, ale nie udało się zaktualizować poprawnie danych po zakończeniu pobytu na koncie Pacjenta nr: <?php echo $_SESSION['wypis_id_pacjenta']?><br>
        Zgłoś ten fakt Administratorowi.</b></p>
        <?php unset($_SESSION['blad_wypis_pacjenta']);
        unset($_SESSION['wypis_id_pacjenta']);
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
<h2 class='informacje-lista-pacjentow'>Lista Twoich pacjentów</h2>
<div class='tabela'>
<table>
    <tr class='kolumny-pacjent'>
        <th>ID Pacjenta</th>
        <th>Imię i nazwisko</th>
        <th>PESEL</th>
        <th>Data rozpoczęcia pobytu</th>
        <th>ID Pobytu</th>
        <th>Działania</th>
    </tr>
<?php
$wynik_pacjent = $zapytanie_pacjent->get_result();
if(mysqli_num_rows($wynik_pacjent) == 0){?> 
    <h3>Brak pacjentów</h3>
    <script>
        $('.kolumny-pacjent').hide()
    </script>
<?php } ?>
<?php
while($_wynik_pacjent = $wynik_pacjent->fetch_array()){
?>
    <tr>
        <td><?php echo $_wynik_pacjent['id_pacjenta']?></td>
        <td><?php echo deszyfruj($_wynik_pacjent['imie'])?> <?php echo deszyfruj($_wynik_pacjent['nazwisko'])?></td>
        <td><?php echo deszyfruj($_wynik_pacjent['pesel'])?></td>
        <td><?php echo deszyfruj($_wynik_pacjent['data_rozpoczecia_ostatniego_pobytu'])?></td>
        <td><?php echo $_wynik_pacjent['id_pobytu']?></td>
        <td><a href='lek-wyswietl-pobyt.php?id_pobytu=<?php echo $_wynik_pacjent['id_pobytu']?>'>Wyświetl informacje o pobycie pacjenta</a>
        <a href='lek-wyswietl-dane-pacjenta.php?id_pacjenta=<?php echo $_wynik_pacjent['id_pacjenta']?>'>Wyświetl i zmień dane pacjenta</a></td>        
    </tr>
<?php } ?>
</table>
</div>
</div>
</main>
</body>
</html>
<?php
$link->close();
?>
