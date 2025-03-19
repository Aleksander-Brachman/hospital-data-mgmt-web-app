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
    <title>Lista pacjentów</title>
    <link href='rej-style.css' rel='stylesheet'>
    <script type='text/javascript' src='jquery-3.7.1.min.js'></script>
    <script>
         $(function(){
            $('#wroc_do_calej_listy').click(function(){
                location.reload();
            });
         });
         function zmianaWyszukiwania(value) {
            if(value === 'szukaj_po_pesel'){
                document.getElementById('pesel').removeAttribute('hidden');
                document.getElementById('pesel').removeAttribute('disabled');

                document.getElementById('id').setAttribute('hidden', true);
                document.getElementById('id').setAttribute('disabled', true);
                
                document.getElementById('nazwisko').setAttribute('hidden', true);
                document.getElementById('nazwisko').setAttribute('disabled', true);
            }
            else if(value === 'szukaj_po_id'){
                document.getElementById('id').removeAttribute('hidden');
                document.getElementById('id').removeAttribute('disabled');

                document.getElementById('pesel').setAttribute('hidden', true);
                document.getElementById('pesel').setAttribute('disabled', true);
                
                document.getElementById('nazwisko').setAttribute('hidden', true);
                document.getElementById('nazwisko').setAttribute('disabled', true);
            }
            else if(value==='szukaj_po_nazwisku'){
                document.getElementById('nazwisko').removeAttribute('hidden');
                document.getElementById('nazwisko').removeAttribute('disabled');

                document.getElementById('pesel').setAttribute('hidden', true);
                document.getElementById('pesel').setAttribute('disabled', true);
                
                document.getElementById('id').setAttribute('hidden', true);
                document.getElementById('id').setAttribute('disabled', true);
            }
         }
    </script>
</head>
<body>
<header>
    <h1 class='panel'>Panel rejestracja</h1>
    <p class='zalogowal-sie'>Zalogował się rejestrator: <?php echo $imie_rejestratora?> <?php echo $nazwisko_rejestratora?>, ID: <?php echo $_SESSION['zalogowany_id']?></p>
    <form action='../logowanie.php' method='post'><button type='submit' name='wyloguj'>Wyloguj się</button></form>
</header>
<main>
<div class='main-container-lista-pacjentow'>
<p class='strona-glowna'><a href='rej-strona-glowna.php'>Wróć do strony głównej</a></p>
<div class='komunikat'>
<?php
if(isset($_SESSION['sukces_dodaj_pacjenta'])){
    ?><style>
    .komunikat{
        border: 1px solid black;
    }
    </style><p><b>Nowy pacjent został dodany pomyślnie. ID Pacjenta: <?php echo $_SESSION['id_nowego_pacjenta']?></b></p><?php
    unset($_SESSION['id_nowego_pacjenta']);
    unset($_SESSION['sukces_dodaj_pacjenta']);
}
if(isset($_SESSION['sukces_zmiana_danych_pacjenta'])){
    ?><style>
    .komunikat{
        border: 1px solid black;
    }
    </style><p><b>Dane pacjenta o nr ID: <?php echo $_SESSION['zmiana_danych_id_pacjenta']?> zostały zmienione pomyślnie.</b></p><?php
    unset($_SESSION['zmiana_danych_id_pacjenta']);
    unset($_SESSION['sukces_zmiana_danych_pacjenta']);
}
?>
</div>
<div class='szukaj-pacjenta'>
<form action='rej-szukaj-pacjenta.php' method='post'>
    <label><b>Szukaj pacjenta: </b></label>
    <select name='wybor_szukaj' onchange='zmianaWyszukiwania(value)'><option value='szukaj_po_id'>Wyszukaj pacjenta po ID (domyślnie)</option>
    <option value='szukaj_po_pesel'>Wyszukaj pacjenta po PESEL</option><option value='szukaj_po_nazwisku'>Wyszukaj pacjenta po nazwisku</option></select>
    <input id='id' type='text' name='szukane_id' pattern='^\d*' placeholder='Wpisz ID Pacjenta' minlength='4' maxlength='4' autocomplete='off' required>
    <input id='pesel' type='text' name='szukany_pesel' pattern='^\d*' placeholder='Wpisz PESEL Pacjenta' minlength='11' maxlength='11' autocomplete='off' required hidden disabled>
    <input id='nazwisko' type='text' name='szukane_nazwisko' maxlength='40' placeholder='Wpisz nazwisko Pacjenta' autocomplete='off' required hidden disabled>
    <button type='submit' name='szukaj'>Szukaj pacjenta</button>
    </form>
</div>
<div class='informacje-lista-pacjentow'>
<h3>Lista zarejestrowanych pacjentów</h3>
</div>

