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
    else{}
}
else{
    header('Location: ../logowanie.php');
    exit();
}

$zapytanie_rejestrator = $link->prepare("SELECT * FROM rejestrator WHERE id_rejestratora=?");
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
if(!$wynik_rejestrator){
    header('Location: rej-strona-glowna.php');
    exit();
}

$imie = deszyfruj($wynik_rejestrator['imie']);
$nazwisko = deszyfruj($wynik_rejestrator['nazwisko']);
$pesel = deszyfruj($wynik_rejestrator['pesel']);
$data_urodzenia = deszyfruj($wynik_rejestrator['data_urodzenia']);
$miejsce_urodzenia = deszyfruj($wynik_rejestrator['miejsce_urodzenia']);
if($wynik_rejestrator['data_ostatniej_zmiany_danych'] != NULL){
    $data_ostatniej_zmiany_danych = deszyfruj($wynik_rejestrator['data_ostatniej_zmiany_danych']);
}
else{
    $data_ostatniej_zmiany_danych = 'Brak zmian';
}
$ulica = deszyfruj($wynik_rejestrator['ulica']);
$nr_domu = deszyfruj($wynik_rejestrator['nr_domu']);
if(!empty($wynik_rejestrator['nr_mieszkania'])){
    $nr_mieszkania = deszyfruj($wynik_rejestrator['nr_mieszkania']);
}
$miasto = deszyfruj($wynik_rejestrator['miasto']);
$wojewodztwo = deszyfruj($wynik_rejestrator['wojewodztwo']);
$kod_pocztowy = deszyfruj($wynik_rejestrator['kod_pocztowy']);
$email = deszyfruj($wynik_rejestrator['email']);
$tel = deszyfruj($wynik_rejestrator['telefon']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Twoje dane</title>
    <link href='rej-style.css' rel='stylesheet'>
    <script type='text/javascript' src='jquery-3.7.1.min.js'></script>
    <script>
        $(function(){
            $('#zmien_dane').click(function(){
                $('#zmien_dane').hide();
                $('input').removeAttr('disabled');            
                $('#wojewodztwo').html("<select name='wojewodztwo'><option value='nie_wybrano'>Wybierz województwo</option><option>Zachodnio-Pomorskie</option><option>Lubuskie</option><option>Dolnośląskie</option><option>Opolskie</option><option>Śląskie</option><option>Małopolskie</option><option>Podkarpackie</option><option>Świętokrzyskie</option><option>Łódzkie</option><option>Kujawsko-Pomorskie</option><option>Mazowieckie</option><option>Warmińsko-Mazurskie</option><option>Podlaskie</option><option>Lubelskie</option><option>Wielkopolskie</option><option>Pomorskie</option></select>");
                $('#anuluj_zmiane').removeAttr('hidden');
                $('#przycisk_zmien_dane').removeAttr('hidden');
                <?php if(empty($wynik_rejestrator['nr_mieszkania'])){?>
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
    <h1 class='panel'>Panel rejestracja</h1>
    <p class='zalogowal-sie'>Zalogował się rejestrator: <?php echo $imie?> <?php echo $nazwisko?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-wyswietl-dane'>
<p class='strona-glowna'><a href='rej-strona-glowna.php'>Wróć do strony głównej</a></p>
<div class='informacje-wyswietl-dane'>
    <p class='ostatnia-zmiana-danych'><b>Ostatnia zmiana danych: <?php echo $data_ostatniej_zmiany_danych?></b></p>
    <p>Jeżeli chcesz zmienić dane kontaktowe (adres zamieszkania, telefon, email), kliknij przycisk <i>Chcę zmienić dane</i>.<br>
    Wszystkie pola przeznaczone do edycji muszą być wypełnione. Możesz wycofać się z operacji zmiany danych, klikając przycisk <i>Anuluj</i>.<br>
    W przypadku chęci zmiany danych osobowych, skontaktuj się z Administratorem.</p>
    <button type='button' id='zmien_dane'>Chcę zmienić dane</button><button type='button' id='anuluj_zmiane' hidden>Anuluj</button>
</div>
<div class='komunikat'>
    <?php
    if(isset($_SESSION['blad_zmiana_danych'])){
        ?><style>
        .komunikat{
            border: 1px solid black;
        }
        </style><p><b>Nie udało się zaktualizować Twoich danych. Spróbuj ponownie.</b></p><?php
        unset($_SESSION['blad_zmiana_danych']);
    }
    ?>
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
        <th class='data-urodzenia'>Data urodzenia</th>
        <th>Miejsce urodzenia</th>
    </tr>
    <tr>
        <td><?php echo $imie?></td>
        <td><?php echo $nazwisko?></td>
        <td><?php echo $pesel?></td>        
        <td><?php echo $data_urodzenia?></td>
        <td><?php echo $miejsce_urodzenia?></td>  
    </tr>
    </table>
</div>
</div>
<div class='tabela-dane-adresowe-kontaktowe'>
<div class='tabela'>
    <form action='rej-wpisz-zmien-dane.php' method='post'>
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
        <td id='nr_mieszkania'><?php if(!empty($wynik_rejestrator['nr_mieszkania'])){?><input type='number' name='nr_mieszkania' autocomplete='off' maxlength='4' value='<?php echo $nr_mieszkania?>' disabled><?php } ?>
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
            <td><input type='tel' name='telefon'  value='<?php echo $tel?>' pattern='[0-9]{3} [0-9]{3} [0-9]{3}|[0-9]{9}' maxlength='11' autocomplete='off' required disabled></td>
            <td><input type='email' name='email'  value='<?php echo $email?>' pattern='^[a-z0-9._]+@[a-z0-9.-]+\.[a-z]{2,4}$' autocomplete='off'  maxlength='40' required disabled></td>
        </tr>
    </table>
    <button id='przycisk_zmien_dane' type='submit' name='rej_zmien_dane' hidden>Zmień dane</button>
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
