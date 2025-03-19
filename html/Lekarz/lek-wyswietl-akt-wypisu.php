<?php
require('../../polacz-baza/polacz-baza-lekarz.php');
require('../../zabezpieczenia/szyfruj-deszyfruj.php');
require('../../tfpdf/tfpdf.php'); 
define("_SYSTEM_TTFONTS", "/var/www/html/tfpdf/font/unifont");

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
        if(isset($_GET['id_pobytu'])){
            $wygenerowany_akt_wypisu = 1;
            $zapytanie_akt_wypisu = $link->prepare("SELECT id_pacjenta, data_zakonczenia_pobytu FROM pobyt WHERE id_pobytu=? AND id_lekarza=? AND data_zakonczenia_pobytu IS NOT NULL AND czy_wygenerowano_akt_wypisu=?");
            $zapytanie_akt_wypisu->bind_param('iii', $_GET['id_pobytu'], $_SESSION['zalogowany_id'], $wygenerowany_akt_wypisu);
            $zapytanie_akt_wypisu->execute();
            if(!$zapytanie_akt_wypisu){
                $_SESSION['blad_wyswietl_akt_wypisu'] = 1;
                $_SESSION['akt_wypisu_id_pobytu'] = $_GET['id_pobytu'];
                header('Location: lek-ostatnio-zakonczone-pobyty.php');
                exit();
            }

            $wynik_akt_wypisu=$zapytanie_akt_wypisu->get_result();
            if(mysqli_num_rows($wynik_akt_wypisu) == 0){
                $_SESSION['brak_dostepu_akt_wypisu_pacjenta'] = 1;
                $_SESSION['akt_wypisu_id_pobytu'] = $_GET['id_pobytu'];
                header('Location: lek-ostatnio-zakonczone-pobyty.php');
                exit();
            }

            $wynik_akt_wypisu = $wynik_akt_wypisu->fetch_array();
            $data_zakonczenia_pobytu = deszyfruj($wynik_akt_wypisu['data_zakonczenia_pobytu']);
            if(strtotime($data_zakonczenia_pobytu) < strtotime('-48 hours')){
                $_SESSION['wygasl_dostep_akt_wypisu_pacjenta'] = 1;
                $_SESSION['akt_wypisu_id_pobytu'] = $_GET['id_pobytu'];
                header('Location: lek-ostatnio-zakonczone-pobyty.php');
                exit();
            }

            $id_pobytu = $_GET['id_pobytu'];
            $id_pacjenta = $wynik_akt_wypisu['id_pacjenta'];
            $zapytanie_lekarz = $link->prepare("SELECT imie, nazwisko, specjalnosc FROM lekarz WHERE id_lekarza=?");
            $zapytanie_lekarz->bind_param('i', $_SESSION['zalogowany_id']);
            $zapytanie_lekarz->execute();
            $wynik_lekarz = $zapytanie_lekarz->get_result();
            if(mysqli_num_rows($wynik_lekarz) == 0){
                unset($_SESSION['zalogowany_lekarz']);
                unset($_SESSION['zalogowany_id']);
                header('Location: ../logowanie.php');
                exit();
            }

            $wynik_lekarz = $wynik_lekarz->fetch_array();
            $imie_lekarza = deszyfruj($wynik_lekarz['imie']);
            $nazwisko_lekarza = deszyfruj($wynik_lekarz['nazwisko']);
            $specjalnosc_lekarza = deszyfruj($wynik_lekarz['specjalnosc']);
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
$zapytanie_pacjent = $link->prepare("SELECT imie, nazwisko, pesel, data_urodzenia FROM pacjent WHERE id_pacjenta=?");
$zapytanie_pacjent->bind_param('i', $id_pacjenta);
$zapytanie_pacjent->execute();
$wynik_pacjent = $zapytanie_pacjent->get_result();
$wynik_pacjent = $wynik_pacjent->fetch_array();

$zapytanie_pobyt = $link->prepare("SELECT data_rozpoczecia_pobytu, id_oddzialu, id_schorzenia, podsumowanie_pobytu FROM pobyt WHERE id_pobytu=?");
$zapytanie_pobyt->bind_param('i', $id_pobytu);
$zapytanie_pobyt->execute();
$wynik_pobyt = $zapytanie_pobyt->get_result();
$wynik_pobyt = $wynik_pobyt->fetch_array();

if(!$wynik_pacjent || !$wynik_pobyt){   
    $_SESSION['blad_wyswietl_akt_wypisu'] = 1;
    $_SESSION['akt_wypisu_id_pobytu'] = $id_pobytu;
    header('Location: lek-ostatnio-zakonczone-pobyty.php');
    exit();
}

$imie = deszyfruj($wynik_pacjent['imie']);
$nazwisko = deszyfruj($wynik_pacjent['nazwisko']);
$pesel = deszyfruj($wynik_pacjent['pesel']);
$data_urodzenia = deszyfruj($wynik_pacjent['data_urodzenia']);

$data_rozpoczecia_pobytu = deszyfruj($wynik_pobyt['data_rozpoczecia_pobytu']);
$id_oddzialu = $wynik_pobyt['id_oddzialu'];
$id_schorzenia = $wynik_pobyt['id_schorzenia'];
$podsumowanie_pobytu = deszyfruj($wynik_pobyt['podsumowanie_pobytu']);

$zapytanie_oddzial_pacjenta = $link->prepare("SELECT opis_oddzialu FROM oddzial WHERE id_oddzialu=?");
$zapytanie_oddzial_pacjenta->bind_param('s', $id_oddzialu);
$zapytanie_oddzial_pacjenta->execute();
$wynik_oddzial_pacjenta = $zapytanie_oddzial_pacjenta->get_result();
$wynik_oddzial_pacjenta = $wynik_oddzial_pacjenta->fetch_array();
if(!$wynik_oddzial_pacjenta){
    $oddzial_pacjenta = 'Błąd';
}
else{
    $oddzial_pacjenta = $wynik_oddzial_pacjenta['opis_oddzialu'];
}

$zapytanie_schorzenie_pacjenta = $link->prepare("SELECT opis_schorzenia FROM schorzenie WHERE id_schorzenia=?");
$zapytanie_schorzenie_pacjenta->bind_param('s', $id_schorzenia);
$zapytanie_schorzenie_pacjenta->execute();
$wynik_schorzenie_pacjenta = $zapytanie_schorzenie_pacjenta->get_result();
$wynik_schorzenie_pacjenta = $wynik_schorzenie_pacjenta->fetch_array();
if(!$wynik_schorzenie_pacjenta){
    $schorzenie_pacjenta = 'Błąd';
}
else{
    $schorzenie_pacjenta = $wynik_schorzenie_pacjenta['opis_schorzenia'];
}


$pdf = new tFPDF(); 
      
$pdf->AddPage(); 
$pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);           
$pdf->SetFont('DejaVu','',24);
            
