<?php
echo("hello");
exit();
$searchTerm = $_GET['term'];

    $sql = mysql_query ("SELECT name_first, employee_id, unique_id, name_last FROM hr_employees WHERE name_first LIKE '{$searchTerm}%' OR name_first LIKE '{$searchTerm}%' OR employee_id LIKE '{$searchTerm}%'");
    $array = array();
    while ($row = mysql_fetch_array($sql)) {
        $array[] = array (

            'value' => $row['name_first'].' '.$row['name_last'].' ('.$row['employee_id'].')',

        );
    }
    //RETURN JSON ARRAY
    echo json_encode ($array);
	?>