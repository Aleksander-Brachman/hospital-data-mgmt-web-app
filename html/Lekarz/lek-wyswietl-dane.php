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
}
else{
    header('Location: ../logowanie.php');
    exit();
}
?>
<?php
$zapytanie_lekarz = $link->prepare("SELECT * FROM lekarz WHERE id_lekarza=?");
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
if(!$wynik_lekarz){
    header('Location: lek-strona-glowna.php');
    exit();
}
$imie = deszyfruj($wynik_lekarz['imie']);
$nazwisko = deszyfruj($wynik_lekarz['nazwisko']);
$pesel = deszyfruj($wynik_lekarz['pesel']);
$data_urodzenia = deszyfruj($wynik_lekarz['data_urodzenia']);
$miejsce_urodzenia = deszyfruj($wynik_lekarz['miejsce_urodzenia']);
if($wynik_lekarz['data_ostatniej_zmiany_danych'] != NULL){
    $data_ostatniej_zmiany_danych = deszyfruj($wynik_lekarz['data_ostatniej_zmiany_danych']);
}
else{
    $data_ostatniej_zmiany_danych = 'Brak zmian';
}
$npwz = deszyfruj($wynik_lekarz['npwz']);
$ulica = deszyfruj($wynik_lekarz['ulica']);
$nr_domu = deszyfruj($wynik_lekarz['nr_domu']);
if(!empty($wynik_lekarz['nr_mieszkania'])){
    $nr_mieszkania = deszyfruj($wynik_lekarz['nr_mieszkania']);
}
$miasto = deszyfruj($wynik_lekarz['miasto']);
$wojewodztwo = deszyfruj($wynik_lekarz['wojewodztwo']);
$kod_pocztowy = deszyfruj($wynik_lekarz['kod_pocztowy']);
$email = deszyfruj($wynik_lekarz['email']);
$tel = deszyfruj($wynik_lekarz['telefon']);
$specjalnosc = deszyfruj($wynik_lekarz['specjalnosc']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Twoje dane</title>
    <link href='lek-style.css' rel='stylesheet'>
    <script type='text/javascript' src='jquery-3.7.1.min.js'></script>
    <script>
        $(function(){
            $('#zmien_dane').click(function(){
                $('#zmien_dane').hide();
                $('input').removeAttr('disabled');            
                $('#wojewodztwo').html("<select name='wojewodztwo'><option value='nie_wybrano'>Wybierz województwo</option><option>Zachodnio-Pomorskie</option><option>Lubuskie</option><option>Dolnośląskie</option><option>Opolskie</option><option>Śląskie</option><option>Małopolskie</option><option>Podkarpackie</option><option>Świętokrzyskie</option><option>Łódzkie</option><option>Kujawsko-Pomorskie</option><option>Mazowieckie</option><option>Warmińsko-Mazurskie</option><option>Podlaskie</option><option>Lubelskie</option><option>Wielkopolskie</option><option>Pomorskie</option></select>");
                $('#anuluj_zmiane').removeAttr('hidden');
                $('#przycisk_zmien_dane').removeAttr('hidden');
                <?php if(empty($wynik_lekarz['nr_mieszkania'])){?>
                $('#nr_mieszkania').html("<input type='number' name='nr_mieszkania' autocomplete='off' maxlength='4'>");
                <?php } ?>
            });
            $('#anuluj_zmiane').click(function(){
                location.reload();
            });
        });
    </script>
</head>
<body>
<header>
    <h1 class='panel'>Panel lekarz</h1>
    <p class='zalogowal-sie'>Zalogował się lekarz: <?php echo $imie?> <?php echo $nazwisko?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-wyswietl-dane'>
<p class='strona-glowna'><a href='lek-strona-glowna.php'>Wróć do strony głównej</a></p>
<div class='informacje-wyswietl-dane'>
    <p class='ostatnia-zmiana-danych'><b>Ostatnia zmiana danych: <?php echo $data_ostatniej_zmiany_danych?></b></p>
    <div class='komunikat'>
    <?php
    if(isset($_SESSION['blad_zmiana_danych'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Nie udało się zaktualizować danych. Spróbuj ponownie.</b></p><?php
        unset($_SESSION['blad_zmiana_danych']);
    }
    ?>
    </div>
    <p>Jeżeli chcesz zmienić dane kontaktowe (adres zamieszkania, telefon, email), kliknij przycisk <i>Chcę zmienić dane</i>.<br>
    Wszystkie pola przeznaczone do edycji muszą być wypełnione. Możesz wycofać się z operacji zmiany danych, klikając przycisk <i>Anuluj</i>.<br>
    W przypadku chęci zmiany danych osobowych, skontaktuj się z Administratorem.</p>
    <button type='button' id='zmien_dane'>Chcę zmienić dane</button><button type='button' id='anuluj_zmiane' hidden>Anuluj</button>
</div>

<div class='secondary-container-wyswietl-dane'>
<div class='tabela-dane-osobowe'>
<div class='tabela'>
    <h3>Dane osobowe</h3>
    <table>
    <tr>
        <th>Imię</th>
        <th>Nazwisko</th>
        <th>PESEL</th>
        <th>Numer PWZ</th>
    </tr>
    <tr>
        <td><?php echo $imie?></td>
        <td><?php echo $nazwisko?></td>
        <td><?php echo $pesel?></td>
        <td><?php echo $npwz?></td>
    </tr>
    </table>
    <table>
    <tr>
        <th>Data urodzenia</th>
        <th>Miejsce urodzenia</th>
        <th>Specjalność</th>
    </tr>
    <tr>
        <td><?php echo $data_urodzenia?></td>
        <td><?php echo $miejsce_urodzenia?></td>
        <td><?php echo $specjalnosc?></td>
    </tr>
    </table>
</div>
</div>
<div class='tabela-dane-adresowe-kontaktowe'>
<div class='tabela'>
    <form action='lek-wpisz-zmien-dane.php' method='post'>
    <h3>Dane kontaktowe</h3>
    <table>
    <tr>
        <th>Ulica</th>
        <th>Nr domu</th>
        <th>Nr mieszkania</th>
    </tr>
    <tr>
        <td><input type='text' name='ulica' autocomplete='off' maxlength='40' value='<?php echo $ulica?>' required disabled></td>
        <td><input type='number' name='nr_domu' autocomplete='off' maxlength='4' value='<?php echo $nr_domu?>' required disabled></td>
        <td id='nr_mieszkania'><?php if(!empty($wynik_lekarz['nr_mieszkania'])){?><input type='number' name='nr_mieszkania' autocomplete='off' maxlength='4' value='<?php echo $nr_mieszkania?>' disabled><?php } ?>
        </td>  
    </tr>
    <tr>
        <th>Kod pocztowy</th>
        <th>Miasto</th>
        <th>Województwo</th>
    </tr>
    <tr>
        <td><input type='text' name='kod_pocztowy'  value='<?php echo $kod_pocztowy?>' pattern='^\d{2}-\d{3}$' autocomplete='off' maxlength='6' required disabled></td>
        <td><input type='text' name='miasto' autocomplete='off'  maxlength='40' value='<?php echo $miasto?>' required disabled></td>
        <td id='wojewodztwo'><?php echo $wojewodztwo?></td>
    </tr>
    </table>
    <table>
        <tr>
            <th>Telefon</th>
            <th>E-mail</th>
        </tr>
        <tr>
            <td><input type='tel' name='telefon'  value='<?php echo $tel?>' pattern='[0-9]{3} [0-9]{3} [0-9]{3}|[0-9]{9}' autocomplete='off' maxlength='11' required disabled></td>
            <td><input type='email' name='email'  value='<?php echo $email?>' pattern='^[a-z0-9._]+@[a-z0-9.-]+\.[a-z]{2,4}$' autocomplete='off'  maxlength='40' required disabled></td>
        </tr>
    </table>
    <button id='przycisk_zmien_dane' type='submit' name='lek_zmien_dane' hidden>Zmień dane</button>
    </form>
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
