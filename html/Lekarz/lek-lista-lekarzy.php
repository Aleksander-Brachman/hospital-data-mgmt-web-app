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
    <title>Lista lekarzy</title>
    <link href='lek-style.css' rel='stylesheet'>
</head>
<body>
<main>
<header>
    <h1 class='panel'>Panel lekarz</h1>
    <p class='zalogowal-sie'>Zalogował się lekarz: <?php echo $imie_lekarza?> <?php echo $nazwisko_lekarza?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-lista-lekarzy'>
<p class='strona-glowna'><a href='lek-strona-glowna.php'>Wróć do strony głównej</a></p>
<div class='sortowanie-lista-lekarzy'>
    <label><b>Sortuj listę: </b></label>
    <form action='' method='post'>
    <select name='wybor_sortowania'><option value='id_rosnaco'>Sortuj po ID (rosnąco) - domyślnie</option>
    <option value='id_malejaco'>Sortuj po ID (malejąco)</option></select>
    <button type='submit' name='wybrane_sortowanie'>Sortuj</button>
    </form>
</div>
<div class='informacje-lista-lekarzy'>
<h3>Lista lekarzy</h3>
<p><?php
if(isset($_POST['wybrane_sortowanie'])){
    if(isset($_POST['wybor_sortowania'])){
        if($_POST['wybor_sortowania'] == 'id_rosnaco'){
            $kwerenda = "SELECT id_lekarza, imie, nazwisko, specjalnosc, telefon, email FROM lekarz ORDER BY id_lekarza ASC";
            $typ_sortowania = 'Lista posortowana wg ID Lekarza rosnąco (domyślnie).';
        }
        elseif($_POST['wybor_sortowania'] == 'id_malejaco'){
            $kwerenda = "SELECT id_lekarza, imie, nazwisko, specjalnosc, telefon, email FROM lekarz ORDER BY id_lekarza DESC";
            $typ_sortowania = 'Lista posortowana wg ID Lekarza malejąco.';
        }
    }
}
else{
    $kwerenda = "SELECT id_lekarza, imie, nazwisko, specjalnosc, telefon, email FROM lekarz ORDER BY id_lekarza ASC";
    $typ_sortowania = 'Lista posortowana wg ID Lekarza rosnąco (domyślnie).';
}
echo $typ_sortowania;
?>
</p>
</div>

<div class='tabela-lista'>
    <table>
        <tr>
            <th>ID Lekarza</th>
            <th>Imię</th>
            <th>Nazwisko</th>
            <th>Specjalność</th>
            <th>Telefon</th>
            <th>E-mail</th>
        </tr>
        <?php
            $zapytanie_lekarz = mysqli_query($link, $kwerenda);
            while($wynik_lekarz = mysqli_fetch_array($zapytanie_lekarz)){
                ?>
                <tr>
                    <td><?php echo $wynik_lekarz['id_lekarza']?></td>
                    <td><?php echo deszyfruj($wynik_lekarz['imie'])?></td>
                    <td><?php echo deszyfruj($wynik_lekarz['nazwisko'])?></td>
                    <td><?php echo deszyfruj($wynik_lekarz['specjalnosc'])?></td>
                    <td><?php echo deszyfruj($wynik_lekarz['telefon'])?></td>
                    <td><?php echo deszyfruj($wynik_lekarz['email'])?></td>
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
                    
