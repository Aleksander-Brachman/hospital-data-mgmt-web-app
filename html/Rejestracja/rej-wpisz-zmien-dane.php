<?php
require('../../polacz-baza/polacz-baza-rejestracja.php');
require('../../zabezpieczenia/szyfruj-deszyfruj.php');
require('../../zabezpieczenia/zabezpieczenia-formularz.php');

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
}
else{
    header('Location: ../logowanie.php');
    exit();
}
?>
<?php
if(isset($_POST['rej_zmien_dane'])){
    $ulica = szyfruj(zabezpieczenia($_POST['ulica']));
    $nr_domu = szyfruj(zabezpieczenia($_POST['nr_domu']));
    if(!empty($_POST['nr_mieszkania'])){
        $nr_mieszkania = szyfruj(zabezpieczenia($_POST['nr_mieszkania']));
    }
    else{
        $nr_mieszkania = '';
    }   
    $miasto = szyfruj(zabezpieczenia($_POST['miasto']));
    if($_POST['wojewodztwo'] == 'nie_wybrano'){
        $wojewodztwo = szyfruj('Nie podano');
    }
    else{
        $wojewodztwo = szyfruj(zabezpieczenia($_POST['wojewodztwo']));
    }
    $kod_pocztowy = szyfruj(zabezpieczenia($_POST['kod_pocztowy']));
    $email = szyfruj(zabezpieczenia($_POST['email']));
    $tel = szyfruj(zabezpieczenia($_POST['telefon']));
    $data_zmiany_danych = szyfruj(date_create()->format('Y-m-d H:i:s'));

    $aktualizuj_dane = $link->prepare("UPDATE rejestrator SET ulica=?, nr_domu=?, nr_mieszkania=?, miasto=?, wojewodztwo=?, kod_pocztowy=?, email=?, telefon=?, data_ostatniej_zmiany_danych=? WHERE id_rejestratora=?");
    $aktualizuj_dane->bind_param('sssssssssi', $ulica,$nr_domu,$nr_mieszkania,$miasto,$wojewodztwo,$kod_pocztowy,$email,$tel,$data_zmiany_danych,$_SESSION['zalogowany_id']);
    $aktualizuj_dane->execute();
    if($aktualizuj_dane){
        $_SESSION['sukces_zmiana_danych']=1;
        header('Location: rej-strona-glowna.php');
        exit();
    }
    else{
        $_SESSION['blad_zmiana_danych']=1;
        header('Location: rej-wyswietl-dane.php');
        exit();
    }
}
else{
    header('Location: rej-wyswietl-dane.php');
    exit();
}
?>
<?php
$link->close();
?>