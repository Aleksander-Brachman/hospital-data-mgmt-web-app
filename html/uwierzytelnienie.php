<?php
require('../polacz-baza/polacz-baza-uwierzytelnienie.php');
require('../zabezpieczenia/szyfruj-deszyfruj.php');
require('../zabezpieczenia/zabezpieczenia-formularz.php');
?>

<?php
if(isset($_POST['zaloguj'])){
    $nazwa_uzytkownika = zabezpieczenia($_POST['nazwa_uzytkownika']);
    $haslo = zabezpieczenia($_POST['haslo']);

    $zapytanie_haslo = $link->prepare("SELECT haslo FROM uzytkownik WHERE nazwa_uzytkownika=?");
    $zapytanie_haslo->bind_param('s', $nazwa_uzytkownika);
    $zapytanie_haslo->execute();
    if(!$zapytanie_haslo){
        $_SESSION['blad_proby_uwierzytelnienia'] = 1;
        header('location: logowanie.php');
        exit();
    }
    $wynik_haslo = $zapytanie_haslo->get_result();
    $wynik_haslo = $wynik_haslo->fetch_array();

    $porownaj_haslo = password_verify($haslo, $wynik_haslo['haslo']);
    
    if($porownaj_haslo){
        $data_logowania = szyfruj(date_create()->format('Y-m-d H:i:s'));
        
        $zapytanie_uzytkownik = $link->prepare("SELECT id_uzytkownika, id_typu_uzytkownika FROM uzytkownik WHERE nazwa_uzytkownika=?");
        $zapytanie_uzytkownik->bind_param('s', $nazwa_uzytkownika);
        $zapytanie_uzytkownik->execute();
        if(!$zapytanie_uzytkownik){
            $_SESSION['blad_proby_uwierzytelnienia'] = 1;
            header('location: logowanie.php');
            exit();
        }
        $wynik_uzytkownik = $zapytanie_uzytkownik->get_result();
        $wynik_uzytkownik = $wynik_uzytkownik->fetch_array();

        if($wynik_uzytkownik['id_typu_uzytkownika'] == 'LK'){
            $dodaj_logowanie = $link->prepare("INSERT INTO udane_zalogowanie(id_uzytkownika, data_logowania) VALUES (?,?)");
            $dodaj_logowanie->bind_param('is', $wynik_uzytkownik['id_uzytkownika'], $data_logowania);
            $dodaj_logowanie->execute();
            if(!$dodaj_logowanie){
                $_SESSION['blad_dodanie_daty_logowania'] = 1;
            }

            $_SESSION['sukces_logowania'] = 1;
            $_SESSION['zalogowany_id'] = $wynik_uzytkownik['id_uzytkownika'];
            header('Location: Lekarz/lek-strona-glowna.php');
            exit();    
        }
        else if($wynik_uzytkownik['id_typu_uzytkownika'] == 'RS'){
            $dodaj_logowanie = $link->prepare("INSERT INTO udane_zalogowanie(id_uzytkownika, data_logowania) VALUES (?,?)");
            $dodaj_logowanie->bind_param('is', $wynik_uzytkownik['id_uzytkownika'], $data_logowania);
            $dodaj_logowanie->execute();
            if(!$dodaj_logowanie){
                $_SESSION['blad_dodanie_daty_logowania'] = 1;
            }
            
            $_SESSION['sukces_logowania'] = 1;
            $_SESSION['zalogowany_id'] = $wynik_uzytkownik['id_uzytkownika'];
            header('Location: Rejestracja/rej-strona-glowna.php');    
            exit();
        }
        else{
            $_SESSION['blad_logowania'] = 1;
            header('location: logowanie.php');
            exit();
        }
    }
    else{
        $_SESSION['blad_logowania'] = 1;
        header('location: logowanie.php');
        exit();
    }
}
else{
    if(isset($_SESSION['zalogowany_lekarz'])){
        header('Location: Lekarz/lek-strona-glowna.php'); 
        exit();
    }
    else if(isset($_SESSION['zalogowany_rejestrator'])){
        header('Location: Rejestracja/rej-strona-glowna.php');
        exit(); 
    }  
    else{
        header('Location: logowanie.php');
        exit();
    }    
}
?>
<?php
$link->close();
?>
