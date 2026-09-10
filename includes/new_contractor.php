<?php
session_start();
include 'connection.php'; // adjust path if needed

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $page            = $_POST['page'] ?? '';

    // Get form data
    $name            = $_POST['contractorName'] ?? '';
    $email           = $_POST['contractorEmail'] ?? '';
    $contact_number  = $_POST['contractorContact'] ?? '';
    $contact_person  = $_POST['contractorPic'] ?? '';
    $address         = $_POST['contractorAddress'] ?? '';

    // Optional fields
    $uid    = $_SESSION['uid'] ?? NULL;
    $add_by = $_SESSION['uid'] ?? NULL;

    // Basic validation
    if (empty($name) || empty($email) || empty($contact_number) || empty($contact_person) || empty($address)) {

        $_SESSION['status'] = 'warning';
        $_SESSION['message'] = 'Please fill in all required fields.';

        header("Location: ../new_contractor.php");
        exit();
    }

    // Prepare SQL
    $stmt = $conn->prepare("INSERT INTO contractor 
        (name, email, contact_person, contact_number, address) 
        VALUES (?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("Penyediaan pertanyaan gagal: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "sssss",
        $name,
        $email,
        $contact_person,
        $contact_number,
        $address,
    );

    // Execute
    if ($stmt->execute()) {
        $_SESSION['status'] = 'success';
        $_SESSION['message'] = 'Kontraktor berjaya ditambah.';

        if ($page == 'con') {
            header("Location: ../contractor_list.php");
        } else {
            header("Location: ../new_contractor.php");
        }
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Ralat pangkalan data: ' . $stmt->error;
    }

    // Close
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../index.php");
    exit();
}
