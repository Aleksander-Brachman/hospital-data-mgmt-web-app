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
        if(isset($_GET['id_pacjenta'])){
            $zapytanie_pobyt = $link->prepare("SELECT id_pacjenta FROM pobyt WHERE id_pacjenta=? AND id_lekarza=? AND data_zakonczenia_pobytu IS NULL");
            $zapytanie_pobyt->bind_param('ii', $_GET['id_pacjenta'], $_SESSION['zalogowany_id']);
            $zapytanie_pobyt->execute();
            $wynik_pobyt_ = $zapytanie_pobyt->get_result();
            if(mysqli_num_rows($wynik_pobyt_)==0){
                $_SESSION['brak_dostepu_dane_pacjenta'] = 1;
                $_SESSION['brak_dostepu_id_pacjenta'] = $_GET['id_pacjenta'];
                header('Location: lek-strona-glowna.php');
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
                $wynik_lekarz = $wynik_lekarz->fetch_array();

                $imie_lekarza = deszyfruj($wynik_lekarz['imie']);
                $nazwisko_lekarza = deszyfruj($wynik_lekarz['nazwisko']);
            }
        }
        else{
            header('Location: lek-strona-glowna.php');
            exit();
        }
    }
}
else{
    header('Location: ../logowanie.php');
    exit();
}
?>
<?php
$zapytanie_pacjent = $link->prepare("SELECT * FROM pacjent WHERE id_pacjenta=? AND id_lekarza=?");
$zapytanie_pacjent->bind_param('ii', $_GET['id_pacjenta'], $_SESSION['zalogowany_id']);
$zapytanie_pacjent->execute();
if(!$zapytanie_pacjent){
    header('Location: lek-strona-glowna.php');
    exit();
}
$wynik_pacjent = $zapytanie_pacjent->get_result();
$wynik_pacjent = $wynik_pacjent->fetch_array();
if(!$wynik_pacjent){
    header('Location: lek-strona-glowna.php');
    exit();
}
$id_pacjenta = $wynik_pacjent['id_pacjenta'];
$imie = deszyfruj($wynik_pacjent['imie']);
$nazwisko = deszyfruj($wynik_pacjent['nazwisko']);
$pesel = deszyfruj($wynik_pacjent['pesel']);
$id_pobytu = $wynik_pacjent['id_pobytu'];

$zapytanie_pobyt_ = $link->prepare("SELECT * FROM pobyt WHERE id_pobytu=? AND id_pacjenta=? AND id_lekarza=? AND data_zakonczenia_pobytu IS NULL");
$zapytanie_pobyt_->bind_param('iii', $id_pobytu, $id_pacjenta, $_SESSION['zalogowany_id']);
$zapytanie_pobyt_->execute();
if(!$zapytanie_pobyt_){
    header('Location: lek-strona-glowna.php');
    exit();
}
$wynik_pobyt_ = $zapytanie_pobyt_->get_result();
$wynik_pobyt_ = $wynik_pobyt_->fetch_array();
if(!$wynik_pobyt_){
    header('Location: lek-strona-glowna.php');
    exit();
}

$historia_pobytu = deszyfruj($wynik_pobyt_['historia_pobytu']);
$data_rozpoczecia_pobytu = deszyfruj($wynik_pobyt_['data_rozpoczecia_pobytu']);
$data_ostatniej_zmiany_danych_pobytu = $wynik_pobyt_['data_ostatniej_zmiany_danych_pobytu'];
if($data_ostatniej_zmiany_danych_pobytu == NULL){
    $data_ostatniej_zmiany_danych_pobytu = 'Brak aktualizacji informacji związanych z pobytem';
}
else{
    $data_ostatniej_zmiany_danych_pobytu = deszyfruj($wynik_pobyt_['data_ostatniej_zmiany_danych_pobytu']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Wypisz pacjenta</title>
    <link href='lek-style.css' rel='stylesheet'>
</head>
<body>
<header>
    <h1 class='panel'>Panel lekarz</h1>
    <p class='zalogowal-sie'>Zalogował się lekarz: <?php echo $imie_lekarza?> <?php echo $nazwisko_lekarza?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-wypisz-pacjenta'>
<p class='strona-glowna'><a href='lek-wyswietl-pobyt.php?id_pobytu=<?php echo $id_pobytu?>'>Wróć do karty pobytu</a></p>
<div class='informacje-wypisz-pacjenta'>
<p class='ostatnia-zmiana-danych-pobytu'><b>Pobyt nr ID: <?php echo $id_pobytu?>. Ostatnia zmiana danych pobytu: <?php echo $data_ostatniej_zmiany_danych_pobytu?></b></p>
<div class='komunikat'>
    <?php
    if(isset($_SESSION['blad_wypis_pacjenta'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Nie udało się wypisać pacjenta. Spróbuj ponownie.</b></p><?php
        unset($_SESSION['blad_wypis_pacjenta']);
    }
    ?>
</div>
<p>
    Jeżeli chcesz wypisać pacjenta o poniższych danych, kliknij przycisk <i>Wypisz pacjenta</i>. <b>Operacja wypisu pacjenta jest ostateczna.</b><br>
    Akt wypisu pacjenta z podsumowaniem pobytu, który możesz dodać do aktu w polu poniżej, będzie dostępny przez 48 godzin od wypisu pacjenta.<br>
    <b>Historia obecnego pobytu, po wypisaniu pacjenta, zostanie zapisana w historii pacjenta związanej z kontem pacjenta w systemie.</b><br>
    Możesz wrócić do karty pobytu, klikając odnośnik <i>Wróć do karty pobytu</i>.
</p>
</div>
<div class='potwierdz-wypis-pacjenta'>
<h3>Czy chcesz wypisać pacjenta o poniższych danych?</h3>
<div class='informacje-o-pacjencie'>
<label>ID Pacjenta: </label><b><?php echo $id_pacjenta?></b><br>
<label>ID Pobytu: </label><b><?php echo $id_pobytu?></b><br>
<label>Data rozpoczęcia pobytu: </label><b><?php echo $data_rozpoczecia_pobytu?></b><br>
<label>Imię: </label><b><?php echo $imie?></b><br>
<label>Nazwisko: </label><b><?php echo $nazwisko?></b><br>
<label>PESEL: </label><b><?php echo $pesel?></b><br>
</div>
<form action='lek-dokoncz-wypisz-pacjenta.php?id_pacjenta=<?php echo $id_pacjenta?>' method='post'>
<label>Podsumowanie pobytu:</label><textarea name='podsumowanie_pobytu' rows='8' cols='100' autocomplete='off'></textarea>
<button type='submit' name='lek_wypisz_pacjenta'>Wypisz pacjenta</button>
</form>
</div>
</div>
</main>
</body>
</html> 
<?php
$link->close();
?>

