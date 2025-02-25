<?php
function getSQLConnection($host = "localhost", $user = "root", $password = "huba", $database = NULL)
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli($host, $user, $password, $database);
    return $mysqli;
}

function dropDatabase($mysqli, $database)
{
    return $mysqli->execute_query("DROP DATABASE IF EXISTS `$database`");
}

function createDatabase($mysqli, $database)
{
    return $mysqli->execute_query("CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARSET utf8");
}

function checkDatabase($mysqli, $database)
{
    $result = $mysqli->execute_query("SHOW DATABASES LIKE '$database'");
    return $result->num_rows !== 0;
}

function createTableClasses($mysqli)
{
    return $mysqli->execute_query("
      CREATE TABLE IF NOT EXISTS `schoolbook`.`classes` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `code` VARCHAR(3) NULL,
        `year` INT NULL,
        PRIMARY KEY (`id`))
      ENGINE = InnoDB;");
}

function insertDataIntoClasses($mysqli)
{
    $result = $mysqli->execute_query("DELETE FROM `schoolbook`.`classes`");
    if (!$result) return false;
    for ($year = 2022; $year <= 2024; $year++)
    {
        foreach (CLASSES as $class)
        {
            $result = $mysqli->execute_query("INSERT INTO `schoolbook`.`classes` (code, year) VALUES ('$class', $year);");
            if (!$result) return false;
        }
    }
    return true;
}

function createTableSubjects($mysqli)
{
    return $mysqli->execute_query("
      CREATE TABLE IF NOT EXISTS `schoolbook`.`subjects` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(20) NOT NULL,
        PRIMARY KEY (`id`))
      ENGINE = InnoDB;");
}

function insertDataIntoSubjects($mysqli)
{
    $result = $mysqli->execute_query("DELETE FROM `schoolbook`.`subjects`");
    if (!$result) return false;
    foreach (SUBJECTS as $subject)
    {
        $result = $mysqli->execute_query("INSERT INTO `schoolbook`.`subjects` (name) VALUES ('$subject');");
        if (!$result) return false;
    }
    return true;
}

function createTableStudents($mysqli)
{
    return $mysqli->execute_query("
      CREATE TABLE IF NOT EXISTS `schoolbook`.`students` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(50) NOT NULL,
        `gender` INT NULL,
        `class_id` INT NULL,
        PRIMARY KEY (`id`))
      ENGINE = InnoDB;
    ");
}

function insertDataIntoStudents($mysqli)
{
    $result = $mysqli->execute_query("DELETE FROM `schoolbook`.`students`");
    if (!$result) return false;
    $result = $mysqli->execute_query("SELECT id FROM `schoolbook`.`classes`");
    if (!$result) return false;
    $classes = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($classes as $class)
    {
        $classId = $class['id'];
        $classCount = rand(MIN_CLASS_COUNT, MAX_CLASS_COUNT);
        for ($count = 0; $count < $classCount; $count++)
        {
            $gender = rand(0, 1);
            $lastName = NAMES['lastnames'][array_rand(NAMES['lastnames'])];
            $firstName = NAMES['firstnames'][$gender === 0 ? 'men' : 'women'][array_rand(NAMES['firstnames'][$gender === 0 ? 'men' : 'women'])];
            $name = $lastName . ' ' . $firstName;
            $result = $mysqli->execute_query("INSERT INTO `schoolbook`.`students` (name, gender, class_id) VALUES ('$name', $gender, $classId);");
            if (!$result) return false;
        }
    }
    return true;
}

function createTableGrades($mysqli)
{
    return $mysqli->execute_query("
      CREATE TABLE IF NOT EXISTS `schoolbook`.`grades` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `student_id` INT NULL,
        `subject_id` INT NULL,
        `grade` INT NULL,
        `date` DATE NULL,
        PRIMARY KEY (`id`))
      ENGINE = InnoDB;
    ");
}

function insertDataIntoGrades($mysqli)
{
    $result = $mysqli->execute_query("DELETE FROM `schoolbook`.`grades`");
    if (!$result) return false;
    $result = $mysqli->execute_query("SELECT s.id, c.year FROM `schoolbook`.`students` s JOIN `schoolbook`.`classes` c ON s.class_id = c.id");
    if (!$result) return false;
    $students = $result->fetch_all(MYSQLI_ASSOC);
    $result = $mysqli->execute_query("SELECT id FROM `schoolbook`.`subjects`");
    if (!$result) return false;
    $subjects = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($students as $student)
    {
        $studentId = $student['id'];
        foreach ($subjects as $subject)
        {
            $subjectId = $subject['id'];
            $grades = generateRandomNumbers(1, 5, MARKS_COUNT);
            foreach ($grades as $grade)
            {
                $date = sprintf("%d-%d-%d", $student['year'], rand(1, 12), rand(1, 28));
                $result = $mysqli->execute_query("INSERT INTO `schoolbook`.`grades` (student_id, subject_id, grade, date) VALUES ($studentId, $subjectId, $grade, '$date')");
                if (!$result) return false;
            }
        }
    }
    return true;
}

function generateRandomNumbers($min, $max, $count)
{
    $ret = [];
    for ($i = 0; $i < $count; $i++)
    {
        $ret[] = rand($min, $max);
    }
    return $ret;
}

