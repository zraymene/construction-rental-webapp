<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 * 
 * In this page : 
 *  - View/Add/Edit/Remove other Materials
 * 
 */
require("../core/db_connect.php");
require("../core/systems.php");

session_start();

refresh_mangers(MATERIALS_MANGER_FLAG | CLIENTS_MANGER_FLAG,$db_connection);

if(!isset($_SESSION['admin']))   // Check if admin is already loged in 
{
    header("Location:../auth/login.php");
}

$info = "";

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    switch($_POST['action_type'])
    {
        case "add":
            
            break;
        case "edit":
            
            break;
        case "delete":
           
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MATERIAL CONTROL PANEL</title>
    <script src="../../js/scripts.js"></script>
</head>
<body>
    <div id="global_wraper" style="width:300px;margin:auto;">
        <h1>Materials table:</h1>
        <ul>
            <li><a href="rents/">Rents</a></li>
            <li><a href="#">Materials</a></li>
            <li><a href="clients/">Clients</a></li>
            <li><a href="../">Admins</a></li>
        </ul>
        
        <hr>

        <p><?php echo $info?></p>

        <h1>Add new Material:</h1>
        <form name="material_form" method="POST" action="index.php" onsubmit="verify_material_data(this);">
            <input type="hidden" name="action_type" value="add" />
            <label for="name_field">Material name:</label><br>
            <input name="mat_name" type="text" class="name_field"> <br>
            <label for="price_field">Default price:</label><br>
            <input name="mat_dprice" type="text" class="price_field"><br>
            <label for="img_field">Image:</label><br>
            <input name="mat_img" type="file" class="img_field" ><br>
            <input type="submit" value="Add" class="submit_btn">
        </form>

        <hr>

        <h1>Materials table:</h1>
        <table id="materials_table" border=1>
            <tr>
                <th></th>
                <th>ID</th>
                <th>name</th>
                <th>Default Price</th>
                <th>Number of rents</th>
                <th>Is free (Now)</th>
                <th>Clients list</th>
                <th>Image</th>
            </tr>
            <?php 
                $start = 1;

                if(isset($_GET['page_num']))
                    $start += ($_GET['page_num'] * NUMBER_ELEMENTS_PER_PAGE) + 1; 

                $res = $_SESSION['MATERIALS_MANGER']->select_range_id($start , $start + NUMBER_ELEMENTS_PER_PAGE);

                $is_free = $checkbox = $cl_html = "";
                $clients_list;

                while($row = $res->fetch_assoc())
                {  
                    $clients_list = json_decode($res['list_clients']);

                    if($row['is_free'])
                    {
                        $checkbox = "";
                        $is_free = "YES";
                    }else {
                        $is_free = "NO";
                        $checkbox = "<input type=\"checkbox\"/>";
                    }

                    if($clients_list != null){
                        foreach($clients_list as $id => $num) {
                            $cl_html .= "Client {$id} rented {$num} time <br>";
                        }
                    }
                    echo "<tr>\n<td>{$checkbox}</td>
                            <td>{$row['id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['defualt_price']}</td>
                            <td>". count($clients_list) ."</td>
                            <td> {$cl_html} </td>
                            <td>{$row['image_path']}</td>
                        </tr>
                        ";
                }
                $res = $is_free = $cl_html = $clients_list = NULL;
            ?>
        </table><br>
        <p><?php echo (isset($_GET['page_num']) ?  $_GET['page_num'] : "1"). "/TOTAL_PAGES" ; ?></p>
        <button type="button" onclick="toggle_display('edit_admin_wraper');">Edit</button>
        <button type="button" onclick="delete_form_submit();">Delete</button>

        <div id="edit_admin_wraper" hidden>
            <h4>Edit material:</h4>
            <form name="auth_form" method="POST" action="#" onsubmit="admin_edit_form_submit(this);">
                <input type="hidden" name="action_type" value="edit" />
                <input type="hidden" name="id" value="0" />
                <label for="name_field">Material name:</label><br>
                <input name="mat_name" type="text" class="name_field"> <br>
                <label for="price_field">Default price:</label><br>
                <input name="mat_dprice" type="text" class="price_field"><br>
                <label for="img_field">Image:</label><br>
                <input name="mat_img" type="file" class="img_field" ><br>
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