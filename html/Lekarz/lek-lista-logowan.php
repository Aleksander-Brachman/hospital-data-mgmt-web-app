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
        $zapytanie_lekarz = $link->prepare("SELECT imie, nazwisko FROM lekarz WHERE id_lekarza=?");
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
    }
}
else{
    header('Location: ../logowanie.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Ostatnie logowania</title>
    <link href='lek-style.css' rel='stylesheet'>
    <script type='text/javascript' src='jquery-3.7.1.min.js'></script>
</head>
<body>
<main>
<header>
    <h1 class='panel'>Panel lekarz</h1>
    <p class='zalogowal-sie'>Zalogował się lekarz: <?php echo $imie_lekarza?> <?php echo $nazwisko_lekarza?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-lista-logowan'>
<p class='strona-glowna'><a href='lek-strona-glowna.php'>Wróć do strony głównej</a></p>
<div class='sortowanie-lista-logowan'>
    <label><b>Pokaż ostatnich:</b></label>
    <form action='' method='post'>
    <select name='wybor_liczba_logowan'><option value='10_logowan'>10 logowań - domyślnie</option>
    <option value='20_logowan'>20 logowań</option>
    <option value='40_logowan'>40 logowań</option>
    </select>
    <button type='submit' name='wybrana_liczba_logowan'>Wybierz</button>
    </form>
</div>
<div class='informacje-lista-logowan'>
<h3>Lista ostatnich logowań</h3>
<p><?php             
if(isset($_POST['wybrana_liczba_logowan'])){
    if(isset($_POST['wybor_liczba_logowan'])){
        if($_POST['wybor_liczba_logowan'] == '10_logowan'){
            $kwerenda = "SELECT * FROM udane_zalogowanie WHERE id_uzytkownika=? ORDER BY id_logowania DESC LIMIT 10";
            $wybrana_ilosc = 'Ostatnie 10 logowań (domyślnie).';
        }
    elseif($_POST['wybor_liczba_logowan'] == '20_logowan'){
            $kwerenda = "SELECT * FROM udane_zalogowanie WHERE id_uzytkownika=? ORDER BY id_logowania DESC LIMIT 20";
            $wybrana_ilosc = 'Ostatnie 20 logowań.';
        }
    elseif($_POST['wybor_liczba_logowan'] == '40_logowan'){
            $kwerenda = "SELECT * FROM udane_zalogowanie WHERE id_uzytkownika=? ORDER BY id_logowania DESC LIMIT 40";
            $wybrana_ilosc = 'Ostatnie 40 logowań.';
        }
    }
}
else{
    $kwerenda = "SELECT * FROM udane_zalogowanie WHERE id_uzytkownika=? ORDER BY id_logowania DESC LIMIT 10";
    $wybrana_ilosc = 'Ostatnie 10 logowań (domyślnie).';
}
echo $wybrana_ilosc;
?>
</p>
</div>
<div class='tabela-lista'>
    <table>
        <tr>
            <th>ID Użytkownika</th>
            <th>ID Logowania</th>
            <th>Data logowania</th>
        </tr>
            <?php
            $zapytanie_logowania = $link->prepare($kwerenda);
            $zapytanie_logowania->bind_param('i', $_SESSION['zalogowany_id']);
            $zapytanie_logowania->execute();
            $wynik_logowania = $zapytanie_logowania->get_result();
            while($_wynik_logowania = $wynik_logowania->fetch_array()){
                ?>
                <tr>
                    <td><?php echo $_wynik_logowania['id_uzytkownika']?></td>
                    <td><?php echo $_wynik_logowania['id_logowania']?></td>
                    <td><?php echo deszyfruj($_wynik_logowania['data_logowania'])?></td>
                </tr>
            <?php
            }
        ?>
    </table>
</div>
</div>
</main>
</body>
</html>
<?php
$link->close();
?>
                    