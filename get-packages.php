<?php

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "construction_db"
);

$sql =
"SELECT * FROM construction_packages
 WHERE status = 1";

$result = $conn->query($sql);

$packages = [];

while($row = $result->fetch_assoc()){

    $packages[] = $row;
}

echo json_encode($packages);
?>