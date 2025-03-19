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
        $zapytanie_lekarz = $link->prepare("SELECT imie, nazwisko FROM lekarz WHERE id_lekarza=?");
        $zapytanie_lekarz->bind_param('i', $_SESSION['zalogowany_id']);
        $zapytanie_lekarz->execute();
        $wynik_lekarz=$zapytanie_lekarz->get_result();
        if(mysqli_num_rows($wynik_lekarz) == 0){
            unset($_SESSION['zalogowany_lekarz']);
            unset($_SESSION['zalogowany_id']);
            header('Location: ../logowanie.php');
            exit();
        }
        $wynik_lekarz=$wynik_lekarz->fetch_array();

        $imie_lekarza = deszyfruj($wynik_lekarz['imie']);
        $nazwisko_lekarza = deszyfruj($wynik_lekarz['nazwisko']);
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
    <title>Ostatnie pobyty</title>
    <link href='lek-style.css' rel='stylesheet'>
</head>
<body>
<header>
    <h1 class='panel'>Panel lekarz</h1>
    <p class='zalogowal-sie'>Zalogował się lekarz: <?php echo $imie_lekarza?> <?php echo $nazwisko_lekarza?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-ostatnio-zakonczone-pobyty'>
<p class='strona-glowna'><a href='lek-strona-glowna.php'>Wróć do strony głównej</a></p>
<div class='komunikat'>
<?php
    if(isset($_SESSION['blad_wyswietl_akt_wypisu'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Wystąpił błąd przy próbie wyświetlenia aktu pobytu, nr ID Pobytu: <?php echo $_SESSION['akt_wypisu_id_pobytu']?>. Spróbuj ponownie.</b></p>
        <?php unset($_SESSION['blad_wyswietl_akt_wypisu']);
        unset($_SESSION['akt_wypisu_id_pobytu']);
    }
    if(isset($_SESSION['wygasl_dostep_akt_wypisu_pacjenta'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Dostęp do aktu wypisu pacjenta wygasł, nr ID Pobytu: <?php echo $_SESSION['akt_wypisu_id_pobytu']?></b></p>
        <?php unset($_SESSION['wygasl_dostep_akt_wypisu_pacjenta']);
        unset($_SESSION['akt_wypisu_id_pobytu']);
    }
    if(isset($_SESSION['brak_dostepu_akt_wypisu_pacjenta'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Brak dostępu do aktu wypisu pacjenta, nr ID Pobytu: <?php echo $_SESSION['akt_wypisu_id_pobytu']?></b></p>
        <?php unset($_SESSION['brak_dostepu_akt_wypisu_pacjenta']);
        unset($_SESSION['akt_wypisu_id_pobytu']);
    }
?>
</div>
<h2 class='informacje-ostatnio-zakonczone-pobyty'>Lista zakończonych pobytów w ostatnich 48 godzinach</h2>
<div class='tabela'>
    <table>
    <tr>
        <th>ID Pobytu</th>
        <th>ID Pacjenta</th>
        <th>Data rozpoczęcia pobytu</th>
        <th>Data zakończenia pobytu</th>  
        <th>Działania</th>
    </tr>
    <?php
    $wygenerowany_akt_wypisu = 1;
    $zapytanie_ostatnie_pobyty = $link->prepare("SELECT * FROM pobyt WHERE id_lekarza=? AND data_zakonczenia_pobytu IS NOT NULL AND czy_wygenerowano_akt_wypisu=?");
    $zapytanie_ostatnie_pobyty->bind_param('ii', $_SESSION['zalogowany_id'], $wygenerowany_akt_wypisu);
    $zapytanie_ostatnie_pobyty->execute();
    $wynik_ostatnie_pobyty = $zapytanie_ostatnie_pobyty->get_result();
    while($_wynik_ostatnie_pobyty=$wynik_ostatnie_pobyty->fetch_array()){
        if(strtotime(deszyfruj($_wynik_ostatnie_pobyty['data_zakonczenia_pobytu'])) > strtotime('-48 hours')){ ?>
        <tr>
            <td><?php echo $_wynik_ostatnie_pobyty['id_pobytu']?></td>
            <td><?php echo $_wynik_ostatnie_pobyty['id_pacjenta']?></td>
            <td><?php echo deszyfruj($_wynik_ostatnie_pobyty['data_rozpoczecia_pobytu'])?></td>
            <td><?php echo deszyfruj($_wynik_ostatnie_pobyty['data_zakonczenia_pobytu'])?></td>
            <td><a href='lek-wyswietl-akt-wypisu.php?id_pobytu=<?php echo $_wynik_ostatnie_pobyty['id_pobytu']?>'>Wyświetl akt wypisu pacjenta</a></td>        
        </tr>
        <?php } ?>
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
