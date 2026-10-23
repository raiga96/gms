<?php

header('Content-Type: application/json');

require 'conn.php';

// Initialize response data array
$data = array();

// 1. Validate request parameters
$cid = isset($_GET['cid']) ? trim($_GET['cid']) : '';

if (empty($cid)) {
    $data[] = ["error" => "Parameter 'cid' is required"];
    echo json_encode($data);
    exit;
}

// 2. Use prepared statements to prevent SQL injection
$sql = "SELECT * FROM contract AS a 
        JOIN contractor AS b ON b.cid = a.cid 
        JOIN subzone AS c ON c.sz_id = a.sz_id 
        JOIN zone AS d ON d.zone_id = c.zone_id 
        WHERE a.contract_id = ?";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind parameter (assuming contract_id is a string or number, bound as string "s" for safety)
    $stmt->bind_param("s", $cid);
    
    // Execute query
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        } else {
            $data[] = ["error" => "No data found"];
        }
    } else {
        $data[] = ["error" => "Execution failed: " . $stmt->error];
    }
    $stmt->close();
} else {
    $data[] = ["error" => "Preparation failed: " . $conn->error];
}

// 3. Close the database connection safely
$conn->close();

// 4. Encode the data in JSON format and send the response
echo json_encode($data);
