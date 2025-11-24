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


$sql = "SELECT P_CODE, P_DESCRIPT, P_INDATE, P_QOH, P_MIN, P_PRICE, P_DISCOUNT, V_CODE FROM PRODUCT";
$result = $conn->query($sql);
//$conn->close(); //close the connection to not leak memory
?>


    <!DOCTYPE html>
    <html lang="en" data-bs-theme="dark">
    <head>
        <meta charset="UTF-8">
        <title>Product Table</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    </head>

    <nav class="navbar navbar-expand-lg bg-body-tertiary"> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
        <a href="unauthenticated_dashboard.php" class="navbar-brand text-decoration-underline">Dashboard</a> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
        <a href="register.php" class="navbar-brand text-decoration-underline">Register</a> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
    </nav>
    <body>
    <main class="container mt-5">
        <h1 class="text-center">Product Table</h1>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
            <tr>
                <th>P_DESCRIPT</th>
                <th>P_QOH</th>
                <th>P_PRICE</th>
                <th>P_DISCOUNT</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['P_DESCRIPT']) ?></td>
                        <td><?= htmlspecialchars($row['P_QOH']) ?></td>
                        <td>$<?= htmlspecialchars($row['P_PRICE']) ?></td>
                        <td><?= htmlspecialchars($row['P_DISCOUNT']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No products found</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
    </html>

<?php
/*$conn->close();  TODO*/