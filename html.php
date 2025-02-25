<?php
function htmlHead()
{
    echo <<<HTML
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Osztályok</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="contents">
HTML;
}

function htmlEnd()
{
    echo '
    </div>
    </body>
    </html>';
}

function formHead()
{
    echo '<nav>
        <form name="nav" method="post" action="">';
}

function formEnd()
{
    echo '</form>
        </nav>';
}

function showInstallButton($mysqli)
{
    if (checkDatabase($mysqli, "schoolbook")) return;
    echo '<button type="submit" name="install" value="0">Adatbázis létrehozása, és feltöltése</button>';
}

function showYearDropdown($mysqli)
{
    if (!checkDatabase($mysqli, "schoolbook")) return;
    $result = $mysqli->execute_query("SELECT year FROM `schoolbook`.`classes` GROUP BY 1;");
    if (!$result)
    {
        showMessage("Hiba: Nem sikerült lekérni az évszámokat!", true);
        return;
    }
    $years = $result->fetch_all();
    echo '<label for="year">Évfolyam </label>';
    echo '<select name="year" id="year">';
    foreach ($years as $year)
    {
        $year = $year[0];
        echo "<option value='$year'>$year</option>";
    }
    echo '</select><br>';
}

function showClassDropdown($mysqli)
{
    if (!checkDatabase($mysqli, "schoolbook")) return;
    $result = $mysqli->execute_query("SELECT code FROM `schoolbook`.`classes` GROUP BY 1;");
    if (!$result)
    {
        showMessage("Hiba: Nem sikerült lekérni az osztályokat!", true);
        return;
    }
    $classes = $result->fetch_all();
    echo '<label for="class">Osztály </label>';
    echo '<select name="class">';
    foreach ($classes as $class)
    {
        $class = $class[0];
        echo "<option value='$class'>$class</option>";
    }
    echo '</select><br>';
}

function showQueryButton()
{
    if (!checkDatabase($mysqli, "schoolbook")) return;
    echo '<button type="submit" name="query" value="0">Adatok lekérése</button>';
}

function showMessage($msg, $error = false)
{
    if ($error)
    {
        echo "<p style='color: red'>$msg</p>";
        return;
    }
    echo "<p>$msg</p>";
}

function installDatabase($mysqli)
{
    $result = createDatabase($mysqli, "schoolbook");
    if (!$result)
    {
        showMessage("Hiba: Nem sikerült adatbázist létrehozni!", true);
        return;
    }
    $result = createTableClasses($mysqli);
    if ($result)
    {
        showMessage("Osztály tábla sikeresen létrehozva!");
    }
    $result = createTableGrades($mysqli);
    if ($result)
    {
        showMessage("Osztályzatok tábla sikeresen létrehozva!");
    }
    $result = createTableStudents($mysqli);
    if ($result)
    {
        showMessage("Tanulók tábla sikeresen létrehozva!");
    }
    $result = createTableSubjects($mysqli);
    if ($result)
    {
        showMessage("Tantárgyak tábla sikeresen létrehozva!");
    }
    $result = insertDataIntoClasses($mysqli);
    if ($result)
    {
        showMessage("Osztály tábla sikeresen feltöltve!");
    }
    $result = insertDataIntoSubjects($mysqli);
    if ($result)
    {
        showMessage("Tantárgyak tábla sikeresen feltöltve!");
    }
    $result = insertDataIntoStudents($mysqli);
    if ($result)
    {
        showMessage("Tanulók tábla sikeresen feltöltve!");
    }
    $result = insertDataIntoGrades($mysqli);
    if ($result)
    {
        showMessage("Osztályzatok tábla sikeresen feltöltve!");
    }
}

// osztályátlag, tantárgyanként, tanulók, tanulók átlagai, tantárgyanként
function showClassStatistics($mysqli)
{
    if (!checkDatabase($mysqli, "schoolbook")) return;
    if (isset($_POST["year"]) && isset($_POST["class"]))
    {
        $year = $_POST["year"];
        $class = $_POST["class"];
        $result = getClassAverage($mysqli, $year, $class);
        if (!$result)
        {
            showMessage("Hiba: Nem sikerült osztály átlagot lekérni!", true);
            return;
        }
        $classAverage = $result->fetch_column();
        $classAveragesPerSubjects = [];
        foreach (SUBJECTS as $subject)
        {
            $result = getClassAveragePerSubject($mysqli, $year, $class, $subject);
            if (!$result)
            {
                showMessage("Hiba: Nem sikerült osztály átlagot lekérni!", true);
                return;
            }
            $avg = $result->fetch_column();
            $classAveragesPerSubjects[] = $avg;
        }
        
    }
}

function getClassAverage($mysqli, $year, $class)
{
    $query = "SELECT AVG(grade)
                FROM grades g JOIN students s ON g.student_id = s.id JOIN classes c ON s.class_id = c.id
                WHERE c.year = $year AND c.code = '$class'";
    return $mysqli->execute_query($query);
}

function getClassAveragePerSubject($mysqli, $year, $class, $subject)
{
    $query = "SELECT AVG(grade)
                FROM grades g JOIN students st ON g.student_id = st.id JOIN classes c ON st.class_id = c.id JOIN subjects su ON g.subject_id = su.id
                WHERE c.year = $year AND c.code = '$class' AND su.name = '$subject'";
    return $mysqli->execute_query($query);
}

function handlePostRequest($mysqli)
{
    if (isset($_POST["install"]))
    {
        if (!checkDatabase($mysqli, "schoolbook"))
        {
            installDatabase($mysqli);
        }
    }
    else if (isset($_POST["query"]))
    {
        
    }
}
