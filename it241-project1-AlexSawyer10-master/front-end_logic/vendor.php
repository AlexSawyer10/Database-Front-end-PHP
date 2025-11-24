<?php
require_once  "db_cred/db_cred.inc";
// database connection settings
/**
 * @var mysqli $conn
 */

session_start();

if(!isset($_SESSION['username']) && !isset($_SESSION['user_id']))
{
    header('Location: logout.php');
}

if ($conn->connect_error)
{
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == 'POST')
{
    if(isset($_POST['add-row']))
    {
        $random_PK = random_int(1,10000000);
        $stmt = $conn->prepare("INSERT INTO VENDOR (V_CODE,V_NAME,V_CONTACT,V_AREACODE,V_PHONE,V_STATE,V_ORDER)
        VALUES (?,'','','','','','')");

        $stmt->bind_param('i',$random_PK);

        if ($stmt->execute() === false)
        {
            $success = false;
            error_log('Error updating record ' . $conn->error);
        }
        else
        {
            header('Location: vendor.php?success=1'); //resets page so then it can go fetch the new row from the data base then display it
            exit(); // remember this leaves entire php block
        }

    }
    if(isset($_POST['delete']))
    {
        $v_code = htmlentities($_POST['V_CODE']);

        $stmt = $conn->prepare("SELECT COUNT(*) AS TOTAL FROM PRODUCT WHERE V_CODE = ?"); // remember u have to reference the alias to get the number of rows, if i referenced P_CODE it woulnd't exist because thats not what the query returns.
        $stmt->bind_param('s',$v_code);
        $stmt->execute();
        $result = $stmt->get_result(); // remember this gets the result from the query REMEMBER AS A RESULT OBJECT NOT AS THE ACTUAL VALUE. Reference for confusion https://www.php.net/manual/en/mysqli-stmt.get-result.php?utm_source=chatgpt.com
        $numberOfRows = $result->fetch_assoc(); // need this to fetch the actual data from get_result(); so I can then later compare the number of rows it has. It returns it as an associative array so the key in this case would be TOTAL. Reference for confusion https://www.php.net/manual/en/mysqli-result.fetch-assoc.php?utm_source=chatgpt.com
        $stmt->close();

        if($numberOfRows['TOTAL'] > 0)
        {
            error_log("Cannot delete this because it already exists in a PRODUCT.");
            header('Location: vendor.php?error=1');

            exit(); // remember this leaves entire php block
        }
        $stmt = $conn->prepare("DELETE FROM VENDOR WHERE V_CODE = ?");
        $stmt->bind_param('s',$v_code);

        $success = true;

        if ($stmt->execute() === false)
        {
            $success = false;
            header('Location: vendor.php?error=1');
        }
        $stmt->close();

        if($success === true)
        {
            header('Location: vendor.php?success=1');
        }
        else
        {
            header('Location: vendor.php?error=1');
        }

        exit();
    }

    if(isset($_POST['save']))
    {
        $v_code = htmlentities($_POST['V_CODE']);
        $v_name = htmlentities($_POST['V_NAME']);
        $v_contact = htmlentities($_POST['V_CONTACT']);
        $v_areacode = htmlentities($_POST['V_AREACODE']);
        $v_phone = htmlentities($_POST['V_PHONE']);
        $v_state = htmlentities($_POST['V_STATE']);
        $v_order = htmlentities($_POST['V_ORDER']);

        if($v_order != 'Y' && $v_order != 'N')
        {
            error_log('invalid v_order' . $conn->error);
            header('Location: vendor.php?error=1');
            exit();
        }
        if(strlen($v_state) > 2)
        {
            error_log('invalid v_state' . $conn->error);
            header('Location: vendor.php?error=1');
            exit();
        }
        if(!is_numeric($v_areacode))
        {
            error_log('invalid v_areacode' . $conn->error);
            header('Location: vendor.php?error=1');
            exit();
        }
        if(is_numeric($v_contact))
        {
            error_log('invalid v_contact' . $conn->error);
            header('Location: vendor.php?error=1');
            exit();
        }
        // I guess vendor name could have a number in it so not adding validation there

        $stmt = $conn->prepare("UPDATE VENDOR SET  V_CODE = ?, V_NAME = ?, V_CONTACT = ?, V_AREACODE = ?, V_PHONE = ?,V_STATE = ?, V_ORDER = ? WHERE V_CODE = ?");
        $stmt->bind_param('issssssi',  $v_code,$v_name, $v_contact, $v_areacode, $v_phone, $v_state, $v_order,$v_code);

        $success = true;

        if ($stmt->execute() === false)
        {
            $success = false;
            error_log('Error updating record ' . $conn->error);
        }
        $stmt->close();

        // TODO

        if($success === true)
        {
            header('Location: vendor.php?success=1');
        }
        else
        {
            header('Location: vendor.php?error=1');
        }

        exit();
    }
}


$sql = "SELECT V_CODE, V_NAME, V_CONTACT, V_AREACODE, V_PHONE, V_STATE, V_ORDER FROM VENDOR"; // remember need these for display
$result = $conn->query($sql); // needed for display

$conn->close(); //close the connection to not leak memory
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Vendor</title>

    <!--bootStrap here-->  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!--CSS here--> <link rel="stylesheet" href="css/vendor.css">

</head>
<body>

<nav class="navbar navbar-expand-lg bg-body-tertiary"> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
    <a href="dashboard.php" class="navbar-brand text-decoration-underline">Dashboard</a> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
    <a href="logout.php" class="navbar-brand text-decoration-underline">Logout</a> <!--bootstrap class https://getbootstrap.com/docs/5.3/components/navbar/#supported-content-->
</nav>

<main class="container mt-3">
        <h1 class="text-center">Editable Vendor Table</h1>

        <!-- display success or error message -->
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">Record Updated successfully</div>
        <?php elseif (isset($_GET['error'])) : ?>
            <div class="alert alert-danger">Record Update failed</div>
        <?php endif; ?>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
            <tr>
                <th>V_CODE</th>
                <th>V_NAME</th>
                <th>V_CONTACT</th>
                <th>V_AREACODE</th>
                <th>V_PHONE</th>
                <th>V_STATE</th>
                <th>V_ORDER</th>
                <th>Update</th>
                <th>Delete</th>
            </tr>
            </thead>
            <tbody>
            <div class="center">
                <form method="post">
                    <button class="add-row" type="submit" name="add-row" id="add-row">Add row</button>
                </form>
            </div>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <input type="text" name="V_CODE" class="form-control" value="<?= $row['V_CODE'] ?>" form="<?= $row['V_CODE']?>" readonly /> <!--remember read only cuz u don't want them to change the code #-->
                        </td>
                        <td>
                            <input type="text" name="V_NAME" class="form-control" value="<?= $row['V_NAME'] ?>" form="<?= $row['V_CODE']?>" />
                        </td>
                        <td>
                            <input type="text" name="V_CONTACT" class="form-control" value="<?= $row['V_CONTACT'] ?>"  form="<?= $row['V_CODE']?>"/>
                        </td>
                        <td>
                            <input type="text" name="V_AREACODE" class="form-control" value="<?= $row['V_AREACODE'] ?>"  form="<?= $row['V_CODE']?>"/>
                        </td>
                        <td>
                            <input type="text" name="V_PHONE" class="form-control" value="<?= $row['V_PHONE'] ?>"  form="<?= $row['V_CODE']?>"/>
                        </td>
                        <td>
                            <input type="text"  name="V_STATE" class="form-control" value="<?= $row['V_STATE'] ?>"  form="<?= $row['V_CODE']?>"/>
                        </td>
                        <td>
                            <input type="text"  name="V_ORDER" class="form-control" value="<?= $row['V_ORDER'] ?>"  form="<?= $row['V_CODE']?>"/>
                        </td>
                        <td>
                            <form id="<?= $row['V_CODE']?>" method="post">
                                <button type="submit" class="btn btn-primary" name="save" id="save">Save</button> <!--remember the inputs are already linked through the form tag, so no need for an extra input-->
                            </form>
                        </td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="V_CODE" id="delete" value="<?= $row['V_CODE']?>"/> <!--remember have to add this to the value of the rows. keep it hidden so no input is seen. is automatically assosiated with the button cuz its in the same form.-->
                                <button type="submit" class="btn btn-danger" name="delete" id="delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No vendors found</td>
                </tr>

            <?php endif; ?>
            </tbody>
        </table>
</main>


<!--bootStrap here--> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
