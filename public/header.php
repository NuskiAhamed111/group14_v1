<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></title>
    <style>
/* Reset some default styles */
body, h1, p {
    margin:0;
    padding-left:20px;
    padding-bottom: 70px;
}

/* Style for the body */
body {
    font-family: Arial, sans-serif; /* Use a clean font */
    line-height: 1.6; /* Improve readability */
    background-color: #f4f4f4; /* Light background color */
    padding-left:20px;
    padding-top: 120px; /* Add enough padding to prevent content from hiding under the fixed header */
}

/* Header styling */
header {
    background:#3d0ec4; /* Header background color */
    color: #fff; /* Text color */
    padding: 15px 20px; /* Padding around header */
    position: fixed; /* Fix the header at the top */
    width: 100%; /* Full width across screen */
    left: 0;
    top: 0; /* Position at the very top */
    z-index: 1000; /* Ensure it stays above other elements */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Optional: add subtle shadow */
}

/* Navigation styling */
nav {
    display: flex; /* Flexbox for alignment */
    justify-content: space-between; /* Space between items */
    align-items: center; /* Center items vertically */
}

nav ul {
    list-style-type: none; /* Remove bullets */
    display: flex; /* Display links in a row */
    margin: 0;
    padding: 0;
}

nav ul li {
    margin: 0 15px; /* Space between links */
    margin-left:20px;
    margin-right:50px;
}

nav ul li a {
    color: #fff; /* Link text color */
    text-decoration: none; /* Remove underline */
    font-weight: bold; /* Make text bold */
    transition: color 0.3s; /* Smooth color transition */
    white-space: nowrap; /* Ensure that text doesn't break or wrap */
}

/* Hover effect for links */
nav ul li a:hover {
    color: #ffdd57; /* Change color on hover */
}

/* Responsive adjustments */
@media (max-width: 768px) {
    nav ul {
        flex-direction: column; /* Stack items on smaller screens */
        align-items: center; /* Center items */
    }
    
    nav ul li {
        margin: 10px 0; /* Space between stacked links */
    }
}

/* Logo styling */
.logo img {
    max-width: 100%; /* Ensure logo is responsive */
    height: auto; /* Maintain aspect ratio */
    white-space: nowrap;
}
</style>


</head>
<body>
    <header>
        <nav>
        <div class="logo">
        <a href="<?php echo htmlspecialchars($dashboard_path); ?>">
                    <img src="../image/logo.jpg" alt="Logo" width="300" height="auto"> <!-- Adjust the path and size as necessary -->
                </a>
            </div>
            <ul>
                
                <li><a href="../public/profile.php"><?php echo isset($username) ? $username : 'Profile'; ?></a></li>

            <li><a href="../public/logout.php">Logout</a></li>
                <!-- Add other navigation items as needed -->
            </ul>
        </nav>
    </header>
</body>