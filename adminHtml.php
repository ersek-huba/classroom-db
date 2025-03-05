<?php
function showTableDropdown()
{
    if (isset($_POST['new']) || isset($_POST['change'])) return;
    echo '<label for="table" class="center">Tábla </label>';
    echo '<select name="table" id="table" class="center">';
    echo "<option value='classes'>Osztályok</option>";
    echo "<option value='subjects'>Tantárgyak</option>";
    echo "<option value='students'>Tanulók</option>";
    echo "<option value='grades'>Jegyek</option>";
    echo '</select><br>';
}

function showAdminViewButton($mysqli)
{
    if (isset($_POST['new']) || isset($_POST['change'])) return;
    echo '<button type="submit" name="admin-view" value="0" class="center">Admin nézet</button><br>';
}

function showNewButton($mysqli)
{
    $table = $_POST['table'];
    echo "<button type='submit' name='new' value='$table' class='center normal'>Új rekord felvétele</button>";
}

function handleAdminPostRequest($mysqli)
{
    if (isset($_POST['admin-view']))
    {
        showTableView($mysqli);
    }
    elseif (isset($_POST['new']))
    {
        showEditForm($mysqli, $_POST['new']);
    }
    elseif (isset($_POST['change']))
    {
        $changeInfo = explode(';', $_POST['change']);
        $table = $changeInfo[0];
        $id = intval($changeInfo[1]);
        showEditForm($mysqli, $table, $id);
    }
    elseif (isset($_POST['delete']))
    {
        deleteRecord($mysqli);
    }
    elseif (isset($_POST['save']))
    {
        insertIntoTable($mysqli);
    }
    elseif (isset($_POST['modify']))
    {
        updateTable($mysqli);
    }
}

function getFieldNameValues($mysqli)
{
    $table = $_POST['save'] ?? explode(';', $_POST['modify'])[0];
    $_POST['table'] = $table;
    $fields = array_slice(getFieldNames($mysqli), 1);
    unset($_POST['table']);
    $fieldNameValues = [];
    foreach ($fields as $field)
    {
        $fieldNameValues[$field] = $_POST[$field];
    }
    return $fieldNameValues;
}

function insertIntoTable($mysqli)
{
    $table = $_POST['save'];
    $fieldNameValues = getFieldNameValues($mysqli);
    $names = array_keys($fieldNameValues);
    $values = array_values($fieldNameValues);
    $args = "(?" . str_repeat(", ?", count($values) - 1) . ")";
    $query = "INSERT INTO $table (" . implode(', ', $names) . ") VALUES $args";
    $result = $mysqli->execute_query($query, $values);
    if (!$result)
    {
        showMessage("Hiba: Nem sikerült új rekordot felvenni!", true);
    }
    else
    {
        showMessage("A rekord fel lett töltve.");
    }
}

function updateTable($mysqli)
{
    $modifyInfo = explode(';', $_POST['modify']);
    $table = $modifyInfo[0];
    $id = intval($modifyInfo[1]);
    $fieldNameValues = getFieldNameValues($mysqli);
    $firstKey = array_key_first($fieldNameValues);
    $args = "$firstKey = ?";
    $values = [$fieldNameValues[$firstKey]];
    foreach ($fieldNameValues as $name => $value)
    {
        $args = $args . ", $name = ?";
        $values[] = $value;
    }
    $query = "UPDATE $table SET $args WHERE id = $id";
    $result = $mysqli->execute_query($query, $values);
    if (!$result)
    {
        showMessage("Hiba: Nem sikerült új rekordot felvenni!", true);
    }
    else
    {
        showMessage("A rekord fel lett töltve.");
    }
}

function showEditForm($mysqli, $tableName, $id = 0)
{
    echo "<h1 class='center'>Rekord szerkesztése</h1>";
    $_POST['table'] = $tableName;
    $fields = array_slice(getFieldNames($mysqli), 1);
    unset($_POST['table']);
    if ($id !== 0)
    {
        $values = getRowFromTable($mysqli, $tableName, $id);
    }
    foreach ($fields as $field)
    {
        $value = $id === 0 ? "" : $values[$field];
        echo "<label for='$field' class='center'>$field </label>";
        echo "<input id='$field' type='text' name='$field' value='$value'></input><br>";
    }
    $name = $id === 0 ? "save" : "modify";
    $value = "$tableName" . ($id === 0 ? "" : ";$id");
    echo "<button type='submit' name='$name' value='$value'>Mentés</button>";
}

function deleteRecord($mysqli)
{
    $deleteData = explode(';', $_POST['delete']);
    $table = $deleteData[0];
    $id = $deleteData[1];
    $result = $mysqli->execute_query("DELETE FROM $table WHERE id = $id");
    if (!$result)
    {
        showMessage("Hiba: Nem sikerült törölni!", true);
    }
    else
    {
        showMessage("A rekord törölve lett az adatbázisból!");
    }
}

function showTableView($mysqli)
{
    if (isset($_POST['table']))
    {
        echo "<h1 class='center'>" . $_POST['table'] . "</h1>";
        tableHead($mysqli);
        fillTableWithData($mysqli);
        tableEnd();
    }
}

function getFieldNames($mysqli)
{
    $fields = [];
    $table = $_POST['table'];
    $result = $mysqli->execute_query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'schoolbook' AND TABLE_NAME = '$table'");
    if (!$result)
    {
        showMessage("Hiba: Nem sikerült a mező neveket lekérni!", true);
        return;
    }
    $field = $result->fetch_column();
    while ($field)
    {
        $fields[] = $field;
        $field = $result->fetch_column();
    }
    return $fields;
}

function tableHead($mysqli)
{
    echo "<table class='center-table'>";
    echo "<tr>";
    $fields = getFieldNames($mysqli);
    foreach ($fields as $field)
    {
        echo "<th>$field</th>";
    }
    echo "<th colspan='2'>";
    showNewButton($mysqli);
    echo "</th>";
    echo "</tr>";
}

function fillTableWithData($mysqli)
{
    $table = $_POST['table'];
    $result = $mysqli->execute_query("SELECT * FROM $table");
    if (!$result)
    {
        showMessage("Hiba: Nem sikerült lekérni minden adatot!", true);
        return;
    }
    $data = $result->fetch_all();
    foreach ($data as $row)
    {
        echo "<tr>";
        foreach ($row as $column)
        {
            echo "<td>$column</td>";
        }
        $id = $row[0];
        echo "<td><button class='normal' type='submit' name='change' value='$table;$id'>Módosít</button></td>";
        echo "<td><button class='normal' type='submit' name='delete' value='$table;$id'>Töröl</button></td>";
        echo "</tr>";
    }
}

function tableEnd()
{
    echo "</table>";
}
