<?php
require 'config.php';
if (!isset($_GET['user_id'])) {
exit("No user ID provided");
}
$user_id = $_GET['user_id'];
$stmt = $pdo->prepare("SELECT * FROM verification_requests WHERE user_id = ?");
$stmt->execute([$user_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$request) {
echo "No verification request found.";
exit;
}
echo "<p><strong>Full Name:</strong> {$request['full_name']}</p>";
echo "<p><strong>Birthdate:</strong> {$request['birthdate']}</p>";
echo "<p><strong>Status:</strong> {$request['status']}</p>";
if (!empty($request['rejection_reason'])) {
echo "<p><strong>Rejection Reason:</strong> {$request['rejectionreason']}</p>";
}
echo "<p><strong>Attachments:</strong></p>";
for ($i = 1; $i <= 3; $i++) {
$field = "attachment" . $i;
if (!empty($request[$field])) {
echo "<a href='{$request[$field]}' target='_blank'>
 <img src='{$request[$field]}' style='max-width:100px; margin:5px; border:1px solid #ccc;'>
 </a>";
 }
}
?>