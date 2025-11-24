<?php
require_once "db_cred/db_cred.inc";
/**
 * @var mysqli $conn
 */

session_start();

if(!isset($_SESSION['username']) && !isset($_SESSION['user_id']))
{
    header('Location: logout.php');
}

if ($_SERVER["REQUEST_METHOD"] == 'POST')
{

     if(isset($_POST["add-row"]))
     {
        $random_PK = bin2hex(random_bytes(5)); // gets random bytes with a length of 8, random bytes gives binary data so need to convert  it to a string . Refer back if confused: https://www.php.net/manual/en/function.bin2hex.php , https://www.php.net/manual/en/function.random-bytes.php

        $stmt = $conn->prepare("INSERT INTO PRODUCT (P_CODE,P_DESCRIPT,P_INDATE,P_QOH,P_MIN,P_PRICE,P_DISCOUNT,V_CODE)
        VALUES (?,'',CURDATE(),0,0,0.00,0.00,NULL)");
        $stmt->bind_param('s',$random_PK);

         if ($stmt->execute() === false)
         {
             $success = false;
             header('Location: product.php?error=1'); //resets page so then it can go fetch the new row from the data base then display it
             exit();
         }
         else
         {
             header('Location: product.php?success=1'); //resets page so then it can go fetch the new row from the data base then display it
             exit(); // remember this leaves entire php block
         }
    }

    if(isset($_POST['delete']))
    {
        $p_code = htmlentities($_POST['P_CODE']);

        $stmt = $conn->prepare("SELECT COUNT(*) AS TOTAL FROM LINE WHERE P_CODE = ?"); // remember u have to reference the alias to get the number of rows, if i referenced P_CODE it woulnd't exist because thats not what the query returns.
        $stmt->bind_param('s',$p_code);
        $stmt->execute();
        $result = $stmt->get_result(); // remember this gets the result from the query REMEMBER AS A RESULT OBJECT NOT AS THE ACTUAL VALUE. Reference for confusion https://www.php.net/manual/en/mysqli-stmt.get-result.php?utm_source=chatgpt.com
        $numberOfRows = $result->fetch_assoc(); // need this to fetch the actual data from get_result(); so I can then later compare the number of rows it has. It returns it as an associative array so the key in this case would be TOTAL. Reference for confusion https://www.php.net/manual/en/mysqli-result.fetch-assoc.php?utm_source=chatgpt.com
        $stmt->close();

        if($numberOfRows['TOTAL'] > 0)
        {
            error_log("Cannot delete this because it already exists in a LINE.");
            header('Location: product.php?error=1');

            exit(); // remember this leaves entire php block
        }

        $stmt = $conn->prepare("DELETE FROM PRODUCT WHERE P_CODE = ?");
        $stmt->bind_param('s',$p_code);

        $success = true;

        if ($stmt->execute() === false)
        {
            $success = false;
            error_log('Error updating record ' . $conn->error);
        }
        $stmt->close();

        if($success === true)
        {
            header('Location: product.php?success=1');
        }
        else
        {
            header('Location: product.php?error=1');
        }

        exit();
    }

    if(isset($_POST['save']))
    {
        $p_code = htmlentities($_POST['P_CODE']);
        $p_descript = htmlentities($_POST['P_DESCRIPT']);
        $p_indate = htmlentities($_POST['P_INDATE']);
        $p_qoh = htmlentities($_POST['P_QOH']);
        $p_min = htmlentities($_POST['P_MIN']);
        $p_price = htmlentities($_POST['P_PRICE']);
        $p_discount = htmlentities($_POST['P_DISCOUNT']);
        $v_code = htmlentities($_POST['V_CODE']);

        if(empty($v_code))
        {
            $v_code = NULL;
        }

        if($v_code !== NULL)
        {
            $stmt = $conn->prepare ("SELECT COUNT(*) AS TOTAL FROM VENDOR WHERE V_CODE = ?"); // remember checking to see if a valid V_CODE is put in to throw an error
            $stmt->bind_param('s',$v_code);
            $stmt->execute();
            $result = $stmt->get_result();
            $numberOfRows = $result->fetch_assoc();
            $stmt->close();

            if($numberOfRows['TOTAL'] == 0)
            {
                error_log("Not a valid vendor code, it doesn't exist");
                header('Location: product.php?error=1');
                exit(); //remember exit the php block
            }
        }

        // idk how to validate date

        if(!is_numeric($p_qoh))
        {
            error_log("Not a valid QOH");
            header('Location: product.php?error=1');
            exit(); //remember exit the php block
        }
        if(!is_numeric($p_min))
        {
            error_log("Not a valid min");
            header('Location: product.php?error=1');
            exit();
        }
        if(!is_numeric($p_price))
        {
            error_log("Not a valid price");
            header('Location: product.php?error=1');
            exit();
        }
        if(!is_numeric($p_discount))
        {
            error_log("Not a valid discount");
            header('Location: product.php?error=1');
            exit();
        }
        if(!is_numeric($v_code))
        {
            error_log("Not a valid vendor code");
            header('Location: product.php?error=1');
            exit();
        }

        $stmt = $conn->prepare("UPDATE PRODUCT SET P_DESCRIPT = ?, P_INDATE = ?, P_QOH = ?, P_MIN = ?, P_PRICE = ?, P_DISCOUNT = ?, V_CODE = ? WHERE P_CODE = ?");
        $stmt->bind_param('ssiiddis', $p_descript, $p_indate, $p_qoh, $p_min, $p_price, $p_discount, $v_code, $p_code);

        $success = true;


        if ($stmt->execute() === false)
        {
            $success = false;
            error_log('Error updating record ' . $conn->error);
        }
        $stmt->close();

        if($success === true)
        {
            header('Location: product.php?success=1');
        }
        else
        {
            header('Location: product.php?error=1');
        }

        exit();
    }

}

$sql = "SELECT P_CODE, P_DESCRIPT, P_INDATE, P_QOH, P_MIN, P_PRICE, P_DISCOUNT, V_CODE FROM PRODUCT"; // remember need these for display
$result = $conn->query($sql); // needed for display

$conn->close(); //close the connection to not leak memory
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Product</title>

    <!--bootStrap here-->  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!--CSS here--><link rel="stylesheet" href="css/product.css">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-body-tertiary"> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
    <a href="dashboard.php" class="navbar-brand text-decoration-underline">Dashboard</a> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
    <a href="logout.php" class="navbar-brand text-decoration-underline">Logout</a> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->

</nav>

<main class="container mt-3">
    <h1 class="text-center">Editable Product Table</h1>

    <!-- display success or error message -->
    <?php if (isset($_GET['success'])) : ?>
        <div class="alert alert-success">Record Updated successfully</div>
    <?php elseif (isset($_GET['error'])) : ?>
        <div class="alert alert-danger">Record Update failed</div>
    <?php endif; ?>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
        <tr>
            <th>P_CODE</th>
            <th>P_DESCRIPT</th>
            <th>P_INDATE</th>
            <th>P_QOH</th>
            <th>P_MIN</th>
            <th>P_PRICE</th>
            <th>P_DISCOUNT</th>
            <th>V_CODE</th>
            <th>Update</th>
            <th>Delete</th>
        </tr>
        </thead>
        <tbody>
        <div class="center">
            <form method="post">
            <button class="product" type="submit" name="add-row" id="add-row">Add row</button>
            </form>
        </div>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <input type="text" name="P_CODE" class="form-control" value="<?= $row['P_CODE'] ?>" form="<?= $row['P_CODE']?>" readonly /> <!--remember read only cuz u don't want them to change the code #-->
                    </td>
                    <td>
                        <input type="text" name="P_DESCRIPT" class="form-control" value="<?= $row['P_DESCRIPT'] ?>" form="<?= $row['P_CODE']?>" />
                    </td>
                    <td>
                        <input type="text" name="P_INDATE" class="form-control" value="<?= $row['P_INDATE'] ?>"  form="<?= $row['P_CODE']?>"/>
                    </td>
                    <td>
                        <input type="number" name="P_QOH" class="form-control" value="<?= $row['P_QOH'] ?>"  form="<?= $row['P_CODE']?>"/>
                    </td>
                    <td>
                        <input type="number" name="P_MIN" class="form-control" value="<?= $row['P_MIN'] ?>"  form="<?= $row['P_CODE']?>"/>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="P_PRICE" class="form-control" value="<?= $row['P_PRICE'] ?>"  form="<?= $row['P_CODE']?>"/>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="P_DISCOUNT" class="form-control" value="<?= $row['P_DISCOUNT'] ?>"  form="<?= $row['P_CODE']?>"/>
                    </td>
                    <td>
                        <input type="text" name="V_CODE" class="form-control" value="<?= $row['V_CODE'] ?>"  form="<?= $row['P_CODE']?>"/>
                    </td>
                    <td>
                        <form id="<?= $row['P_CODE']?>" method="post">
                            <button type="submit" class="btn btn-primary" name="save" id="save">Save</button> <!--remember the inputs are already linked through the form tag, so no need for an extra input-->
                        </form>
                    </td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="P_CODE" id="delete" value="<?= $row['P_CODE']?>"/> <!--remember have to add this to the value of the rows. keep it hidden so no input is seen. is automatically assosiated with the button cuz its in the same form.-->
                            <button type="submit" class="btn btn-danger" name="delete" id="delete">Delete</button>
                        </form>
                    </td>
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
<!--Conn.close? maybe TODO come back -->