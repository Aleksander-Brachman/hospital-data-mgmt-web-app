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
if(isset($_POST['lek_wypisz_pacjenta'])){
    if(isset($_GET['id_pacjenta'])){
        $zapytanie_pobyt = $link->prepare("SELECT id_pacjenta, id_pobytu, historia_pobytu FROM pobyt WHERE id_pacjenta=? AND id_lekarza=? AND data_zakonczenia_pobytu IS NULL");
        $zapytanie_pobyt->bind_param('ii', $_GET['id_pacjenta'], $_SESSION['zalogowany_id']);
        $zapytanie_pobyt->execute();
        if(!$zapytanie_pobyt){
            $_SESSION['blad_wypis_pacjenta'] = 1;
            header("Location: lek-wypisz-pacjenta.php?id_pacjenta=".$_GET['id_pacjenta']."");
            exit();
        }
        $wynik_pobyt=$zapytanie_pobyt->get_result();
        if(mysqli_num_rows($wynik_pobyt) == 0){
            header('Location: lek-strona-glowna.php');
            exit();
        }

        $id_pacjenta = $_GET['id_pacjenta'];
        $wynik_pobyt = $wynik_pobyt->fetch_array();
        if(!$wynik_pobyt){
            $_SESSION['blad_wypis_pacjenta']=1;
            header('Location: lek-wypisz-pacjenta.php?id_pacjenta='.$id_pacjenta.'');
            exit();
        }

        $id_pacjenta = $wynik_pobyt['id_pacjenta'];
        $id_pobytu = $wynik_pobyt['id_pobytu'];
        $historia_pobytu = deszyfruj($wynik_pobyt['historia_pobytu']);

        if(!empty($_POST['podsumowanie_pobytu'])){
            $podsumowanie_pobytu = zabezpieczenia($_POST['podsumowanie_pobytu']);
        }
        else{
            $podsumowanie_pobytu = 'Brak podsumowania';
        }

        $data_zakonczenia_pobytu = date_create()->format('Y-m-d H:i:s');
        $zakoncznie_historii_pobytu = nl2br("------ ZAKOŃCZENIE POBYTU NR: " . $id_pobytu . ", DATA ZAKOŃCZENIA: " . $data_zakonczenia_pobytu . " ------\n
                                    Nr ID Pacjenta: ".$id_pacjenta.", Nr ID Lekarza: ".$_SESSION['zalogowany_id']."\n
                                    Podsumowanie pobytu: ".$podsumowanie_pobytu."\n
                                    ------ KONIEC WPISU ------\n");
        $zakonczona_historia_pobytu = $historia_pobytu . $zakoncznie_historii_pobytu;
        $zakonczona_historia_pobytu = szyfruj($zakonczona_historia_pobytu);
        $data_zakonczenia_pobytu = szyfruj($data_zakonczenia_pobytu);
        $podsumowanie_pobytu = szyfruj($podsumowanie_pobytu);

        $wygenerowano_akt_wypisu=1;

        $zapytanie_historia_pacjenta = $link->prepare("SELECT historia_pacjenta FROM pacjent WHERE id_pacjenta=? AND id_lekarza=?");
        $zapytanie_historia_pacjenta->bind_param('ii', $id_pacjenta, $_SESSION['zalogowany_id']);
        $zapytanie_historia_pacjenta->execute();
        $wynik_historia_pacjenta = $zapytanie_historia_pacjenta->get_result();
        $wynik_historia_pacjenta = $wynik_historia_pacjenta->fetch_array();
        if(!$wynik_historia_pacjenta){
            $_SESSION['blad_wypis_pacjenta']=1;
            header('Location: lek-wypisz-pacjenta.php?id_pacjenta='.$id_pacjenta.'');
            exit();
        }
        $sprawdz_historie_pacjenta = $wynik_historia_pacjenta['historia_pacjenta'];

        if($sprawdz_historie_pacjenta == NULL){
            $zakoncz_pobyt = $link->prepare("UPDATE pobyt SET data_zakonczenia_pobytu=?, historia_pobytu=?, czy_wygenerowano_akt_wypisu=?, podsumowanie_pobytu=? WHERE id_pobytu=?");
            $zakoncz_pobyt->bind_param('ssisi', $data_zakonczenia_pobytu, $zakonczona_historia_pobytu, $wygenerowano_akt_wypisu, $podsumowanie_pobytu, $id_pobytu);
            $zakoncz_pobyt->execute();
            if(!$zakoncz_pobyt){
                $_SESSION['blad_wypis_pacjenta']=1;
                header('Location: lek-wypisz-pacjenta.php?id_pacjenta='.$id_pacjenta.'');
                exit();
            }
            else{
                $zapytanie_historia_pobytu = $link->prepare("SELECT historia_pobytu FROM pobyt WHERE id_pobytu=?");
                $zapytanie_historia_pobytu->bind_param('i', $id_pobytu);
                $zapytanie_historia_pobytu->execute();
                $wynik_historia_pobytu = $zapytanie_historia_pobytu->get_result();
                $wynik_historia_pobytu = $wynik_historia_pobytu->fetch_array();

                $pobrana_historia_pobytu = $wynik_historia_pobytu['historia_pobytu'];

                $aktualizuj_pacjenta = $link->prepare("UPDATE pacjent SET data_zakonczenia_ostatniego_pobytu=?, historia_pacjenta=?, id_lekarza=NULL, id_pobytu=NULL WHERE id_pacjenta=?");
                $aktualizuj_pacjenta->bind_param('ssi', $data_zakonczenia_pobytu, $pobrana_historia_pobytu, $id_pacjenta);
                $aktualizuj_pacjenta->execute();

                if(!$aktualizuj_pacjenta){
                    $_SESSION['blad_wypis_pacjenta'] = 1;
                    $_SESSION['wypis_id_pacjenta'] = $id_pacjenta;
                    header('Location: lek-strona-glowna.php');
                    exit();
                }
                else{
                    $_SESSION['sukces_wypis_pacjenta'] = 1;
                    $_SESSION['wypis_id_pacjenta'] = $id_pacjenta;
                    header('Location: lek-strona-glowna.php');
                    exit();
                }
            }
        }
        else{
            $zakoncz_pobyt = $link->prepare("UPDATE pobyt SET data_zakonczenia_pobytu=?, historia_pobytu=?, czy_wygenerowano_akt_wypisu=?, podsumowanie_pobytu=? WHERE id_pobytu=?");
            $zakoncz_pobyt->bind_param('ssisi', $data_zakonczenia_pobytu, $zakonczona_historia_pobytu, $wygenerowano_akt_wypisu, $podsumowanie_pobytu, $id_pobytu);
            $zakoncz_pobyt->execute();
            if(!$zakoncz_pobyt){
                $_SESSION['blad_wypis_pacjenta']=1;
                header('Location: lek-wypisz-pacjenta.php?id_pacjenta='.$id_pacjenta.'');
                exit();
            }
            else{
                $zapytanie_historia_pobytu = $link->prepare("SELECT historia_pobytu FROM pobyt WHERE id_pobytu=?");
                $zapytanie_historia_pobytu->bind_param('i', $id_pobytu);
                $zapytanie_historia_pobytu->execute();
                $wynik_historia_pobytu = $zapytanie_historia_pobytu->get_result();
                $wynik_historia_pobytu = $wynik_historia_pobytu->fetch_array();

                $pobrana_historia_pobytu = $wynik_historia_pobytu['historia_pobytu'];
                $pobrana_historia_pobytu = deszyfruj($pobrana_historia_pobytu);
                
                $historia_pacjenta = deszyfruj($sprawdz_historie_pacjenta); 

                $zaktualizowana_historia_pacjenta = $historia_pacjenta . $pobrana_historia_pobytu;
                $zaktualizowana_historia_pacjenta = szyfruj($zaktualizowana_historia_pacjenta);
                
                $aktualizuj_pacjenta = $link->prepare("UPDATE pacjent SET data_zakonczenia_ostatniego_pobytu=?, historia_pacjenta= ?, id_lekarza=NULL, id_pobytu=NULL WHERE id_pacjenta=?");
                $aktualizuj_pacjenta->bind_param('ssi', $data_zakonczenia_pobytu, $zaktualizowana_historia_pacjenta, $id_pacjenta);
                $aktualizuj_pacjenta->execute();
                if(!$aktualizuj_pacjenta){
                    $_SESSION['blad_wypis_pacjenta']=  1;
                    $_SESSION['wypis_id_pacjenta'] = $id_pacjenta;
                    header('Location: lek-strona-glowna.php');
                    exit();
                }
                else{
                    $_SESSION['sukces_wypis_pacjenta'] = 1;
                    $_SESSION['wypis_id_pacjenta'] = $id_pacjenta;
                    header('Location: lek-strona-glowna.php');
                    exit();
                }
            }
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

    