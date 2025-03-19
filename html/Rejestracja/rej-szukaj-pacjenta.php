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
    else{}
}
else{
    header('Location: ../logowanie.php');
    exit();
}
?>
<?php
if(isset($_POST['szukaj'])){
    if(isset($_POST['wybor_szukaj'])){
        if($_POST['wybor_szukaj'] == 'szukaj_po_id'){
            $zapytanie_pacjent_po_id = mysqli_query($link,"SELECT id_pacjenta FROM pacjent");
            $lista_id = array();
            while($wynik_pacjent_po_id = mysqli_fetch_array($zapytanie_pacjent_po_id)){
                if($wynik_pacjent_po_id['id_pacjenta'] == $_POST['szukane_id']){
                    array_push($lista_id, $wynik_pacjent_po_id['id_pacjenta']);
                }
            }
            if(count($lista_id) == 0){
                $_SESSION['brak_pacjenta'] = 1;
                $_SESSION['szukane_id'] = $_POST['szukane_id'];
                header('Location: rej-lista-pacjentow.php');
                exit();
            }
            else{
                $_SESSION['jest_pacjent'] = 1;
                $_SESSION['wyszukany_pacjent_id'] = $lista_id;
                header('Location: rej-lista-pacjentow.php');
                exit();
            }
        }
        elseif($_POST['wybor_szukaj'] == 'szukaj_po_pesel'){
            $zapytanie_pacjent_po_pesel = mysqli_query($link,"SELECT id_pacjenta, pesel FROM pacjent");
            $lista_id = array();
            while($wynik_pacjent_po_pesel = mysqli_fetch_array($zapytanie_pacjent_po_pesel)){
                if(deszyfruj($wynik_pacjent_po_pesel['pesel']) == $_POST['szukany_pesel']){
                    array_push($lista_id, $wynik_pacjent_po_pesel['id_pacjenta']);
                }
            }
            if(count($lista_id) == 0){
                $_SESSION['brak_pacjenta'] = 1;
                $_SESSION['szukany_pesel'] = $_POST['szukany_pesel'];
                header('Location: rej-lista-pacjentow.php');
                exit();
            }
            else{
                $_SESSION['jest_pacjent'] = 1;
                $_SESSION['wyszukany_pacjent_id'] = $lista_id;
                header('Location: rej-lista-pacjentow.php');
                exit();
            }
        }
        elseif($_POST['wybor_szukaj'] == 'szukaj_po_nazwisku'){
            $zapytanie_pacjent_po_nazwisku = mysqli_query($link,"SELECT id_pacjenta, nazwisko FROM pacjent");
            $lista_id = array();
            while($wynik_pacjent_po_nazwisku = mysqli_fetch_array($zapytanie_pacjent_po_nazwisku)){
                if(deszyfruj($wynik_pacjent_po_nazwisku['nazwisko']) == $_POST['szukane_nazwisko']){
                    array_push($lista_id, $wynik_pacjent_po_nazwisku['id_pacjenta']);
                }
            }
            if(count($lista_id) == 0){
                $_SESSION['brak_pacjenta'] = 1;
                $_SESSION['szukane_nazwisko'] = $_POST['szukane_nazwisko'];
                header('Location: rej-lista-pacjentow.php');
                exit();
            }
            else{
                $_SESSION['jest_pacjent'] = 1;
                $_SESSION['wyszukany_pacjent_id'] = $lista_id;
                header('Location: rej-lista-pacjentow.php');
                exit();
            }
        }
    }
    else{
        header('Location: rej-lista-pacjentow.php');
        exit();
    }
}
else{
    header('Location: rej-lista-pacjentow.php');
    exit();
}
?>
<?php
$link->close();
?>