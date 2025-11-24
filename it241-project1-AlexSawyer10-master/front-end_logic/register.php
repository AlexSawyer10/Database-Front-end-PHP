<?php
require_once  "db_cred/db_cred.inc";
// database connection settings
/**
 * @var mysqli $conn
 */

if ($conn->connect_error)
{
    die("Connection failed: " . $conn->connect_error);
}

if(session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

$submittedFormData = [
        'username' => '',
        'password' => '',
];
$formErrors = [];

function escapeForHtml($input)
{
    return htmlspecialchars($input ?? '', ENT_QUOTES); //the ?? is null checking, ENT_QUOTES translates it to its html entities
}

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $username = htmlentities($_POST['username']);
    $password = htmlentities($_POST['password']);

    if(empty($username))
    {
        $formErrors[] = "Username cannot be empty"; // remember my database checks for unique usernames already
    }

    if(empty($password))
    {
        $formErrors[] = "Password cannot be empty";
    }

    if(empty($formErrors))
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT); //bcrypt generates salt already for you
        try
        {
            $stmt = $conn->prepare("INSERT INTO USER (USER_USERNAME,USER_PASSWORD) VALUES(?,?)"); // insert new user into db

            if ($stmt === false)
            {
                $formErrors[] = "Database error: failed to prepare the statement";
            }
            else
            {
                $stmt->bind_param("ss", $username, $passwordHash);

                $stmt->execute(); // This will throw an exception if it fails
                $successMessage = "Registration successful! You can now go to the login page!";

                $stmt->close();
            }
        }
        catch(Exception $e)
        {
            if($e->getCode() == 1062)
            {
                $formErrors[] = htmlentities($username) . " is already registered.";
            }
            else
            {
                $formErrors[] = "Error:" . $e->getMessage();
            }
        }



    }
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Page</title>
<!--bootStrap here-->  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<!--css here--> <link href="css/register.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary"> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
    <a href="login.php" class="navbar-brand text-decoration-underline">Login</a> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
</nav>
<main>
    <div class="card shadow-sm bg-body-secondary">
        <div class="card-body">
            <div class="text-center">
                <h1 class="h3 mb-4">Register</h1>

                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label required">
                            <strong>Username</strong>
                        </label>
                        <input
                                type="text"
                                class="form-control"
                                id="username"
                                name="username"
                                placeholder="ex: Xx_alexSawyer_xX"
                                value="<?= escapeForHtml($submittedFormData['username']) ?>"
                                required
                        />
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label required">
                            <strong>Password</strong>
                            <br>
                        </label>
                        <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="ex: secret_Password!"
                                value="<?= escapeForHtml($submittedFormData['password']) ?>"
                                required
                        />
                    <?php if(!empty($formErrors)):
                    foreach($formErrors as $error) :?>
                        <div class="alert alert-danger">
                            <h6>Errors in your submission: <?= $error ?></h6>
                        </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if(empty($formErrors) && ($_SERVER["REQUEST_METHOD"] == "POST")): ?>
                        <div class="alert alert-success">
                            <h6><?= $successMessage ?> </h6>
                    <?php endif; ?>
                <br>
                    <h6>Already registered?</h6>
                    <a href="login.php" class="login btn mb-1 text-decoration-underline">Login</a>
                    <h4>-</h4>
                    <a href="unauthenticated_dashboard.php" class="login btn mb-1 text-decoration-underline">Continue as Guest</a>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Register</button>

                        <button type="reset" class="btn-outline-secondary">Reset</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</main>


<footer>Designed by Alex Sawyer ©</footer>

<!--bootStrap here--> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>


</html>
