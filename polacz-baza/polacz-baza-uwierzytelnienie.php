<?php
session_start();
try{
    $link = new mysqli('localhost','uwierzytelnienie','pnbwTyAUs78u2u6','szpital');
}
catch(Exception $e){
    $_SESSION['blad_polaczenia'] = 1;
    if(isset($_SESSION['zalogowany_lekarz'])){
        unset($_SESSION['zalogowany_lekarz']);
    }
    elseif(isset($_SESSION['zalogowany_rejestrator'])){
        unset($_SESSION['zalogowany_rejestrator']);
    }
    header('Location: ../logowanie.php');
    die();
}
?>
