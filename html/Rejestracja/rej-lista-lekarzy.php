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
        $zapytanie_rejestrator=$link->prepare("SELECT imie, nazwisko FROM rejestrator WHERE id_rejestratora=?");
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
    <title>Lista lekarzy</title>
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
<div class='main-container-lista-lekarzy'>
<p class='strona-glowna'><a href='rej-strona-glowna.php'>Wróć do strony głównej</a></p>
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
            $zapytanie_lekarz=mysqli_query($link, $kwerenda);
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
                    
