<?php
require('../../polacz-baza/polacz-baza-zmien-haslo.php');
require('../../zabezpieczenia/szyfruj-deszyfruj.php');
require('../../zabezpieczenia/zabezpieczenia-formularz.php');

if(isset($_SESSION['zalogowany_lekarz'])){
    if(isset($_SESSION['sukces_uzupelnienia_danych'])){
        if(isset($_POST['lek_zmien_haslo'])){
            $obecne_haslo = zabezpieczenia($_POST['obecne_haslo']);
            $zapytanie_haslo = $link->prepare("SELECT haslo FROM uzytkownik WHERE id_uzytkownika=?");
            $zapytanie_haslo->bind_param('i', $_SESSION['zalogowany_id']);
            $zapytanie_haslo->execute();
            if(!$zapytanie_haslo){
                $_SESSION['blad_zmien_haslo_sukces_uzupelnienia_danych'] = 1;
                header('Location: lek-czy-nowe-haslo.php');
                exit();
            }
            $wynik_haslo = $zapytanie_haslo->get_result();
            $wynik_haslo = $wynik_haslo->fetch_array();

            $porownaj_haslo = password_verify($obecne_haslo, $wynik_haslo['haslo']);
            if($porownaj_haslo){
                $nowe_haslo = zabezpieczenia($_POST['nowe_haslo']);
                $nowe_haslo_potwierdz = zabezpieczenia($_POST['nowe_haslo_potwierdz']);
                if($nowe_haslo === $nowe_haslo_potwierdz){
                    $nowe_haslo_hash = password_hash($nowe_haslo, PASSWORD_DEFAULT);
                    $zmien_haslo = $link->prepare("UPDATE uzytkownik SET haslo=? WHERE id_uzytkownika=?");
                    $zmien_haslo->bind_param('si', $nowe_haslo_hash, $_SESSION['zalogowany_id']);
                    $zmien_haslo->execute();
                    if(!$zmien_haslo){
                        $_SESSION['blad_zmien_haslo_sukces_uzupelnienia_danych'] = 1;
                        header('Location: lek-czy-nowe-haslo.php');
                        exit();
                    }
                    else{
                        $data_zmiany_hasla = szyfruj(date_create()->format('Y-m-d H:i:s'));
                        $aktualizuj_date_zmiany_hasla = $link->prepare("UPDATE lekarz SET data_ostatniej_zmiany_hasla=? WHERE id_lekarza=?");
                        $aktualizuj_date_zmiany_hasla->bind_param('si', $data_zmiany_hasla, $_SESSION['zalogowany_id']);
                        $aktualizuj_date_zmiany_hasla->execute();
                        if(!$aktualizuj_date_zmiany_hasla){
                            $_SESSION['sukces_zmien_haslo_bez_daty'] = 1;
                            header('Location: lek-strona-glowna.php');
                            exit();
                        }
                        else{
                            $_SESSION['sukces_zmien_haslo'] = 1;
                            header('Location: lek-strona-glowna.php');
                            exit();
                        }
                    }
                }
                else{
                    $_SESSION['blad_zmien_haslo_sukces_uzupelnienia_danych'] = 1;
                    header('Location: lek-czy-nowe-haslo.php');
                    exit();
                }
            }
            else{
                $_SESSION['blad_zmien_haslo_sukces_uzupelnienia_danych'] = 1;
                header('Location: lek-czy-nowe-haslo.php');
                exit();
            }
        }
        else{
            header('Location: lek-czy-nowe-haslo.php');
            exit();
        }
    }
    else{
            header('Location: lek-strona-glowna.php');
            exit();
    }
}
else{
    header('Location: ../logowanie.php');
    exit();
}
?>
<?php
$link->close();
?>