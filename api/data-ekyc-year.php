<?php

header('Content-Type: application/json');

$servername = "127.0.0.1";
$username = "root";
$password = "12345678";
$dbname = "chart_example";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// รับค่า year จาก query parameter
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// ดึงข้อมูลจากตาราง ekyc_detail เฉพาะปีที่เลือก
$sql = "SELECT DATE_FORMAT(date_ekyc, '%m') as month, COUNT(DISTINCT mem_id) as count
        FROM ekyc_detail
        WHERE YEAR(date_ekyc) = ?
        GROUP BY month
        ORDER BY month";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $year);
$stmt->execute();
$result = $stmt->get_result();

$monthly_counts = [];
$months = ["01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม", "04" => "เมษายน", 
           "05" => "พฤษภาคม", "06" => "มิถุนายน", "07" => "กรกฎาคม", "08" => "สิงหาคม",
           "09" => "กันยายน", "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"];

while ($row = $result->fetch_assoc()) {
    $monthly_counts[$months[$row['month']]] = $row['count'];
}
$conn->close();

// เตรียมข้อมูลสำหรับ Chart.js
$labels = array_keys($monthly_counts);
$values = array_values($monthly_counts);

$response = [
    "labels" => $labels,
    "datasets" => [
        [
            "label" => "จำนวนสมาชิกที่ทำ E-KYC ต่อเดือนในปี",
            "backgroundColor" => "rgba(35,137,231,0.7)",
            "borderColor" => "rgba(35,137,231,1)",
            "borderWidth" => 1,
            "data" => $values
        ]
    ]
];

echo json_encode($response);
?>
