<?php
// Start session and include necessary files
session_start();
include 'db.php';

// Initialize the message variables
$message_title = '';
$message_text = '';
$message_type = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $year = $_POST['year'];
    $department = $_POST['department'];
    $targets = $_POST['targets'];
    $total_budget = $_POST['totalBudget'];
    $initiative = $_POST['initiative'];

    // Check if user_id is set in session
    if (!isset($_SESSION['user_id'])) {
        die("User ID not set in session.");
    }
    $user_id = $_SESSION['user_id']; // Assuming 'user_id' is stored in the session

    $sql = "INSERT INTO goal (title, year, department, targets, total_budget, initiative, user_id)
            VALUES ('$title', '$year', '$department', '$targets', '$total_budget', '$initiative', '$user_id')";

    if ($conn->query($sql) === TRUE) {
        // Success message
        $message_title = 'Success!';
        $message_text = 'Goal created successfully.';
        $message_type = 'success';
    } else {
        // Error message
        $message_title = 'Error!';
        $message_text = 'Error creating goal: ' . $conn->error;
        $message_type = 'error';
    }

    $conn->close();
}
?>

<?php if (!empty($message_title) && !empty($message_text) && !empty($message_type)): ?>
<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center;">
    <div style="background-color: #fff; width: 80%; max-width: 600px; padding: 20px; border-radius: 5px; text-align: center; position: relative;">

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '<?php echo $message_type; ?>',
                    title: '<?php echo $message_title; ?>',
                    text: '<?php echo $message_text; ?>'
                }).then((result) => {
                    if (result.isConfirmed || result.isDismissed) {
                        window.location.href = '../html/ManageGoals.php';
                    }
                });
            });
        </script>

    </div>
</div>
<?php endif; ?>