$pdf->Cell(55); 
$pdf->Cell(0, 20, 'Akt wypisu pacjenta'); 
$pdf->Ln(20); 
    
$pdf->SetFont('DejaVu','',18);
    
$pdf->Cell(10,30, 'Dane osobowe pacjenta');
$pdf->Cell(90);
$pdf->Cell(10,30, 'Dane lekarza prowadzącego');
$pdf->Ln(10); 

$pdf->SetFont('DejaVu','',12);
    
$pdf->Cell(10, 30, 'Nr ID Pacjenta: '.$id_pacjenta.'');
$pdf->Cell(90);
$pdf->Cell(10,30, 'Imię: '.$imie_lekarza.'');
$pdf->Ln(10); 
    
$pdf->Cell(10,30, 'Imię: '.$imie.'');
$pdf->Cell(90);
$pdf->Cell(10,30, 'Nazwisko: '.$nazwisko_lekarza.'');
$pdf->Ln(10);

$pdf->Cell(10,30, 'Nazwisko: '.$nazwisko.'');
$pdf->Cell(90);
$pdf->Cell(10,30, 'Specjalność: '.$specjalnosc_lekarza.'');  
$pdf->Ln(10); 

$pdf->Cell(10,30, 'PESEL: '.$pesel.'');
$pdf->Ln(10); 
$pdf->Cell(10,30, 'Data urodzenia: '.$data_urodzenia.'');
$pdf->Ln(20);

$pdf->SetFont('DejaVu','',18);

$pdf->Cell(10,30, 'Pobyt nr ID: '.$id_pobytu.'');
$pdf->Ln(10);

$pdf->SetFont('DejaVu','',12);

$pdf->Cell(10,30, 'Data rozpoczęcia pobytu: '.$data_rozpoczecia_pobytu.'');
$pdf->Ln(10);
$pdf->Cell(10,30, 'Oddział: '.$oddzial_pacjenta.'');
$pdf->Ln(10);
$pdf->Cell(10,30, 'Schorzenie (wg ICD-10):');
$pdf->Ln(20);
$pdf->SetFont('DejaVu','',10);
$pdf->MultiCell(0,10, $schorzenie_pacjenta);
$pdf->SetFont('DejaVu','',12);
$pdf->Cell(10,30, 'Data zakończenia pobytu: '.$data_zakonczenia_pobytu.'');
$pdf->Ln(10);
$pdf->Cell(10,30, 'Podsumowanie pobytu:');
$pdf->Ln(20);
$pdf->MultiCell(0,10, $podsumowanie_pobytu);

$pdf->Output(); 
?>
<?php
$link->close();
?>