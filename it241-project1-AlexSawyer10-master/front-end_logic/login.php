<?php
require_once "db_cred/db_cred.inc";
/**
 * @var mysqli $conn
 */

if($conn->connect_error)
{
    die("Connection failed: " . $conn->connect_error);
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
        $stmt = $conn->prepare("SELECT USER_ID, USER_PASSWORD FROM USER WHERE USER_USERNAME = ?");
        if($stmt === false)
        {
            $formErrors[] = "Database error: failed to prepare the statement";
        }
        else
        {
            $stmt->bind_param("s",$username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1)
            {
                $stmt->bind_result($user_id, $hashedPassword);
                $stmt->fetch();

                if(password_verify($password, $hashedPassword))
                {
                    session_start();

                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_username'] = $username;

                    $successMessage = "Login successful! You can now go to the dashboard page!";

                    header('Location: dashboard.php');
                    exit;
                }
                else
                {
                    $formErrors[] = "Invalid password.";
                }
            }
            else
            {
                $formErrors[] = "No account found with those credentials.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log In Page </title>
    <!--bootStrap here-->  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!--css here--> <link href="css/login.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary"> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
    <a href="register.php" class="navbar-brand text-decoration-underline">Register</a> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
</nav>

<main>
    <div class="card shadow-sm bg-body-secondary">
        <div class="card-body">
            <div class="text-center">
                <h1 class="h3 mb-4">Login</h1>

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

                            <br>
                            <h6>Not registered?</h6>
                            <a href="register.php" class="register btn mb-1 text-decoration-underline">Register</a>
                            <h4>-</h4>
                            <a href="unauthenticated_dashboard.php" class="register btn mb-1 text-decoration-underline">Continue as Guest</a>  <!--TODO --><!--Link dashboard page -->

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Log in</button>

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
