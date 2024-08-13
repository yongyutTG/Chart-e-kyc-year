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

$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$sql = "SELECT DATE_FORMAT(date_ekyc, '%m') as month,  
         COUNT(DISTINCT CASE WHEN max_address IN ('TG01', 'TG02', 'TG03', 'TG04', 'TG05', 'TG06', 'TG07', 'TG08', 'TG09', 'TG10')  THEN mem_id ELSE NULL END) as group1,
         COUNT(DISTINCT CASE WHEN max_address IN ('TG11', 'TG12', 'TG13', 'TG14', 'TG15', 'TG16', 'TG17', 'TG18') THEN mem_id ELSE NULL END) as group2,
         COUNT(DISTINCT CASE WHEN max_address IN ('TG19', 'TG20', 'TG21', 'TG22', 'TG23', 'TG24', 'TG25') THEN mem_id ELSE NULL END) as group3
  FROM ekyc_detail
  WHERE YEAR(date_ekyc) = ?
  GROUP BY month
  ORDER BY month";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $year);
$stmt->execute();
$result = $stmt->get_result();

$monthly_counts = ["group1" => [], "group2" => [], "group3" => []];
$months = ["01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม", "04" => "เมษายน", 
           "05" => "พฤษภาคม", "06" => "มิถุนายน", "07" => "กรกฎาคม", "08" => "สิงหาคม",
           "09" => "กันยายน", "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"];

$total_count = 0;  // ตัวแปรสำหรับเก็บยอดรวมทั้งหมด

while ($row = $result->fetch_assoc()) {
    $month = $months[$row['month']];
    $monthly_counts['group1'][$month] = $row['group1'];
    $monthly_counts['group2'][$month] = $row['group2'];
    $monthly_counts['group3'][$month] = $row['group3'];

    // เพิ่มจำนวนสมาชิกในแต่ละกลุ่มเข้ากับยอดรวมทั้งหมด
    $total_count += $row['group1'] + $row['group2'] + $row['group3'];
}

$conn->close();

$labels = array_keys($monthly_counts['group1']);
$response = [
    "labels" => $labels,
    "datasets" => [
        [
            "label" => "สำนักงานลาดพร้าว",
            "backgroundColor" => "rgba(75,192,192,0.7)",
            "borderColor" => "rgba(75,192,192,1)",
            "data" => array_values($monthly_counts['group1'])
        ],
        [
            "label" => "สำนักงานดอนเมือง",
            "backgroundColor" => "rgba(153,102,255,0.7)",
            "borderColor" => "rgba(153,102,255,1)",
            "data" => array_values($monthly_counts['group2'])
        ],
        [
            "label" => "สำนักงานกิ่งแก้ว",
            "backgroundColor" => "rgba(255,159,64,0.7)",
            "borderColor" => "rgba(255,159,64,1)",
            "data" => array_values($monthly_counts['group3'])
        ]
    ],
    "total_count" => $total_count  // ส่งยอดรวมทั้งหมดไปยัง JavaScript
];

echo json_encode($response);
?>
