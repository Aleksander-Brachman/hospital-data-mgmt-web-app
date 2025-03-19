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
        if(isset($_GET['id_pobytu'])){
            $zapytanie_pobyt = $link->prepare("SELECT id_pobytu, id_pacjenta FROM pobyt WHERE id_pobytu=? AND id_lekarza=? AND data_zakonczenia_pobytu IS NULL");
            $zapytanie_pobyt->bind_param('ii', $_GET['id_pobytu'], $_SESSION['zalogowany_id']);
            $zapytanie_pobyt->execute();
            $wynik_pobyt = $zapytanie_pobyt->get_result();
            if(mysqli_num_rows($wynik_pobyt) == 0){
                $_SESSION['brak_dostepu_pobyt_pacjenta'] = 1;
                $_SESSION['brak_dostepu_id_pobytu'] = $_GET['id_pobytu'];
                header('Location: lek-strona-glowna.php');
                exit();
            }
            else{
                $wynik_pobyt = $wynik_pobyt->fetch_array();
                $id_pobytu = $wynik_pobyt['id_pobytu'];
                $id_pacjenta = $wynik_pobyt['id_pacjenta'];

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
$zapytanie_oddzial = mysqli_query($link,"SELECT * FROM oddzial ORDER BY opis_oddzialu");
$zapytanie_schorzenie = mysqli_query($link,"SELECT * FROM schorzenie ORDER BY id_schorzenia");

$zapytanie_pacjent = $link->prepare("SELECT * from pacjent WHERE id_pacjenta=? AND id_pobytu=? AND id_lekarza=?");
$zapytanie_pacjent->bind_param('iii', $id_pacjenta, $id_pobytu, $_SESSION['zalogowany_id']);
$zapytanie_pacjent->execute();
if(!$zapytanie_pacjent){
    header('Location: lek-strona-glowna.php');
    exit();
}
$wynik_pacjent = $zapytanie_pacjent->get_result();
$wynik_pacjent = $wynik_pacjent->fetch_array();
if(!$wynik_pacjent){
    header('Location: lek-strona-glowna.php');
    exit();
}

$imie = deszyfruj($wynik_pacjent['imie']);
$nazwisko = deszyfruj($wynik_pacjent['nazwisko']);
$id_lekarza = $wynik_pacjent['id_lekarza'];

$zapytanie_pobyt_ = $link->prepare("SELECT * FROM pobyt WHERE id_pobytu=? AND id_pacjenta=? AND id_lekarza=? AND data_zakonczenia_pobytu IS NULL");
$zapytanie_pobyt_->bind_param('iii', $id_pobytu, $id_pacjenta, $_SESSION['zalogowany_id']);
$zapytanie_pobyt_->execute();
if(!$zapytanie_pobyt_){
    header('Location: lek-strona-glowna.php');
    exit();
}
$wynik_pobyt_ = $zapytanie_pobyt_->get_result();
$wynik_pobyt_ = $wynik_pobyt_->fetch_array();
if(!$wynik_pobyt_){
    header('Location: lek-strona-glowna.php');
    exit();
}
$historia_pobytu = deszyfruj($wynik_pobyt_['historia_pobytu']);
$id_schorzenia = $wynik_pobyt_['id_schorzenia'];
$id_oddzialu = $wynik_pobyt_['id_oddzialu'];
$data_rozpoczecia_pobytu = deszyfruj($wynik_pobyt_['data_rozpoczecia_pobytu']);
$data_ostatniej_zmiany_danych_pobytu = $wynik_pobyt_['data_ostatniej_zmiany_danych_pobytu'];
if($data_ostatniej_zmiany_danych_pobytu == NULL){
    $data_ostatniej_zmiany_danych_pobytu = 'Brak aktualizacji informacji związanych z pobytem';
}
else{
    $data_ostatniej_zmiany_danych_pobytu = deszyfruj($wynik_pobyt_['data_ostatniej_zmiany_danych_pobytu']);
}

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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Dane pobytu</title>
    <link href='lek-style.css' rel='stylesheet'>
    <script type='text/javascript' src='jquery-3.7.1.min.js'></script>
    <script>
        function zmianaOddzialu(checkbox) { 
        if (checkbox.checked && checkbox.id === 'inny_oddzial') {
            document.getElementById('oddzial').removeAttribute('disabled');
        } else {
            document.getElementById('oddzial').setAttribute('disabled', true);
            }
        }
        function zmianaSchorzenia(checkbox) { 
        if (checkbox.checked && checkbox.id === 'inne_schorzenie') {
            document.getElementById('schorzenie').removeAttribute('disabled');
        } else {
            document.getElementById('schorzenie').setAttribute('disabled', true);
            }
        }
        $(function(){
            $('#aktualizuj_pobyt').click(function(){
                $('#aktualizuj_pobyt_formularz').removeAttr('hidden');
                $('#anuluj_aktualizacje').removeAttr('hidden');
                $('#aktualizuj_pobyt').hide();
            });
            $('#anuluj_aktualizacje').click(function(){
                location.reload();
            });
        });
    </script>
</head>
<body>
<header>
    <h1 class='panel'>Panel lekarz</h1>
    <p class='zalogowal-sie'>Zalogował się lekarz: <?php echo $imie_lekarza?> <?php echo $nazwisko_lekarza?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-wyswietl-pobyt-pacjenta'>
<p class='strona-glowna'><a href='lek-strona-glowna.php'>Wróć do strony głównej</a></p>
<div class='informacje-wyswietl-pobyt-pacjenta'>
    <p class='ostatnia-zmiana-danych-pobytu'><b>Pobyt nr ID: <?php echo $id_pobytu?>. Ostatnia zmiana danych pobytu: <?php echo $data_ostatniej_zmiany_danych_pobytu?></b></p>
    <div class='komunikat'>
    <?php
    if(isset($_SESSION['blad_aktualizacji_pobytu'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Nie udało się zaktualizować danych pobytu. Spróbuj ponownie.</b></p><?php
        unset($_SESSION['blad_aktualizacji_pobytu']);
    }
    if(isset($_SESSION['brak_oddzial_schorzenie'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Zadeklarowałeś zmianę oddziału i/lub schorzenia, ale nie wybrałeś oddziału/schorzenia z listy. Spróbuj ponownie.</b></p><?php
        unset($_SESSION['brak_oddzial_schorzenie']);
    }
    ?>
    </div>
    <p>
    Jeżeli chcesz zaktualizować informacje związane z pobytem Pacjenta, kliknij przycisk <i>Aktualizuj informacje o pobycie</i>.<br>
    Możesz anulować dodawanie nowych informacji, klikając przycisk <i>Anuluj</i>.<br>
    Jeżeli chcesz wypisać pacjenta <a href='lek-wypisz-pacjenta.php?id_pacjenta=<?php echo $id_pacjenta?>'>kliknij tutaj</a>
    </p>
</div>
<div class='secondary-container-wyswietl-pobyt-pacjenta'>
<div class='secondary-container-left-wyswietl-pobyt-pacjenta'>
<div class='tabela-dane-pobytu-pacjenta'>
<div class='tabela'>
    <h3>Dane związane z pobytem</h3>
    <table>
    <tr>
        <th>ID Pobytu</th>
        <th>ID Pacjenta</th>
        <th>Imię</th> 
        <th>Nazwisko</th>
    </tr>
    <tr>
        <td><?php echo $id_pobytu?></td>
        <td><?php echo $id_pacjenta?></td>
        <td><?php echo $imie?></td>
        <td><?php echo $nazwisko?></td>

    </tr>
    <tr>
        <th>Data rozp. pobytu</th>
        <th>ID Lekarza</th>
        <th>ID Oddziału</th>
        <th>ID Schorzenia</th>
    </tr>
    <tr>
        <td><?php echo $data_rozpoczecia_pobytu?></td>
        <td><?php echo $id_lekarza?></td>  
        <td title='<?php echo $oddzial_pacjenta?>'><?php echo $id_oddzialu?></td>
        <td title='<?php echo $schorzenie_pacjenta?>'><?php echo $id_schorzenia?></td>
    </tr>
    </table>
</div>
</div>
<p><button type='button' id='aktualizuj_pobyt'>Aktualizuj informacje o pobycie</button><button type='button' id='anuluj_aktualizacje' hidden>Anuluj</button></p>
<div class='formularz-aktualizuj-pobyt-pacjenta' id='aktualizuj_pobyt_formularz' hidden>
    <h3>Aktualizuj informacje związane z pobytem pacjenta</h3>
    <form action='lek-wpisz-aktualizacje-pobytu.php?id_pobytu=<?php echo $id_pobytu?>' method='post'>
    <div class='checkbox-pobyt'>
    <input type='checkbox' id='inny_oddzial' onclick='zmianaOddzialu(this)' ><label>Chcę zmienić oddział pacjenta</label>
    </div>
    <label>Oddział:</label><select id='oddzial' name='oddzial' disabled><option value='nie_wybrano'>Wybierz oddział</option><?php
                while($wynik_oddzial = mysqli_fetch_assoc($zapytanie_oddzial)){?>
                <option value='<?php echo $wynik_oddzial['id_oddzialu']?>'><?php echo $wynik_oddzial['opis_oddzialu']?></option>
    <?php } ?></select>
    <div class='checkbox-pobyt'>
    <input type='checkbox' id='inne_schorzenie' onclick='zmianaSchorzenia(this)' ><label>Chcę zmienić schorzenie pacjenta</label>
    </div>
    <label>Rodzaj schorzenia:</label><select id='schorzenie' name='schorzenie' disabled><option value='nie_wybrano'>Wybierz schorzenie</option>
                <?php while($wynik_schorzenie = mysqli_fetch_assoc($zapytanie_schorzenie)){?>
                <option value='<?php echo $wynik_schorzenie['id_schorzenia']?>'><?php echo $wynik_schorzenie['id_schorzenia']?> - <?php echo $wynik_schorzenie['opis_schorzenia']?></option>
    <?php } ?></select>
    <label>Dodaj nowe informacje, uwagi:</label><textarea name='nowe_informacje' rows='8' cols='100' autocomplete='off'></textarea>
    <div class='button-aktualizuj-pobyt'>
    <button type='submit' name='lek_aktualizuj_pobyt'>Aktualizuj pobyt</button>
    </div>
    </form>
</div>
</div>
<div class='secondary-container-right-wyswietl-pobyt-pacjenta'>
<div class='widok-historia-pobytu-pacjenta'> 
    <h3>Historia pobytu nr ID: <?php echo $id_pobytu?></h3>
    <span><?php echo substr($historia_pobytu, 5) ?></span>
</div>
</div>
</div>
</div>
</main>
</body>
</html> 
<?php
$link->close();
?>

