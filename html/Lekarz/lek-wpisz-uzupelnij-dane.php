<?php
require('../../polacz-baza/polacz-baza-lekarz.php');
require('../../zabezpieczenia/szyfruj-deszyfruj.php');
require('../../zabezpieczenia/zabezpieczenia-formularz.php');

if(isset($_SESSION['zalogowany_lekarz'])){
    $zapytanie_czy_uzupelniono_dane = $link->prepare("SELECT czy_uzupelniono_dane FROM uzytkownik WHERE id_uzytkownika=?");
    $zapytanie_czy_uzupelniono_dane->bind_param('i', $_SESSION['zalogowany_id']);
    $zapytanie_czy_uzupelniono_dane->execute();
    $wynik_czy_uzupelniono_dane = $zapytanie_czy_uzupelniono_dane->get_result();
    $wynik_czy_uzupelniono_dane = $wynik_czy_uzupelniono_dane->fetch_array();
        
    if($wynik_czy_uzupelniono_dane['czy_uzupelniono_dane'] == 1){
        header('Location: lek-strona-glowna.php');
        exit();
    }
    else{}
}
else{
    header('Location: ../logowanie.php');
    exit();
}
?>
<?php
if(isset($_POST['lek_uzupelnij_dane'])){
    $imie = szyfruj(zabezpieczenia($_POST['imie']));
    $nazwisko = szyfruj(zabezpieczenia($_POST['nazwisko']));
    $plec = szyfruj(zabezpieczenia($_POST['plec']));
    $pesel = szyfruj(zabezpieczenia($_POST['pesel']));
    $data_urodzenia = szyfruj(zabezpieczenia($_POST['data_urodzenia']));
    $miejsce_urodzenia = szyfruj(zabezpieczenia($_POST['miejsce_urodzenia']));
    $npwz = szyfruj(zabezpieczenia($_POST['npwz']));
    if($_POST['specjalnosc'] == 'nie_wybrano'){
        $_SESSION['brak_specjalnosci'] = 1;
        header('Location: lek-uzupelnij-dane.php');
        exit();
    }
    else{
        $specjalnosc = szyfruj(zabezpieczenia($_POST['specjalnosc']));
    }
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

    $uzupelniono_dane = 1;
    $aktualizuj_czy_uzupelniono_dane = $link->prepare("UPDATE uzytkownik SET czy_uzupelniono_dane=? WHERE id_uzytkownika=?");
    $aktualizuj_czy_uzupelniono_dane->bind_param('ii', $uzupelniono_dane, $_SESSION['zalogowany_id']);
    $aktualizuj_czy_uzupelniono_dane->execute();
    if($aktualizuj_czy_uzupelniono_dane){
        $rola_uzytkownika = 'LK';
        $dodaj_lekarza = $link->prepare("INSERT INTO lekarz(id_lekarza,id_typu_uzytkownika,imie,nazwisko,plec,pesel,data_urodzenia,miejsce_urodzenia,
        npwz,email,telefon,ulica,nr_domu,nr_mieszkania,miasto,kod_pocztowy,wojewodztwo,specjalnosc) 
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        $dodaj_lekarza->bind_param('isssssssssssssssss',$_SESSION['zalogowany_id'],$rola_uzytkownika,$imie,$nazwisko,$plec,$pesel,$data_urodzenia,
        $miejsce_urodzenia,$npwz,$email,$tel,$ulica,$nr_domu,$nr_mieszkania,$miasto,$kod_pocztowy,$wojewodztwo,$specjalnosc);

        $dodaj_lekarza->execute();
        if(!$dodaj_lekarza){
            $nie_uzupelniono_danych = 0;
            $przywroc_nie_uzupelniono_danych = $link->prepare("UPDATE uzytkownik SET czy_uzupelniono_dane=? WHERE id_uzytkownika=?");
            $przywroc_nie_uzupelniono_danych->bind_param('ii', $nie_uzupelniono_danych, $_SESSION['zalogowany_id']);
            $przywroc_nie_uzupelniono_danych->execute();
            if(!$przywroc_nie_uzupelniono_danych){
                $_SESSION['blad_uzupelnienia_danych'] = 1;
                header('Location: lek-strona-glowna.php');
                exit();
            }
            else{
                $_SESSION['ponowne_uzupelnienie_danych'] = 1;
                header('Location: lek-uzupelnij-dane.php');
                exit();
            }
        }
        else{
            $_SESSION['sukces_uzupelnienia_danych'] = 1;
            header('Location: lek-czy-nowe-haslo.php');
            exit();
        }
    }
    else{
        $_SESSION['ponowne_uzupelnienie_danych'] = 1;
        header('Location: lek-uzupelnij-dane.php');
        exit();
    }
}
else{
    header('Location: lek-strona-glowna.php');
    exit();
}
?>
<?php
$link->close();
?>