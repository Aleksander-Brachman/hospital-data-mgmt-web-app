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
        
    if($wynik_czy_uzupelniono_dane['czy_uzupelniono_dane'] == 0){
        header('Location: lek-uzupelnij-dane.php');
        exit();
    }
}
else{
    header('Location: ../logowanie.php');
    exit();
}
if(isset($_POST['lek_aktualizuj_pobyt'])){
    if(isset($_GET['id_pobytu'])){
        $zapytanie_pobyt = $link->prepare("SELECT id_pobytu, id_pacjenta, id_oddzialu, id_schorzenia, historia_pobytu FROM pobyt WHERE id_pobytu=? AND id_lekarza=? AND data_zakonczenia_pobytu IS NULL");
        $zapytanie_pobyt->bind_param('ii', $_GET['id_pobytu'], $_SESSION['zalogowany_id']);
        $zapytanie_pobyt->execute();
        if(!$zapytanie_pobyt){
            $_SESSION['blad_aktualizacji_pobytu'] = 1;
            header("Location: lek-wyswietl-pobyt.php?id_pobytu=".$_GET['id_pobytu']."");
            exit();
        }
        $wynik_pobyt = $zapytanie_pobyt->get_result();
        if(mysqli_num_rows($wynik_pobyt)==0){
            header('Location: lek-strona-glowna.php');
            exit();
        }

        $wynik_pobyt = $wynik_pobyt->fetch_array();
        $id_pobytu = $wynik_pobyt['id_pobytu'];
        $id_pacjenta = $wynik_pobyt['id_pacjenta'];
        $id_oddzialu = $wynik_pobyt['id_oddzialu'];
        $id_schorzenia = $wynik_pobyt['id_schorzenia'];
        $historia_pobytu = deszyfruj($wynik_pobyt['historia_pobytu']);

        if(isset($_POST['oddzial'])){
            if($_POST['oddzial'] == 'nie_wybrano'){
                $_SESSION['brak_oddzial_schorzenie'] = 1;
                header('Location: lek-wyswietl-pobyt.php?id_pobytu='.$id_pobytu.'');
                exit();
            }
            else{
                $nowy_oddzial = "Zmieniono oddział: ".$_POST['oddzial']."";
                $nowe_id_oddzialu = zabezpieczenia($_POST['oddzial']);
            }
        }
        else{
            $nowy_oddzial = "Brak zmiany oddziału.";
            $nowe_id_oddzialu = $id_oddzialu;
        }

        if(isset($_POST['schorzenie'])){
            if($_POST['schorzenie'] == 'nie_wybrano'){
                $_SESSION['brak_oddzial_schorzenie'] = 1;
                header('Location: lek-wyswietl-pobyt.php?id_pobytu='.$id_pobytu.'');
                exit();
            }
            else{
                $nowe_schorzenie = 'Zmieniono rodzaj schorzenia: '.$_POST['schorzenie'].'';
                $nowe_id_schorzenia = zabezpieczenia($_POST['schorzenie']);
            }
        }
        else{
            $nowe_schorzenie = 'Brak zmiany schorzenia.';
            $nowe_id_schorzenia = $id_schorzenia;
        }

        if(!empty($_POST['nowe_informacje'])){
            $info = zabezpieczenia($_POST['nowe_informacje']);
        }
        else{ 
            $info = 'Brak nowych informacji.';
        }

        $data_aktualizacji_pobytu = date_create()->format('Y-m-d H:i:s');

        $aktualizacja_historii_pobytu = nl2br("------ AKTUALIZACJA POBYTU NR: " . $id_pobytu . ", DATA AKTUALIZACJI: " . $data_aktualizacji_pobytu . " ------\n
                        Nr ID Pacjenta: ".$id_pacjenta.", Nr ID Lekarza: ".$_SESSION['zalogowany_id'].", Oddział: ". $nowe_id_oddzialu .", ICD-10: ". $nowe_id_schorzenia ."\n
                        Nowe informacje: ".$info."\n
                        ".$nowe_schorzenie."\n
                        ".$nowy_oddzial."\n
                        ------ KONIEC WPISU ------\n\n");

        $zaktualizowana_historia_pobytu = $historia_pobytu . $aktualizacja_historii_pobytu;
        $zaktualizowana_historia_pobytu = szyfruj($zaktualizowana_historia_pobytu);
        $data_aktualizacji_pobytu = szyfruj($data_aktualizacji_pobytu);

        $aktualizuj_pobyt = $link->prepare("UPDATE pobyt SET id_oddzialu=?, id_schorzenia=?, data_ostatniej_zmiany_danych_pobytu=?, historia_pobytu=? WHERE id_pobytu=?");
        $aktualizuj_pobyt->bind_param('ssssi',$nowe_id_oddzialu,$nowe_id_schorzenia,$data_aktualizacji_pobytu,$zaktualizowana_historia_pobytu,$id_pobytu);
        $aktualizuj_pobyt->execute();
        if($aktualizuj_pobyt){
            $_SESSION['sukces_aktualizacja_pobytu'] = 1;
            $_SESSION['aktualizacja_id_pobytu'] = $id_pobytu;
            $_SESSION['aktualizacja_id_pacjenta'] = $id_pacjenta;
            header('Location: lek-strona-glowna.php');
            exit();
        }
        else{
            $_SESSION['blad_aktualizacji_pobytu'] = 1;
            header('Location: lek-wyswietl-pobyt.php?id_pobytu='.$id_pobytu.'');
            exit();
        }          
    }
    else{
        header('Location: lek-strona-glowna.php');
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