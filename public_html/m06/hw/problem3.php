<?php
require_once(__DIR__ . "/base.php");

$a1_users = [
    ["userId" => 1, "name" => "Alice", "age" => 28],
    ["userId" => 2, "name" => "Bob", "age" => 34]
];

$a2_users = [
    ["userId" => 3, "name" => "Charlie", "age" => 22],
    ["userId" => 4, "name" => "Diana", "age" => 29]
];

$a3_users = [
    ["userId" => 5, "name" => "Eve", "age" => 31],
    ["userId" => 6, "name" => "Frank", "age" => 26]
];

$a4_users = [
    ["userId" => 7, "name" => "Grace", "age" => 25],
    ["userId" => 8, "name" => "Hank", "age" => 30]
];

$a1_activities = [
    ["userId" => 1, "activity" => "Running"],
    ["userId" => 2, "activity" => "Swimming"]
];

$a2_activities = [
    ["userId" => 3, "activity" => "Cycling"],
    ["userId" => 4, "activity" => "Hiking"]
];

$a3_activities = [
    ["userId" => 5, "activity" => "Climbing"],
    ["userId" => 6, "activity" => "Skiing"]
];

$a4_activities = [
    ["userId" => 7, "activity" => "Diving"],
    ["userId" => 8, "activity" => "Surfing"]
];

function joinArrays($users, $activities) {
    printProblemMultiData($users, $activities);
    echo "<br>Joined output:<br>";

    // Use the $users and $activities parameters. Do not directly read $a1-$a4 arrays inside this function.
    // TODO: Add your UCID, date, and planning comments before writing the final solution.
    // TODO Objective: Join both arrays on the userId property into one $joined array.
    $joined = [];
    // Start edits

    // End edits
    echo "<pre>" . var_export($joined, true) . "</pre>";
}

$ucid = "YOUR_UCID_HERE";
printHeader($ucid, 3);
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
            <td><?php joinArrays($a1_users, $a1_activities); ?></td>
            <td><?php joinArrays($a2_users, $a2_activities); ?></td>
            <td><?php joinArrays($a3_users, $a3_activities); ?></td>
            <td><?php joinArrays($a4_users, $a4_activities); ?></td>
        </tr>
    </tbody>
</table>
<?php printFooter($ucid, 3); ?>
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
