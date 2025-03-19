<?php
session_start();
try{
    $link = new mysqli('localhost','rejestracja','RzIuW2blmQXyvLv','szpital');
}
catch(Exception $e){
    $_SESSION['blad_polaczenia'] = 1;
    if(isset($_SESSION['zalogowany_rejestrator'])){
        unset($_SESSION['zalogowany_rejestrator']);
    }
    header('Location: ../logowanie.php');
    die();
}
?>
