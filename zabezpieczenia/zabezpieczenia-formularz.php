<?php
function zabezpieczenia($dane)
{
 $dane = trim($dane);
 $dane = stripslashes($dane);
 $dane = htmlspecialchars($dane);
 return $dane;
}
?>