<?php
// user_management.php

session_start();
include '../config/db.php';
include '../public/header.php';

// Check if the user is staff
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    echo "<p class='error-msg'>Access Denied. Only staff members can access this page.</p>";
    exit;
}

// Fetch users grouped by role
$roles = ['student', 'mentor', 'staff'];
$user_data = [];

foreach ($roles as $role) {
    $stmt = $conn->prepare("SELECT id, username, full_name, role FROM users WHERE role = ?");
    $stmt->bind_param('s', $role);
    $stmt->execute();
    $user_data[$role] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        /* General Page Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h2, h3 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }

        /* Table Styles */
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        thead {
            background-color: #4CAF50;
            color: white;
        }

        thead th {
            padding: 12px;
            font-size: 18px;
            text-align: left;
        }

        tbody tr {
            border-bottom: 1px solid #ddd;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        tbody td {
            padding: 10px;
            font-size: 16px;
            text-align: left;
        }

        /* Error Message Styling */
        .error-msg {
            text-align: center;
            color: red;
            font-weight: bold;
            margin-top: 20px;
        }

        /* Button for User Navigation */
        button {
            display: block;
            width: 150px;
            margin: 10px auto;
            padding: 10px;
            font-size: 16px;
            color: #fff;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            table {
                width: 100%;
            }

            thead th, tbody td {
                font-size: 14px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <h2>User Management</h2>

    <?php foreach ($user_data as $role => $users): ?>
        <h3><?php echo ucfirst($role) . 's'; ?></h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No users found in this role.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <!-- Back Button -->
    <button onclick="goBack()">Back</button>

    <script>
        // Adjust body padding to account for the header height dynamically
        window.addEventListener('load', function() {
            const headerHeight = document.querySelector('header').offsetHeight;
            document.body.style.paddingTop = headerHeight + 'px';
        });
         // Go back to the previous page
         function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>

<?php include '../public/footer.php'; ?>
