<?php
session_start();
try{
    $link = new mysqli('localhost','lekarz','hvXJ7pO11nrCI2e','szpital');
}
catch(Exception $e){
    $_SESSION['blad_polaczenia'] = 1;
    if(isset($_SESSION['zalogowany_lekarz'])){
        unset($_SESSION['zalogowany_lekarz']);
    }
    header('Location: ../logowanie.php');
    die();
}
?>
