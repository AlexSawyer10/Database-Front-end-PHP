<?php
session_start();
//The dashboard must contain clear links to the PRODUCT and VENDOR pages.
if(!isset($_SESSION['username']) && !isset($_SESSION['user_id']))
{

    header('Location: logout.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Page</title>

    <!--bootStrap here-->  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!--css here--> <link href="css/dashboard.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary"> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
    <a href="logout.php" class="navbar-brand text-decoration-underline">Logout</a> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
</nav>
<main>
    <div class="text-center">
    <h1 class="text-decoration-underline mb-3">Dashboard Page</h1>

    <h2 class="my-5">Continue to the Product page here -> <a href="product.php" class="product btn mb-1 text-decoration-underline">Product</a></h2>

    <h2 class="my-5">Continue to the Vendor page here -> <a href="vendor.php" class="product btn mb-1 text-decoration-underline">Vendor</a></h2>

    </div>
</main>


<!--bootStrap here--> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
