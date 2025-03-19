<?php
require('../../polacz-baza/polacz-baza-rejestracja.php');
require('../../zabezpieczenia/szyfruj-deszyfruj.php');;
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
    else{}
}
else{
    header('Location: ../logowanie.php');
    exit();
}

$sprawdzenie_pesel = mysqli_query($link,"SELECT id_pacjenta, pesel FROM pacjent");
if(isset($_POST['pesel'])){
    while($wynik_sprawdzenie_pesel = mysqli_fetch_array($sprawdzenie_pesel)){
        if(deszyfruj($wynik_sprawdzenie_pesel['pesel']) == $_POST['pesel']){
            $_SESSION['pesel_znajduje_sie_w_systemie'] = 1;
            $_SESSION['powtorzony_pesel_id_pacjenta'] = $wynik_sprawdzenie_pesel['id_pacjenta'];
            header('Location: rej-dodaj-pacjenta.php');
            exit();
        }
    }
}
?>
<?php
if(isset($_POST['rej_dodaj_pacjenta'])){
    $imie = szyfruj(zabezpieczenia($_POST['imie']));
    $nazwisko = szyfruj(zabezpieczenia($_POST['nazwisko']));
    $plec = szyfruj(zabezpieczenia($_POST['plec']));

    if(isset($_POST['pesel'])){
        $pesel = szyfruj(zabezpieczenia($_POST['pesel']));
    }
    else{
        $pesel = szyfruj('Nie podano');
    }
    
    $data_urodzenia = szyfruj(zabezpieczenia($_POST['data_urodzenia']));
    $miejsce_urodzenia = szyfruj(zabezpieczenia($_POST['miejsce_urodzenia']));

    if(isset($_POST['obywatelstwo'])){
        if(!empty($_POST['obywatelstwo'])){
            $obywatelstwo = szyfruj(zabezpieczenia($_POST['obywatelstwo']));
        }
        else{
            $obywatelstwo = szyfruj('Nie podano');
        }
    }
    else{ $obywatelstwo = szyfruj('polskie'); }

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
    
    if(!empty($_POST['email'])){
        $email = szyfruj(zabezpieczenia($_POST['email']));
    }
    else{
        $email = szyfruj('Nie podano');
    } 
    
    if(!empty($_POST['telefon'])){
        $tel = szyfruj(zabezpieczenia($_POST['telefon']));
    }
    else{
        $tel = szyfruj('Nie podano');
    } 

    $data_dodania_do_bazy = date_create()->format('Y-m-d H:i:s');

    $dodaj_pacjenta = $link->prepare("INSERT INTO pacjent(imie, nazwisko, plec, pesel, data_urodzenia, miejsce_urodzenia, email, telefon, ulica, nr_domu, nr_mieszkania, miasto, kod_pocztowy, wojewodztwo, obywatelstwo, data_dodania_do_bazy) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    $dodaj_pacjenta->bind_param('ssssssssssssssss',$imie,$nazwisko,$plec,$pesel,$data_urodzenia,
    $miejsce_urodzenia,$email,$tel,$ulica,$nr_domu,$nr_mieszkania,$miasto,$kod_pocztowy,$wojewodztwo,$obywatelstwo,$data_dodania_do_bazy);
    
    $dodaj_pacjenta->execute();
    if($dodaj_pacjenta){
        $zapytanie_id_pacjenta = mysqli_query($link,"SELECT id_pacjenta FROM pacjent ORDER BY data_dodania_do_bazy DESC LIMIT 1");
        $wynik_id_pacjenta = mysqli_fetch_array($zapytanie_id_pacjenta);
        $id_pacjenta = $wynik_id_pacjenta['id_pacjenta'];
        $_SESSION['sukces_dodaj_pacjenta'] = 1;
        $_SESSION['id_nowego_pacjenta'] = $id_pacjenta;
        header('Location: rej-lista-pacjentow.php');
        exit();
    }
    else{
        $_SESSION['blad_dodaj_pacjenta'] = 1;
        header('Location: rej-dodaj-pacjenta.php');
        exit();
    }
}
else{
    header('Location: rej-strona-glowna.php');
    exit();
}
?>
<?php
$link->close();
?>
