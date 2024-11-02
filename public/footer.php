<?php
// Start of the footer.php file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Ensure the body and html take up full height */
        html, body {
            height: 100%; /* Full height */
            margin: 0; /* Remove default margin */
            display: flex; /* Use Flexbox */
            flex-direction: column; /* Align children in a column */
        }

        /* Main content area */
        .content {
            flex: 1; /* Take up remaining space */
        }

        /* Ensure footer stays at the bottom and is fixed */
footer {
flex-shrink: 0; 
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 60px; /* Adjust height as needed */
    background-color: white;
    color: black;    
    text-align: center;
    line-height: 60px; /* Vertically centers the text */
}

/* Add bottom padding or margin to the body/main content */
body, main {
    padding-bottom: 120px; /* Must be equal to or greater than footer height */
}

        footer p {
            margin: 0; /* Remove default margin */
        }

        footer nav {
            margin-top: 10px; /* Add some space above the nav */
        }

        footer nav ul {
            list-style-type: none; /* Remove bullet points */
            padding: 0; /* Remove padding */
            margin: 0; /* Remove margin */
            display: inline-block; /* Center the list */
        }

        footer nav ul li {
            display: inline; /* Display links in a line */
            margin: 0 15px; /* Add margin between links */
        }

        footer nav ul li a {
            text-decoration: none; /* Remove underline */
            color: blue; /* Link color */
        }

        footer nav ul li a:hover {
            text-decoration: underline; /* Underline on hover */
        }
    </style>
</head>
<body>
    <div class="content">
        <!-- Your main content goes here -->
    </div>
    
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Industrial Diary Management System. All rights reserved.</p>
        <nav>
            <ul>
                <li><a href="privacy_policy.php">Privacy Policy</a></li>
                <li><a href="terms_of_service.php">Terms of Service</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </nav>
    </footer>
</body>
</html>
