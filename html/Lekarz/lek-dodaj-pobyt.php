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
        $zapytanie_oddzial = mysqli_query($link,"SELECT * FROM oddzial ORDER BY opis_oddzialu");
        $zapytanie_schorzenie = mysqli_query($link,"SELECT * FROM schorzenie ORDER BY id_schorzenia");
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
    <title>Dodaj pobyt</title>
    <link href='lek-style.css' rel='stylesheet'>
</head>
<body>
<header>
    <h1 class='panel'>Panel lekarz</h1>
    <p class='zalogowal-sie'>Zalogował się lekarz: <?php echo $imie_lekarza?> <?php echo $nazwisko_lekarza?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-dodaj-pobyt'>
<p class='strona-glowna'><a href='lek-strona-glowna.php'>Wróć do strony głównej</a></p>
<div class='komunikat'>
    <?php 
    if(isset($_SESSION['blad_wczytania_danych_pacjenta'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Wystąpił błąd przy wczytywaniu danych pacjenta. Spróbuj ponownie.</b></p><?php
        unset($_SESSION['blad_wczytania_danych_pacjenta']);
    }
    if(isset($_SESSION['brak_oddzial_schorzenie'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Nie wybrałeś oddziału i/lub schorzenia przy próbie dodania pobytu. Spróbuj ponownie.</b></p><?php
        unset($_SESSION['brak_oddzial_schorzenie']);
    }
    if(isset($_SESSION['blad_dodaj_pobyt'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Wystąpił błąd przy próbie wpisania danych pobytu pacjenta o nr ID: <?php echo $_SESSION['id_dodanego_pacjenta']?>. Spróbuj ponownie.</b></p><?php
        unset($_SESSION['blad_dodaj_pobyt']);
        unset($_SESSION['id_dodawanego_pacjenta']);
    }
    ?>
</div>
<div class='szukaj-pacjenta'>
    <form action='' method='post'>
    <label><b>Wpisz numer ID pacjenta: </b></label>
    <input type='text' name='id_pacjenta' placeholder='Nr ID pacjenta' pattern='^\d*' minlength='4' maxlength='4' autocomplete='off' required>
    <button type='submit' name='znajdz_pacjenta_przez_id'>Znajdź pacjenta</button>
    </form>
</div>
<?php
if(isset($_POST['znajdz_pacjenta_przez_id'])){
    $zapytanie_pacjent = $link->prepare("SELECT id_pacjenta FROM pacjent WHERE id_pacjenta=?");
    $zapytanie_pacjent->bind_param('i', $_POST['id_pacjenta']);
    $zapytanie_pacjent->execute();
    $wynik_pacjent=$zapytanie_pacjent->get_result();
    if(mysqli_num_rows($wynik_pacjent) == 0){
        ?><div class='blad-wyszukiwania'><style>
        .blad-wyszukiwania{
            border: 1px solid black;
        }
        </style>
        <p><b>W systemie nie znajduje się pacjent o nr ID: <?php echo $_POST['id_pacjenta']?></b></p>
        </div>
        <a class='anuluj' href='lek-dodaj-pobyt.php'><button type='button'>Anuluj</button></a>
        <?php
    }
    else{
        $zapytanie_pacjent_2 = $link->prepare("SELECT id_pacjenta FROM pacjent WHERE id_pacjenta=? AND id_lekarza IS NULL");
        $zapytanie_pacjent_2->bind_param('i', $_POST['id_pacjenta']);
        $zapytanie_pacjent_2->execute();
        $wynik_pacjent_2 = $zapytanie_pacjent_2->get_result();
        if(mysqli_num_rows($wynik_pacjent_2) == 0){
            ?><div class='blad-wyszukiwania'><style>
            .blad-wyszukiwania{
                border: 1px solid black;
            }
            </style><p><b>W systemie znajduje się pacjent o nr ID: <?php echo $_POST['id_pacjenta']?>, jednakże jest ona/on aktualnie prowadzony przez innego lekarza.</b></p>
            </div>
            <a class='anuluj' href='lek-dodaj-pobyt.php'><button type='button'>Anuluj</button></a>
            <?php
        }
        else{
            $zapytanie_pacjent_3 = $link->prepare("SELECT id_pacjenta FROM pacjent WHERE id_pacjenta=? AND id_lekarza IS NULL AND data_zakonczenia_ostatniego_pobytu IS NULL");
            $zapytanie_pacjent_3->bind_param('i', $_POST['id_pacjenta']);
            $zapytanie_pacjent_3->execute();
            $wynik_pacjent_3 = $zapytanie_pacjent_3->get_result();
            if(mysqli_num_rows($wynik_pacjent_3) == 0){
                ?>
                <style>
                    .szukaj-pacjenta {
                        display: none;
                    }
                </style>
                <?php
                $zapytanie_pacjent_z_historia = $link->prepare("SELECT * FROM pacjent WHERE id_pacjenta=? AND id_lekarza IS NULL and data_zakonczenia_ostatniego_pobytu IS NOT NULL");
                $zapytanie_pacjent_z_historia->bind_param('i', $_POST['id_pacjenta']);
                $zapytanie_pacjent_z_historia->execute();
                $wynik_pacjent_z_historia = $zapytanie_pacjent_z_historia->get_result();
                $wynik_pacjent_z_historia = $wynik_pacjent_z_historia->fetch_array();
                if(!$wynik_pacjent_z_historia){
                    $_SESSION['blad_wczytania_danych_pacjenta'] = 1;
                    header('Location: lek-dodaj-pobyt.php');
                    exit();
                }
                ?>
                <div class='formularz-dodaj-pobyt'>
                <a class='anuluj' href='lek-dodaj-pobyt.php'><button>Anuluj dodawanie</button></a>
                <div class='dane-pacjenta'>
                <h3>Dane pacjenta o nr ID: <?php echo $_POST['id_pacjenta']?></h3>
                <label>Imię: </label><b><?php echo deszyfruj($wynik_pacjent_z_historia['imie'])?></b><br>
                <label>Nazwisko: </label><b><?php echo deszyfruj($wynik_pacjent_z_historia['nazwisko'])?></b><br>
                <label>PESEL: </label><b><?php echo deszyfruj($wynik_pacjent_z_historia['pesel'])?></b><br>
                <label>Data urodzenia: </label><b><?php echo deszyfruj($wynik_pacjent_z_historia['data_urodzenia'])?></b><br>
                <label>Historia pacjenta: </label><b>Dostępna</b>
                </div>
                <div class='dodaj-dane-pobytu'>
                <h3>Dołącz dane związane z pobytem</h3>
                
                <form action='lek-wpisz-nowy-pobyt-2.php' method='post'>
                <label>ID Pacjenta: </label><input type='text' name='id_pacjenta' value='<?php echo $_POST['id_pacjenta']?>' readonly>
                <label>Oddział: </label><select  name='oddzial'><option value='nie_wybrano'>Wybierz oddział</option><?php
                while($wynik_oddzial = mysqli_fetch_assoc($zapytanie_oddzial)){?>
                <option value='<?php echo $wynik_oddzial['id_oddzialu']?>'><?php echo $wynik_oddzial['opis_oddzialu']?></option>
                <?php } ?></select>
                <label>Rodzaj schorzenia: </label><select name='schorzenie'><option value='nie_wybrano'>Wybierz schorzenie</option>
                <?php while($wynik_schorzenie = mysqli_fetch_assoc($zapytanie_schorzenie)){?>
                <option value='<?php echo $wynik_schorzenie['id_schorzenia']?>'><?php echo $wynik_schorzenie['id_schorzenia']?> - <?php echo $wynik_schorzenie['opis_schorzenia']?></option>
                <?php } ?></select>
                <label>Początkowe uwagi: </label><textarea name='uwagi' rows='6' cols='100' autocomplete='off'></textarea>
                </div>
                <button type='submit' name='lek_dodaj_pobyt_pacjenta_z_historia'>Dodaj pobyt</button>
                </form>
                </div>
                <?php
            }
            else{
                ?>
                <style>
                    .szukaj-pacjenta {
                        display: none;
                    }
                </style>
                <?php
                $zapytanie_pacjent_bez_historii = $link->prepare("SELECT * FROM pacjent WHERE id_pacjenta=? AND id_lekarza IS NULL and data_zakonczenia_ostatniego_pobytu IS NULL");
                $zapytanie_pacjent_bez_historii->bind_param('i', $_POST['id_pacjenta']);
                $zapytanie_pacjent_bez_historii->execute();
                $wynik_pacjent_bez_historii = $zapytanie_pacjent_bez_historii->get_result();
                $wynik_pacjent_bez_historii = $wynik_pacjent_bez_historii->fetch_array();
                if(!$wynik_pacjent_bez_historii){
                    $_SESSION['blad_wczytania_danych_pacjenta'] = 1;
                    header('Location: lek-dodaj-pobyt.php');
                    exit();
                }
                ?>
                <div class='formularz-dodaj-pobyt'>
                <a class='anuluj' href='lek-dodaj-pobyt.php'><button>Anuluj dodawanie</button></a>
                <div class='dane-pacjenta'>
                <h3>Dane pacjenta o nr ID: <?php echo $_POST['id_pacjenta']?></h3>

                <label>Imię: </label><b><?php echo deszyfruj($wynik_pacjent_bez_historii['imie'])?></b><br>
                <label>Nazwisko: </label><b><?php echo deszyfruj($wynik_pacjent_bez_historii['nazwisko'])?></b><br>
                <label>PESEL: </label><b><?php echo deszyfruj($wynik_pacjent_bez_historii['pesel'])?></b><br>
                <label>Data urodzenia: </label><b><?php echo deszyfruj($wynik_pacjent_bez_historii['data_urodzenia'])?></b><br>
                <label>Historia pacjenta: </label><b>Brak w systemie</b>
                </div>
                <div class='dodaj-dane-pobytu'>
                <h3>Dołącz dane związane z pobytem</h3>
                <form action='lek-wpisz-nowy-pobyt.php' method='post'>
                <label>ID Pacjenta: </label><input type='text' name='id_pacjenta' value='<?php echo $_POST['id_pacjenta']?>' readonly>
                <label>Oddział: </label><select name='oddzial'><option value='nie_wybrano'>Wybierz oddział</option><?php
                while($wynik_oddzial = mysqli_fetch_assoc($zapytanie_oddzial)){?>
                <option value='<?php echo $wynik_oddzial['id_oddzialu']?>'><?php echo $wynik_oddzial['opis_oddzialu']?></option>
                <?php } ?></select>
                <label>Rodzaj schorzenia: </label><select name='schorzenie'><option value='nie_wybrano'>Wybierz schorzenie</option>
                <?php while($wynik_schorzenie = mysqli_fetch_assoc($zapytanie_schorzenie)){?>
                <option value='<?php echo $wynik_schorzenie['id_schorzenia']?>'><?php echo $wynik_schorzenie['id_schorzenia']?> - <?php echo $wynik_schorzenie['opis_schorzenia']?></option>
                <?php } ?></select>
                <label>Początkowe uwagi: </label><textarea name='uwagi' rows='6' cols='100' autocomplete='off'></textarea>
                </div>
                <button type='submit' name='lek_dodaj_pobyt'>Dodaj pobyt</button>
                </form>
                </div>
                <?php
            }
        }
    }
}
?>
</div>
</main>
</body>
</html>
<?php
$link->close();
?>