<div class='tabela-lista-pacjentow'>
        <table>
            <tr class='kolumny-pacjent'>
                <th>ID Pacjenta</th>
                <th>Imię i nazwisko</th>
                <th>PESEL</th>
                <th>Data dodania do systemu</th>
                <th>Czy pacjent znajduje się aktualnie w szpitalu?</th>
                <th>Działania</th>
            </tr>
                <?php
                if(isset($_SESSION['jest_pacjent'])){
                    foreach($_SESSION['wyszukany_pacjent_id'] as $id_pacjenta){
                    $zapytanie_pacjent = $link->prepare("SELECT id_pacjenta, imie, nazwisko, pesel, data_dodania_do_bazy, id_lekarza, data_rozpoczecia_ostatniego_pobytu FROM pacjent WHERE id_pacjenta=?");
                    $zapytanie_pacjent->bind_param('i', $id_pacjenta);
                    $zapytanie_pacjent->execute();
                    $wynik_pacjent=$zapytanie_pacjent->get_result();
                    $wynik_pacjent=$wynik_pacjent->fetch_array();
                    ?>

                    <tr>
                    <td><?php echo $wynik_pacjent['id_pacjenta']?></td>
                    <td><?php echo deszyfruj($wynik_pacjent['imie'])?> <?php echo deszyfruj($wynik_pacjent['nazwisko'])?></td>
                    <td><?php echo deszyfruj($wynik_pacjent['pesel'])?></td>
                    <td><?php echo $wynik_pacjent['data_dodania_do_bazy']?></td>
                    <?php
                    if($wynik_pacjent['id_lekarza'] != NULL){
                        ?>
                        <td><span><b>TAK</b></span><span>Pobyt od: <?php echo deszyfruj($wynik_pacjent['data_rozpoczecia_ostatniego_pobytu'])?></span><span>ID Lekarza prowadzącego: <?php echo $wynik_pacjent['id_lekarza']?></span></td><?php
                        }
                    else{
                    ?>
                        <td><span><b>NIE</b></span></td><?php
                    }
                    ?>
                    <td><a href='rej-wyswietl-dane-pacjenta.php?id_pacjenta=<?php echo $wynik_pacjent['id_pacjenta']?>'>Wyświetl i zmień dane pacjenta</a></td>      
                    </tr>
                    <?php } ?>
                    </table>
                    <button type='button' id='wroc_do_calej_listy'>Wróć do całej listy</button>     
                    </div>   
                    <?php 
                    unset($_SESSION['jest_pacjent']);
                    unset($_SESSION['wyszukany_pacjent_id']);
                }
                else if(isset($_SESSION['brak_pacjenta'])){
                    ?>
                    <style>
                        .kolumny-pacjent {
	                        display: none;
                        }
                    </style>

                    <?php
                    if(isset($_SESSION['szukane_id'])){
                        ?><div class='blad-wyszukiwania'><style>
                        .blad-wyszukiwania{
                            border: 1px solid black;
                        }
                        </style>
                        <p><b>Brak pacjenta w systemie z nr ID: <?php echo $_SESSION['szukane_id']?></p></b>
                        </div>
                        <button type='button' id='wroc_do_calej_listy'>Wróć do całej listy</button>
                        </table>
                        </div>
                        <?php
                        unset($_SESSION['szukane_id']);
                        unset($_SESSION['brak_pacjenta']);
                    }
                    elseif(isset($_SESSION['szukany_pesel'])){
                        ?><div class='blad-wyszukiwania'><style>
                        .blad-wyszukiwania{
                            border: 1px solid black;
                        }
                        </style>
                        <p><b>Brak pacjenta w systemie z nr PESEL: <?php echo $_SESSION['szukany_pesel']?></p></b>
                        </div>
                        <button type='button' id='wroc_do_calej_listy'>Wróć do całej listy</button>
                        </table>
                        </div>
                        <?php
                        unset($_SESSION['szukany_pesel']);
                        unset($_SESSION['brak_pacjenta']);
                    }
                    elseif(isset($_SESSION['szukane_nazwisko'])){
                        ?><div class='blad-wyszukiwania'><style>
                        .blad-wyszukiwania{
                            border: 1px solid black;
                        }
                        </style>
                        <p><b>Brak pacjenta w systemie o nazwisku: <?php echo $_SESSION['szukane_nazwisko']?></p></b>
                        </div>
                        <button type='button' id='wroc_do_calej_listy'>Wróć do całej listy</button>
                        </table>
                        </div>
                        <?php
                        unset($_SESSION['szukane_nazwisko']);
                        unset($_SESSION['brak_pacjenta']);
                    }
                }
                else{
                    $zapytanie_wszyscy_pacjenci = mysqli_query($link,"SELECT id_pacjenta, imie, nazwisko, pesel, data_dodania_do_bazy, id_lekarza, data_rozpoczecia_ostatniego_pobytu FROM pacjent ORDER BY data_dodania_do_bazy DESC");
                    while($wynik_wszyscy_pacjenci = mysqli_fetch_assoc($zapytanie_wszyscy_pacjenci)){
                    ?><tr>
                        <td><?php echo $wynik_wszyscy_pacjenci['id_pacjenta']?></td>
                        <td><?php echo deszyfruj($wynik_wszyscy_pacjenci['imie'])?> <?php echo deszyfruj($wynik_wszyscy_pacjenci['nazwisko'])?></td>
                        <td><?php echo deszyfruj($wynik_wszyscy_pacjenci['pesel'])?></td>
                        <td><?php echo $wynik_wszyscy_pacjenci['data_dodania_do_bazy']?></td>
                        <td>
                            <?php if($wynik_wszyscy_pacjenci['id_lekarza']!=NULL){?>
                                <span><b>TAK</b></span><span>Pobyt od: <?php echo deszyfruj($wynik_wszyscy_pacjenci['data_rozpoczecia_ostatniego_pobytu'])?></span><span>ID Lekarza prowadzącego: <?php echo $wynik_wszyscy_pacjenci['id_lekarza']?></span>
                            <?php }
                            else{ ?>
                                <span><b>NIE</b></span>
                            <?php }?>
                        </td>
                        <td><a href='rej-wyswietl-dane-pacjenta.php?id_pacjenta=<?php echo $wynik_wszyscy_pacjenci['id_pacjenta']?>'>Wyświetl i zmień dane pacjenta</a></td>
                    </tr>
                    <?php } ?>
                    </table> 
                    </div>
                <?php }?>
</div>
</main>
</body>
</html>
<?php
$link->close();
?>
