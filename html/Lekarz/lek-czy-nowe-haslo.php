<?php
session_start();
if(isset($_SESSION['zalogowany_lekarz'])){
    if(isset($_SESSION['sukces_uzupelnienia_danych'])){?>
        <!DOCTYPE html>
        <html>
        <head>
        <meta charset="utf-8">
        <link href='lek-uzupelnij-dane-style.css' rel="stylesheet">
        <title>Zmiana hasła</title>
        </head>
        <body>
        <header>
            <h1 class="panel">Panel lekarz</h1>
            <form action="../logowanie.php" method="post"><button type="submit" name="wyloguj">Wyloguj się</button></form>
            <script type="text/javascript" src='jquery-3.7.1.min.js'></script>
        </header>
        <main>
        <div class="main-container-nowe-haslo">
        <div class="informacje-nowe-haslo">
            <h3>Dane zostały prawidłowo uzupełnione.</h3>
            <p>Teraz możesz zdecydować czy chcesz zmienić swoje hasło dostępu do systemu, przyznane przez Administratora.<br>
            <b>Pamiętaj, że hasło powinno być silne, zgodnie z poniższymi zaleceniami.</b><br>
            Jeżeli nie chcesz zmieniać hasła przyznanego przez Administratora, kliknij w link <i>Przejdź do strony głównej</i></p>
        </div>
        <p class="strona-glowna"><a href='lek-strona-glowna.php'>Przejdź do strony głównej</a></p>
        <div class="komunikat-blad-zmiany-hasla">
        <?php
            if(isset($_SESSION['blad_zmien_haslo_sukces_uzupelnienia_danych'])){
                ?><style>
                .komunikat-blad-zmiany-hasla{
                    border: 1px solid black;
                }
                </style><p><b>Nie udało się zaktualizować hasła. Zwróć uwagę na poprawność danych i spróbuj ponownie.</b></p><?php
                unset($_SESSION['blad_zmien_haslo_sukces_uzupelnienia_danych']);
            }
        ?>
        </div>
        <div class="secondary-container-nowe-haslo">
        <div class="wymogi-nowe-haslo">
        <p>Jeżeli chcesz zmienić swoje hasło, skorzystaj z formularza obok.</p>
            <p><b>Hasło powinno:</b></p>
            <ul>
                <li><p id="dlugosc" class="nie-spelnia"><b>Zawierać co najmniej osiem znaków i maksymalnie dwadzieścia cztery znaki</p></b></li>
                <li><p id="wielka_litera" class="nie-spelnia"><b>Zawierać co najmniej jedną wielką literę</p></b></li>
                <li><p id="cyfra" class="nie-spelnia"><b>Zawierać co najmniej jedną cyfrę</p></b></li>
                <li><p id="znak_specjalny" class="nie-spelnia"><b>Zawierać co najmniej jeden znak specjalny (!, ?, &, *)</p></b></li>
            </ul>
        </div>
        <div class="nowe-haslo-formularz">
            <form action="lek-wpisz-nowe-haslo.php" method="post">
            <label>Wprowadź obecne hasło</label><input type="password" name="obecne_haslo" required>
            <label>Wprowadź nowe hasło</label><input id="nowe_haslo" type="password"  name="nowe_haslo" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!?&*]).{8,24}$" title="Co najmniej jedna wielka litera, jedna cyfra, jeden znak specjalny i między 8, a 24 znaki" required>           
            <label>Powtórz nowe hasło</label><input type="password" name="nowe_haslo_potwierdz" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!?&*]).{8,24}$" title="Co najmniej jedna wielka litera, jedna cyfra, jeden znak specjalny i między 8, a 24 znaki" required>
            <button type="submit" name="lek_zmien_haslo">Zmień hasło</button>
            </form>
        </div>
        </div>
        </div>
        </main>
        </body>
        <script>
            var haslo = document.getElementById("nowe_haslo");
            var wielka_litera = document.getElementById("wielka_litera");
            var znak_specjalny = document.getElementById("znak_specjalny");
            var cyfra = document.getElementById("cyfra");
            var dlugosc = document.getElementById("dlugosc");
            
            haslo.onkeyup = function() {
                var znaki_specjalne = /[!?&*]/g;
                if(haslo.value.match(znaki_specjalne)) {
                    znak_specjalny.classList.remove("nie-spelnia");
                    znak_specjalny.classList.add("spelnia");
                } else {
                    znak_specjalny.classList.remove("spelnia");
                    znak_specjalny.classList.add("nie-spelnia");
                }

                var wielkie_litery = /[A-Z]/g;
                if(haslo.value.match(wielkie_litery)) {
                    wielka_litera.classList.remove("nie-spelnia");
                    wielka_litera.classList.add("spelnia");
                } else {
                    wielka_litera.classList.remove("spelnia");
                    wielka_litera.classList.add("nie-spelnia");
                }

                var cyfry = /[0-9]/g;
                if(haslo.value.match(cyfry)) {
                    cyfra.classList.remove("nie-spelnia");
                    cyfra.classList.add("spelnia");
                } else {
                    cyfra.classList.remove("spelnia");
                    cyfra.classList.add("nie-spelnia");
                }

                if(haslo.value.length >= 8 && haslo.value.length < 24) {
                    dlugosc.classList.remove("nie-spelnia");
                    dlugosc.classList.add("spelnia");
                } else {
                    dlugosc.classList.remove("spelnia");
                    dlugosc.classList.add("nie-spelnia");
                }
            }
        </script>
        </html>           
        <?php
        }
    else{
        header('Location: lek-strona-glowna.php');
        exit();
    }
}
else{
    header('Location: ../logowanie.php');
}
?>
