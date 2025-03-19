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
        if(isset($_GET['id_pacjenta'])){
            $zapytanie_pacjent = $link->prepare("SELECT * FROM pacjent WHERE id_pacjenta=? AND id_lekarza=?");
            $zapytanie_pacjent->bind_param('ii', $_GET['id_pacjenta'], $_SESSION['zalogowany_id']);
            $zapytanie_pacjent->execute();
            $wynik_pacjent_ = $zapytanie_pacjent->get_result();
            if(mysqli_num_rows($wynik_pacjent_) == 0){
                $_SESSION['brak_dostepu_dane_pacjenta'] = 1;
                $_SESSION['brak_dostepu_id_pacjenta'] = $_GET['id_pacjenta'];
                header('Location: lek-strona-glowna.php');
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
$wynik_pacjent = $wynik_pacjent_->fetch_array();
if(!$wynik_pacjent){
    header('Location: lek-strona-glowna.php');
    exit();
}
$id_pacjenta = $wynik_pacjent['id_pacjenta'];
$id_lekarza = $wynik_pacjent['id_lekarza'];
$id_pobytu = $wynik_pacjent['id_pobytu'];
$data_dodania_do_bazy = $wynik_pacjent['data_dodania_do_bazy'];
if($wynik_pacjent['data_ostatniej_zmiany_danych'] != NULL){
    $data_ostatniej_zmiany_danych = deszyfruj($wynik_pacjent['data_ostatniej_zmiany_danych']);
}
else{
    $data_ostatniej_zmiany_danych = 'Brak zmian';
}
$imie = deszyfruj($wynik_pacjent['imie']);
$nazwisko = deszyfruj($wynik_pacjent['nazwisko']);
$pesel = deszyfruj($wynik_pacjent['pesel']);
$obywatelstwo = deszyfruj($wynik_pacjent['obywatelstwo']);
$data_urodzenia = deszyfruj($wynik_pacjent['data_urodzenia']);
$miejsce_urodzenia = deszyfruj($wynik_pacjent['miejsce_urodzenia']);
$ulica = deszyfruj($wynik_pacjent['ulica']);
$nr_domu = deszyfruj($wynik_pacjent['nr_domu']);
if(!empty($wynik_pacjent['nr_mieszkania'])){
    $nr_mieszkania = deszyfruj($wynik_pacjent['nr_mieszkania']);
}
if($wynik_pacjent['historia_pacjenta'] != NULL){
    $historia_pacjenta = deszyfruj($wynik_pacjent['historia_pacjenta']);
    $lista_historia_pacjenta = explode("start", $historia_pacjenta);
}
else{ 
    $historia_pacjenta = 'Pierwszy pobyt';
}
$miasto = deszyfruj($wynik_pacjent['miasto']);
$wojewodztwo = deszyfruj($wynik_pacjent['wojewodztwo']);
$kod_pocztowy = deszyfruj($wynik_pacjent['kod_pocztowy']);
$email = deszyfruj($wynik_pacjent['email']);
$tel = deszyfruj($wynik_pacjent['telefon']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Dane pacjenta</title>
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
                <?php if(empty($wynik_pacjent['nr_mieszkania'])){?>
                $('#nr_mieszkania').html("<input type='number' name='nr_mieszkania' autocomplete='off' maxlength='4'>");
                <?php } ?>
                <?php if($tel == 'Nie podano'){
                    ?>
                    $('#tel').removeAttr('value');<?php
                }?>
                <?php if($email == 'Nie podano'){
                    ?>
                    $('#email').removeAttr('value');<?php
                } ?>
            });
            $('#anuluj_zmiane').click(function(){
                location.reload();
            });
            $('#wyswietl_historie').click(function(){
                $('.historia-pacjenta').removeAttr('hidden');
                $('.belka-historia-pacjenta').removeAttr('hidden');
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
<div class='main-container-wyswietl-dane-pacjenta'>
<p class='strona-glowna'><a href='lek-strona-glowna.php'>Wróć do strony głównej</a></p>
<div class='informacje-wyswietl-dane-pacjenta'>
    <p class='ostatnia-zmiana-danych-pacjenta'><b>Dane pacjenta nr ID: <?php echo $id_pacjenta?>. Ostatnia zmiana danych: <?php echo $data_ostatniej_zmiany_danych?></b></p>
    <div class='komunikat'>
    <?php
    if(isset($_SESSION['blad_zmiana_danych_pacjenta'])){
            ?><style>
            .komunikat{
                border: 1px solid black;
            }
            </style><p><b>Nie udało się zaktualizować danych pacjenta. Spróbuj ponownie.</b></p><?php
            unset($_SESSION['blad_zmiana_danych_pacjenta']);
    }
    ?>
    </div>
    <p>Jeżeli chcesz zmienić dane kontaktowe pacjenta (adres zamieszkania, email, telefon), kliknij przycisk <i>Chcę zmienić dane pacjenta</i>.<br>
    Możeszy wycofać się z operacji zmiany danych, klikając przycisk <i>Anuluj</i>.<br>
    Pola z gwiazdką oznaczają pola konieczne do wypełnienia. Pola do wypełnienia bez gwiazdki - jeżeli nie znasz ich wartości - pozostaw puste.</p>
    <button type='button' id='zmien_dane'>Chcę zmienić dane pacjenta</button><button type='button' id='anuluj_zmiane' hidden>Anuluj</button>
</div>

<div class='secondary-container-wyswietl-dane-pacjenta'>
<div class='secondary-container-left-wyswietl-dane-pacjenta'>
<div class='tabela-dane-osobowe-pacjenta'>
<div class='tabela'>
    <h3>Dane osobowe</h3>
    <table>
    <tr>
        <th>ID Pacjenta</th>
        <th>ID Lekarza prowadzącego</th>
        <th>ID obecnego pobytu</th>
        <th>Data dodania do systemu</th>
        <th>Historia medyczna pacjenta</th>
    </tr>
    <tr>
        <td><?php echo $id_pacjenta?></td>
        <td><?php echo $id_lekarza?></td>
        <td><?php echo $id_pobytu?></td>
        <td><?php echo $data_dodania_do_bazy?></td>
        <td><?php if($wynik_pacjent['historia_pacjenta'] != NULL){
            ?><a id="wyswietl_historie" href='#historia_pacjenta'>Kliknij by wyświetlić</a>  
            <?php }
                else{ 
                     echo $historia_pacjenta;
                }?>
        </td>
    </tr>
    </table>
    <table>
    <tr>
        <th>Imię</th>
        <th>Nazwisko</th>
        <th>PESEL</th>
        <th>Obywatelstwo</th>
        <th>Data urodzenia</th>
        <th>Miejsce urodzenia</th>
    </tr>
    <tr>
        <td><?php echo $imie?></td>
        <td><?php echo $nazwisko?></td>
        <td><?php echo $pesel?></td>
        <td><?php echo $obywatelstwo?></td>
        <td><?php echo $data_urodzenia?></td>
        <td><?php echo $miejsce_urodzenia?></td>
    </tr>
    </table> 
</div>
</div>
<?php 
if(isset($lista_historia_pacjenta)){
    foreach($lista_historia_pacjenta as $historia => $historia_pobytu){ 
        if($historia === array_keys($lista_historia_pacjenta)[0]){
            ?>
            <div id="historia_pacjenta" class="belka-historia-pacjenta" hidden> 
                <style>
                    .belka-historia-pacjenta{
                        text-align: center;
                    }
                </style>
            <h2>Historia medyczna pacjenta nr ID: <?php echo $id_pacjenta?></h2>
            <p><a id="zamknij-widok" href="lek-wyswietl-dane-pacjenta.php?id_pacjenta=<?php echo $id_pacjenta?>">Zamknij widok</a></p>
            </div>
            <?php
        }
        else{
        ?>
            <div class="historia-pacjenta" hidden> 
            <h3>Historia pobytu nr ID: <?php echo substr($historia_pobytu, 28, 5)?></h3>
            <span><?php echo $historia_pobytu ?></span>
            </div>
  <?php }
    }
} ?>
</div>
<div class='secondary-container-right-wyswietl-dane-pacjenta'>
<div class='tabela-dane-adresowe-kontaktowe-pacjenta'>
<div class='tabela'>
<form action='lek-wpisz-zmien-dane-pacjenta.php?id_pacjenta=<?php echo $id_pacjenta?>' method='post'>
    <h3>Dane kontaktowe</h3>
    <table>
    <tr>
        <th>Ulica*</th>
        <th>Nr domu*</th>
        <th>Nr mieszkania</th>
    </tr>
    <tr>
        <td><input type='text' name='ulica' autocomplete='off' maxlength='40' value='<?php echo $ulica?>' required disabled></td>
        <td><input type='number' name='nr_domu' autocomplete='off' maxlength='4' value='<?php echo $nr_domu?>' required disabled></td>
        <td id='nr_mieszkania'><?php if(!empty($wynik_pacjent['nr_mieszkania'])){?><input type='number' name='nr_mieszkania' autocomplete='off' maxlength='4' value='<?php echo $nr_mieszkania?>' disabled><?php } ?>
        </td>  
    </tr>
    <tr>
        <th>Kod pocztowy*</th>
        <th>Miasto*</th>
        <th>Województwo*</th>
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
            <td><input id='tel' type='tel' name='telefon'  value='<?php echo $tel?>' pattern='[0-9]{3} [0-9]{3} [0-9]{3}|[0-9]{9}' autocomplete='off' maxlength='11'  disabled></td>
            <td><input id= 'email' type='email' name='email'  value='<?php echo $email?>' pattern='^[a-z0-9._]+@[a-z0-9.-]+\.[a-z]{2,4}$' autocomplete='off'  maxlength='40'  disabled></td>
        </tr>
    </table>
    <button id='przycisk_zmien_dane' type='submit' name='lek_zmien_dane_pacjenta' hidden>Zmień dane pacjenta</button>
    </form>
</div>
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
