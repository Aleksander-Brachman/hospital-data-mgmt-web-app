<?php
require('../../polacz-baza/polacz-baza-rejestracja.php');
require('../../zabezpieczenia/szyfruj-deszyfruj.php');

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
    else{
        $zapytanie_rejestrator = $link->prepare("SELECT imie, nazwisko FROM rejestrator WHERE id_rejestratora=?");
        $zapytanie_rejestrator->bind_param('i', $_SESSION['zalogowany_id']);
        $zapytanie_rejestrator->execute();
        $wynik_rejestrator = $zapytanie_rejestrator->get_result();
        if(mysqli_num_rows($wynik_rejestrator) == 0){
            unset($_SESSION['zalogowany_rejestrator']);
            unset($_SESSION['zalogowany_id']);
            header('Location: ../logowanie.php');
            exit();
        }
        $wynik_rejestrator = $wynik_rejestrator->fetch_array();

        $imie_rejestratora = deszyfruj($wynik_rejestrator['imie']);
        $nazwisko_rejestratora = deszyfruj($wynik_rejestrator['nazwisko']);
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
    <link href='rej-style.css' rel='stylesheet'>
</head>
<body>
<main>
<header>
    <h1 class='panel'>Panel rejestracja</h1>
    <p class='zalogowal-sie'>Zalogował się rejestrator: <?php echo $imie_rejestratora?> <?php echo $nazwisko_rejestratora?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-lista-logowan'>
<p class='strona-glowna'><a href='rej-strona-glowna.php'>Wróć do strony głównej</a></p>

<div class='sortowanie-lista-logowan'>
    <label><b>Pokaż ostatnich: </b></label>
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
            if(!$zapytanie_logowania){
                ?><p><b>Błąd wczytania ostatnich logowań</b></p><?php
            }
            else{
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
                    