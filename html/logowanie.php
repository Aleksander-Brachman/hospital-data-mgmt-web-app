<?php 
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <link href='logowanie-style.css' rel='stylesheet'>
    <title>Logowanie</title>
</head>
<body>
<main>
<div class='main-container-logowanie'>
<div class='komunikat'>
<?php
if(isset($_POST['wyloguj'])){
    if(isset($_SESSION['zalogowany_lekarz'])){
            unset($_SESSION['zalogowany_id']);
            unset($_SESSION['zalogowany_lekarz']);
            ?><style>
            .komunikat{
                border: 1px solid black;
            }
            </style><p><b>Udało się poprawnie wylogować!</b></p><?php
    }
    else if(isset($_SESSION['zalogowany_rejestrator'])){
            unset($_SESSION['zalogowany_id']);
            unset($_SESSION['zalogowany_rejestrator']);
            ?><style>
            .komunikat{
                border: 1px solid black;
            }
            </style><p><b>Udało się poprawnie wylogować!</b></p><?php
    }
}
if(isset($_SESSION['blad_logowania'])){?>
    <style>
    .komunikat{
        border: 1px solid black;
    }
    </style>
    <p><b>Błąd logowania - błędna nazwa użytkownika i/lub hasło</b></p><?php
    unset($_SESSION['blad_logowania']);
}
if(isset($_SESSION['blad_proby_uwierzytelnienia'])){?>
    <style>
    .komunikat{
        border: 1px solid black;
    }
    </style>
    <p><b>Wystąpił błąd przy próbie uwierzytelnienia danych. Spróbuj ponownie.</b></p><?php
    unset($_SESSION['blad_proby_uwierzytelnienia']);
}
if(isset($_SESSION['blad_polaczenia'])){
    ?><style>
    .komunikat{
        border: 1px solid black;
    }
    </style>
    <p><b>Błąd połączenia. Zaloguj się ponownie lub skontaktuj się z Administratorem.</b></p><?php
    unset($_SESSION['blad_polaczenia']);
    if(isset($_SESSION['zalogowany_id'])){
        unset($_SESSION['zalogowany_id']);
    }
}
if(isset($_SESSION['sukces_logowania'])){
    unset($_SESSION['zalogowany_id']);
    unset($_SESSION['sukces_logowania']);
}
?>
</div>
<?php
//Automatyczne przekierowywanie przy aktywnym zalogowaniu
if(isset($_SESSION['zalogowany_lekarz'])){
    header('Location: Lekarz/lek-strona-glowna.php'); 
    exit();
} 
if(isset($_SESSION['zalogowany_rejestrator'])){
    header('Location: Rejestracja/rej-strona-glowna.php'); 
    exit();
}
?>
<div class='okno-formularz'>
<h1>Zaloguj się do systemu</h1>
<div class='formularz'>
<form action='uwierzytelnienie.php' method='post'>
        <label>Nazwa użytkownika:</label><input type='text' name='nazwa_uzytkownika' placeholder='Wpisz nazwę użytkownika' autocomplete='off' required>
        <label>Hasło:</label><input type='password' name='haslo' placeholder='Wpisz hasło' autocomplete='off' required>
        <button type='submit' name='zaloguj'>Zaloguj</button>
</form> 
</div>
</div>
</div>
</main>
</body>
</html>
