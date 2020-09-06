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

refresh_mangers(CLIENTS_MANGER_FLAG,$db_connection);

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
            
            $client = new Client();
            $client->first_name = $_POST['first_name'];
            $client->last_name  = $_POST['last_name'];
            $client->email      = $_POST['email'];
            $client->phone      = $_POST['phone'];
            $client->list_rents = array();

            if($_SESSION['CLIENTS_MANGER']->add($client))
                $info = "Client added usccesfully!";
            else
                $info = "Error while adding new Client !";

            $client = null;

            break;
        case "edit":

            $new_client = new Client();
            $new_client->id         = $_POST['id'];
            $new_client->first_name = $_POST['first_name'];
            $new_client->last_name  = $_POST['last_name'];
            $new_client->email      = $_POST['email'];
            $new_client->phone      = $_POST['phone'];

            var_dump($new_client);

            if($_SESSION['CLIENTS_MANGER']->update($new_client))
                $info = "Client edited usccesfully!";
            else
                $info = "Error while editing Client !";

            $new_client = NULL;
           
            break;
        case "delete":

            if(!$_SESSION['CLIENTS_MANGER']->delete($_POST['list_ids'],$_POST['num_ids']))
                $info = "Error while deleting clients!";
            else
                $info = "Client deleted succesfully!";
         
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CLIENTS CONTROL PANEL</title>
    <script src="../../js/scripts.js"></script>
    <style>
        img {
            width : 100px;
            height : 100px;
        }
    </style>
</head>
<body>
    <div id="global_wraper" style="">
        <h1>CLIENTS table:</h1>
        <ul>
            <li><a href="../rents/">Rents</a></li>
            <li><a href="../materials/">Materials</a></li>
            <li><a href="#">Clients</a></li>
            <li><a href="../">Admins</a></li>
        </ul>
        
        <hr>

        <p><?php echo $info?></p>

        <h1>Add new Client:</h1>
        <form name="material_form" method="POST" action="index.php" onsubmit="verify_client_data(this);" enctype="multipart/form-data">
            <input type="hidden" name="action_type" value="add" />
            <label for="fname_field">First name:</label><br>
            <input name="first_name" type="text" class="fname_field"> <br>
            <label for="lname_field">Last name:</label><br>
            <input name="last_name" type="text" class="lname_field" ><br>
            <label for="email_field">Email:</label><br>
            <input name="email" type="email" class="email_field" ><br>
            <label for="phone_field">Phone number:</label><br>
            <input name="phone" type="number" class="phone_field" ><br>
            <input type="submit" value="Add" class="submit_btn">
        </form>

        <hr>

        <h1>Clients table:</h1>
        <table id="elements_table" border=1>
            <tr>
                <th></th>
                <th>ID</th>
                <th>First name</th>
                <th>Last name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Number of Rents</th>
            </tr>
            <?php 
                $start = 0;

                if(isset($_GET['page_num']))
                    $start += ($_GET['page_num'] * NUMBER_ELEMENTS_PER_PAGE) + 1; 

                $res = $_SESSION['CLIENTS_MANGER']->select_limit($start , $start + NUMBER_ELEMENTS_PER_PAGE);
                    
                if( $res != NULL)
                {
                    $rents_html = "";
                    $rents_list;
                    
                    while($row = $res->fetch_array())
                    {  
                        $rents_list = json_decode($row['list_rents']);

                        echo "<tr>\n<td><input type=\"checkbox\"/></td>
                                <td>{$row['id']}</td>
                                <td>{$row['first_name']}</td>
                                <td>{$row['last_name']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['phone']}</td>
                                <td>". count($rents_list) ."</td>
                            </tr>
                            ";
                    }

                    $res->free_result();
            }
            ?>
        </table><br>
        <p><?php 
                $total_pages  = round( ($_SESSION['CLIENTS_MANGER']->get_total_rows_count() / NUMBER_ELEMENTS_PER_PAGE) + 0.5);
                $current_page = intval((isset($_GET['page_num']) ?  $_GET['page_num'] : "1"));
                echo $current_page . "/" . $total_pages;
            ?></p>
        <button onclick=<?php echo "location.href='index.php?page_num=". ($current_page - 1) ."'"; ?> type="button" <?php echo ($current_page == 1) ? "disabled" : ""; ?> >Previous</button>
        <button onclick=<?php echo "location.href='index.php?page_num=". ($current_page + 1) ."'"; ?> type="button" <?php echo ($current_page == $total_pages) ? "disabled" : ""; ?>>After</button>
        <br>
        <br>
        <button type="button" onclick="toggle_display('edit_wraper');">Edit</button>
        <button type="button" onclick="delete_form_submit();">Delete</button>

        <div id="edit_wraper" hidden>
            <h4>Edit clients:</h4>
            <form name="auth_form" method="POST" action="#" onsubmit="clients_edit_form_submit(this);" enctype="multipart/form-data">
                <input type="hidden" name="action_type" value="edit" />
                <input type="hidden" name="id" value="0" />
                <label for="fname_field">First name:</label><br>
                <input name="first_name" type="text" class="fname_field"> <br>
                <label for="lname_field">Last name:</label><br>
                <input name="last_name" type="text" class="lname_field" ><br>
                <label for="email_field">Email:</label><br>
                <input name="email" type="email" class="email_field" ><br>
                <label for="phone_field">Phone number:</label><br>
                <input name="phone" type="number" class="phone_field" ><br>
                <input type="submit" value="Edit" class="submit_btn">
            </form>
        </div>
        <hr>

        <form name="delete_form" method="POST" action="index.php">
            <input type="hidden" name="action_type" value="delete" />
            <input type="hidden" name="num_ids" value="delete" />
        </form>


        <form method="POST" action="../auth/logout.php">
            <input type="submit" value="Logout" class="submit_btn">
        </form>
    </div>
</body>
</html>