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
?>
<?php
if(isset($_POST['lek_dodaj_pobyt'])){
    if($_POST['oddzial'] == 'nie_wybrano' || $_POST['schorzenie'] == 'nie_wybrano'){
        $_SESSION['brak_oddzial_schorzenie'] = 1;
        header('Location: lek-dodaj-pobyt.php');
        exit();
    }
    else{
        $id_pacjenta = zabezpieczenia($_POST['id_pacjenta']);
        $id_oddzialu = zabezpieczenia($_POST['oddzial']);
        $id_schorzenia = zabezpieczenia($_POST['schorzenie']);

        if(!empty($_POST['uwagi'])){
            $uwagi = zabezpieczenia($_POST['uwagi']);
        }
        else{ $uwagi = 'Brak uwag.';}

        $data_rozpoczecia_pobytu = date_create()->format('Y-m-d H:i:s');

        $znajdz_id_pobytu = mysqli_query($link, "SELECT id_pobytu FROM pobyt ORDER BY id_pobytu DESC LIMIT 1");
        if(mysqli_num_rows($znajdz_id_pobytu) == 0){
            $id_pobytu = 10001;
        }
        else{
            $wynik_id_pobytu = mysqli_fetch_array($znajdz_id_pobytu);
            $id_pobytu = $wynik_id_pobytu['id_pobytu'] + 1;
        }
        
        $historia_pobytu = nl2br("start------ POCZĄTEK POBYTU NR: " . $id_pobytu . ", DATA ROZPOCZĘCIA: " . $data_rozpoczecia_pobytu . " ------\n
                            Nr ID Pacjenta: ".$id_pacjenta.", Nr ID Lekarza: ".$_SESSION['zalogowany_id'].", Oddział: ".$id_oddzialu.", ICD-10: ".$id_schorzenia."\n
                            Początkowe uwagi: ".$uwagi."\n
                              ------ KONIEC WPISU ------\n\n");

        $historia_pobytu = szyfruj($historia_pobytu);
        $data_rozpoczecia_pobytu = szyfruj($data_rozpoczecia_pobytu);

        $dodaj_pobyt = $link->prepare("INSERT INTO pobyt(id_pobytu, id_pacjenta, id_lekarza, id_oddzialu, id_schorzenia, data_rozpoczecia_pobytu, historia_pobytu) VALUES(?,?,?,?,?,?,?)");
        $dodaj_pobyt->bind_param('iiissss',$id_pobytu, $id_pacjenta, $_SESSION['zalogowany_id'], $id_oddzialu, $id_schorzenia, $data_rozpoczecia_pobytu, $historia_pobytu);
        $dodaj_pobyt->execute();
        if(!$dodaj_pobyt){
            $_SESSION['blad_dodaj_pobyt'] = 1;
            $_SESSION['id_dodawanego_pacjenta'] = $id_pacjenta;
            header('Location: lek-dodaj-pobyt.php');
            exit();
        }
        else{
            $uzupelnij_pacjenta = $link->prepare("UPDATE pacjent SET id_pobytu=?, id_lekarza=?, data_rozpoczecia_ostatniego_pobytu=? WHERE id_pacjenta=?");
            $uzupelnij_pacjenta->bind_param('iisi', $id_pobytu, $_SESSION['zalogowany_id'], $data_rozpoczecia_pobytu, $id_pacjenta);
            $uzupelnij_pacjenta->execute();
            if($uzupelnij_pacjenta){
                $_SESSION['sukces_dodaj_pobyt'] = 1;
                $_SESSION['nowy_pacjent_id'] = $id_pacjenta;
                $_SESSION['nowy_pobyt_id'] = $id_pobytu;
                header('Location: lek-strona-glowna.php');
                exit();
            }
            else{
                $_SESSION['blad_powiazania_pacjent_pobyt'] = 1;
                $_SESSION['nowy_pacjent_id'] = $id_pacjenta;
                $_SESSION['nowy_pobyt_id'] = $id_pobytu;
                header('Location: lek-strona-glowna.php');
                exit();
            }
        }
    }
}
else{
    header('Location: lek-dodaj-pobyt.php');
    exit();
}
?>
<?php
$link->close();
?>
