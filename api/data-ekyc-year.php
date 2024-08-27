

<?php

header('Content-Type: application/json');

$servername = "DEPTSRV";
$username = "sa";
$password = "P@ssw0rd99";
$dbname = "tg_coop_fly";

$connectionInfo = array("Database" => $dbname, "UID" => $username, "PWD" => $password);
$conn = sqlsrv_connect($servername, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$sql = "SELECT DATEPART(MONTH, date_ekyc) as month,  
         COUNT(DISTINCT CASE WHEN max_address IN ('TG01','TG02','TG07','TG09','TG11','TG13','TG14','TG15','TG17') THEN mem_id ELSE NULL END) as group1,
         COUNT(DISTINCT CASE WHEN max_address IN ('TG06','TG24') THEN mem_id ELSE NULL END) as group2,
         COUNT(DISTINCT CASE WHEN max_address IN ('TG03','TG04','TG05','TG08','TG10','TG12','TG16','TG18','TG19','TG20','TG21','TG22','TG23','TG25','TG26') 
         THEN mem_id ELSE NULL END) as group3
  FROM ekyc_detail
  WHERE YEAR(date_ekyc) = ?
  GROUP BY DATEPART(MONTH,date_ekyc)
  ORDER BY DATEPART(MONTH,date_ekyc)";



$params = array($year);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$monthly_counts = ["group1" => [], "group2" => [], "group3" => []];
$months = ["01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม", "04" => "เมษายน", 
           "05" => "พฤษภาคม", "06" => "มิถุนายน", "07" => "กรกฎาคม", "08" => "สิงหาคม",
           "09" => "กันยายน", "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"];

$total_count = 0; 

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $month = str_pad($row['month'], 2, '0', STR_PAD_LEFT);
    $month_name = $months[$month];
    $monthly_counts['group1'][$month_name] = $row['group1'];
    $monthly_counts['group2'][$month_name] = $row['group2'];
    $monthly_counts['group3'][$month_name] = $row['group3'];

 
    $total_count += $row['group1'] + $row['group2'] + $row['group3'];
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

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
    "total_count" => $total_count  // Sending the total count to the JavaScript
];

echo json_encode($response);
?>
