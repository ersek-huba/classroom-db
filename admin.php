<?php
include_once("html.php");
include_once("sql.php");
include_once("adminHtml.php");

$mysqli = getSQLConnection(database: "schoolbook");
htmlHead();
formHead();
showTableDropdown();
showAdminViewButton($mysqli);
handleAdminPostRequest($mysqli);
formEnd();
htmlEnd();
