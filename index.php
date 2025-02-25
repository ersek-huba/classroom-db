<?php
include_once("data.php");
include_once("sql.php");
include_once("html.php");

$mysqli = getSQLConnection();
htmlHead();
formHead();
showInstallButton($mysqli);
showYearDropdown($mysqli);
showClassDropdown($mysqli);
formEnd();
handlePostRequest($mysqli);
htmlEnd();
