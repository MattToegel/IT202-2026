<?php
require_once(__DIR__ . "/base.php");

$a1 = [
    ["id" => 1, "make" => "Toyota", "model" => "Camry", "year" => 2010],
    ["id" => 2, "make" => "Honda", "model" => "Civic", "year" => 2005]
];

$a2 = [
    ["id" => 3, "make" => "Ford", "model" => "Mustang", "year" => 1995],
    ["id" => 4, "make" => "Chevrolet", "model" => "Impala", "year" => 2000]
];

$a3 = [
    ["id" => 5, "make" => "Nissan", "model" => "Altima", "year" => 2015],
    ["id" => 6, "make" => "BMW", "model" => "3 Series", "year" => 2018]
];

$a4 = [
    ["id" => 7, "make" => "Mercedes", "model" => "C Class", "year" => 2011],
    ["id" => 8, "make" => "Audi", "model" => "A4", "year" => 1990]
];

function processCars($cars) {
    printProblemData($cars);
    echo "<br>New Properties Output:<br>";

    // Use the $cars parameter. Do not directly read $a1, $a2, $a3, or $a4 inside this function.
    // TODO: Add your UCID, date, and planning comments before writing the final solution.
    // TODO Objective: Create $processedCars with the original properties plus age and isClassic.
    $currentYear = null;
    $processedCars = [];
    $classic_age = 25;
    // Start edits

    // End edits
    echo "<pre>" . var_export($processedCars, true) . "</pre>";
}

$ucid = "YOUR_UCID_HERE";
printHeader($ucid, 2);
?>
<table>
    <thead>
        <tr>
            <th>A1</th>
            <th>A2</th>
            <th>A3</th>
            <th>A4</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?php processCars($a1); ?></td>
            <td><?php processCars($a2); ?></td>
            <td><?php processCars($a3); ?></td>
            <td><?php processCars($a4); ?></td>
        </tr>
    </tbody>
</table>
<?php printFooter($ucid, 2); ?>
<style>
    table {
        border-spacing: 1em 3em;
        border-collapse: separate;
    }

    td {
        border-right: solid 1px black;
        border-left: solid 1px black;
        vertical-align: top;
    }
</style>
