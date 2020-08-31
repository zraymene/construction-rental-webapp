<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 * 
 * In this page : 
 *  - Only CEO can access
 *  - View/Add/Edit/Remove other admins
 * 
 */
require("core/db_connect.php");
require("core/systems.php");

session_start();

refresh_mangers(ADMINS_MANGER_FLAG,$db_connection);

if(!isset($_SESSION['admin']))   // Check if admin is already loged in 
{
    header("Location:auth/login.php");
}

if(!$_SESSION['admin']->is_ceo)
{
    header("Location:rents/");  // Not CEO , Send him to rents page
}

$info = "";

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    switch($_POST['action_type'])
    {
        case "add":
            $admin = new Admin();
            $admin->username = $_POST['username'];
            $admin->password = $_POST['password'];
            $admin->is_ceo   = FALSE;
            
            if($_SESSION['ADMINS_MANGER']->add($admin))
                $info = "Admin added usccesfully!";
            else
                $info = "Error while adding admin !";

            break;
        case "edit":
            // ADDDDDDDDDD MEEEEEEEEEEEEEEE YOU FUCKING CUNT 
             echo "<pre>";
            print_r($_POST);
            echo "</pre>";

            echo "<pre>";
            print_r(password_get_info($_POST['password']));
            echo "</pre>";
            $pass = $_POST['password'];

            if(!password_get_info($pass)['algo'])
            {
                echo 'grgrgr <br>';
                $pass = password_hash($pass , PASSWORD_DEFAULT);
            }

            echo $pass;

            $tmp_obj = new Admin();
            $tmp_obj->id       = $_POST['id'];
            $tmp_obj->username = $_POST['username'];
            $tmp_obj->password = $pass;

            if(!$_SESSION['ADMINS_MANGER']->update($tmp_obj))
                $info = "Error while editing admin!";
            else
                $info = "Admin edited sccesfully!";

            $tmp_obj = null;
            break;
        case "delete":
            if(!$_SESSION['ADMINS_MANGER']->delete($_POST['list_ids'],$_POST['num_ids']))
                      $info = "Error while deleting admin!";
                    else
                     $info = "Admin deleted sccesfully!";
            
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN INDEX</title>
    <script src="../js/scripts.js"></script>
</head>
<body>
    <div id="global_wraper" style="width:300px;margin:auto;">
        <h1>Admins table:</h1>
        <ul>
            <li><a href="rents/">Rents</a></li>
            <li><a href="materials/">Materials</a></li>
            <li><a href="clients">Clients</a></li>
            <li><a href="#">Admins</a></li>
        </ul>
        
        <hr>

        <p><?php echo $info?></p>

        <h1>Add new admin:</h1>
        <form name="auth_form" method="POST" action="index.php" onsubmit="verify_data(this);">
        <input type="hidden" name="action_type" value="add" />
            <label for="username_field">Username:</label><br>
            <input name="username" type="text" class="username_field"> <br>
            <label for="password_field">Password:</label><br>
            <input name="password" type="password" class="password_field"><br>
            <input type="submit" value="Add" class="submit_btn">
        </form>

        <hr>

        <h1>Admins table:</h1>
        <table id="admins_table" border=1>
            <tr>
                <th></th>
                <th>ID</th>
                <th>Username</th>
                <th>Is CEO ?</th>
            </tr>
            <?php 
                $res = $_SESSION['ADMINS_MANGER']->select_all();

                $is_ceo = $checkbox = "";

                while($row = $res->fetch_assoc())
                {  

                    if($row['is_ceo'])
                    {
                        $checkbox = "";
                        $is_ceo = "YES";
                    }else {
                        $is_ceo = "NO";
                        $checkbox = "<input type=\"checkbox\"/>";
                    }
                    echo "<tr>\n<td>{$checkbox}</td>
                            <td>{$row['id']}</td>
                            <td>{$row['username']}</td>
                            <td hidden>{$row['password']}</td>
                            <td>{$is_ceo}</td>
                        </tr>
                        ";
                }
                $res = $is_ceo = NULL;
            ?>
        </table>
        
        <button type="button" onclick="toggle_display('edit_admin_wraper');">Edit</button>
        <button type="button" onclick="delete_form_submit();">Delete</button>

        <div id="edit_admin_wraper" hidden>
            <h4>Edit account:</h4>
            <form name="auth_form" method="POST" action="#" onsubmit="admin_edit_form_submit(this);">
                <input type="hidden" name="action_type" value="edit" />
                <input type="hidden" name="id" value="0" />
                <label for="username_field">New Username:</label><br>
                <input name="username" type="text" class="username_field"> <br>
                <label for="password_field">New Password:</label><br>
                <input name="password" type="password" class="password_field"><br>
                <input type="submit" value="Edit" class="submit_btn">
            </form>
        </div>
        <hr>

        <form name="delete_form" method="POST" action="index.php">
            <input type="hidden" name="action_type" value="delete" />
            <input type="hidden" name="num_ids" value="delete" />
        </form>


        <form method="POST" action="auth/logout.php">
            <input type="submit" value="Logout" class="submit_btn">
        </form>
    </div>
</body>
</html>