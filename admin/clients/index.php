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

$info_msg = $error_msg = "";

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
                $info_msg = "Client added usccesfully!";
            else
                $error_msg = "Error while adding new Client !";

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
                $info_msg = "Client edited usccesfully!";
            else
                $error_msg = "Error while editing Client !";

            $new_client = NULL;
           
            break;
        case "delete":

            if(!$_SESSION['CLIENTS_MANGER']->delete($_POST['list_ids'],$_POST['num_ids']))
                $error_msg = "Error while deleting clients!";
            else
                $info_msg = "Client deleted succesfully!";
         
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
</head>
<body>
    <?php include("../header.php"); ?>

    <div class="content-wraper">
        <div class="container">
            <h1>Clients table:</h1>
            <?php 
                    $color = $msg = "";

                    if(empty($error_msg) && !empty($info_msg)) {
                        $color = "green";
                        $msg = $info_msg;
                    }else if(!empty($error_msg) && empty($info_msg)){
                        $color = "red";
                        $msg = $error_msg;
                    }

                    if(!empty($color))
                    {
                        echo "<div class=\"notfication-container notif-{$color}\">
                                <div class=\"notif-icon\">
                                    <img />
                                </div>
                                <div class=\"notif-msg\">
                                    <p>{$msg}</p>
                                </div>
                            </div>";
                    }
            ?>
            
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
            <p class="page-index"><?php 
                    $total_pages  = round( ($_SESSION['CLIENTS_MANGER']->get_total_rows_count() / NUMBER_ELEMENTS_PER_PAGE) + 0.5);
                    $current_page = intval((isset($_GET['page_num']) ?  $_GET['page_num'] : "1"));
                    echo $current_page . " / " . $total_pages;
                ?></p>
            <div class="btns-wraper">
                <button class="btn" onclick=<?php echo "location.href='index.php?page_num=". ($current_page - 1) ."'"; ?> type="button" <?php echo ($current_page == 1) ? "disabled" : ""; ?> >Previous</button>
                <button class="btn" onclick=<?php echo "location.href='index.php?page_num=". ($current_page + 1) ."'"; ?> type="button" <?php echo ($current_page == $total_pages) ? "disabled" : ""; ?>>After</button>
            </div>
            <div class="btns-wraper">
                <button type="button" onclick="toggle_display('edit_wraper');" class="btn">Edit</button>
                <button type="button" onclick="toggle_display('add_wraper');" class="btn">Add</button>
                <button type="button" onclick="delete_form_submit();" class="btn">Delete</button>
            </div>
            </div>
        </div>
        <br>
        
        <div id="add_wraper" class="popup-container" hidden>    
            <div class="container center" >    
                <h1>Add new Client:</h1>
                <form name="material_form" method="POST" action="index.php" onsubmit="verify_client_data(this);" enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="add" />
                    <label for="fname_field">First name:</label><br>
                    <input name="first_name" type="text" class="fname_field input-field"> <br>
                    <label for="lname_field">Last name:</label><br>
                    <input name="last_name" type="text" class="lname_field input-field" ><br>
                    <label for="email_field">Email:</label><br>
                    <input name="email" type="email" class="email_field input-field" ><br>
                    <label for="phone_field">Phone number:</label><br>
                    <input name="phone" type="number" class="phone_field input-field" ><br>
                    <div class="btns-wraper">
                        <input type="submit" value="Add" class="btn">
                        <button type="button" onclick="toggle_display('add_wraper');" class="btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>      

        <div id="edit_wraper" class="popup-container" hidden>    
            <div class="container center" >    
                <h4>Edit clients:</h4>
                <form name="auth_form" method="POST" action="#" onsubmit="clients_edit_form_submit(this);" enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="edit" />
                    <input type="hidden" name="id" value="0" />
                    <label for="fname_field">First name:</label><br>
                    <input name="first_name" type="text" class="fname_field input-field"> <br>
                    <label for="lname_field">Last name:</label><br>
                    <input name="last_name" type="text" class="lname_field input-field" ><br>
                    <label for="email_field">Email:</label><br>
                    <input name="email" type="email" class="email_field input-field" ><br>
                    <label for="phone_field">Phone number:</label><br>
                    <input name="phone" type="number" class="phone_field input-field" ><br>
                    <div class="btns-wraper">
                        <input type="submit" value="Edit" class="btn">
                        <button type="button" onclick="toggle_display('edit_wraper');" class="btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <form name="delete_form" method="POST" action="index.php">
            <input type="hidden" name="action_type" value="delete" />
            <input type="hidden" name="num_ids" value="delete" />
        </form>

    </div>
</body>
</html>