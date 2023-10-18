<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            padding-top: 100px;
        }

        .login-container {
            background-color: #ffffff;
            max-width: 400px;
            margin: auto;
            padding: 40px 50px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 28px;
            text-align: center;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="password"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: #ffffff;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <?php
    if (isset($_GET['error']) && $_GET['error'] == 1) {
        echo '<div class="error-message">Login failed. Please try again.</div>';
    }
    ?>

    <form action="do-login.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        
        <input type="submit" value="Login">
    </form>
</div>

</body>
</html>
