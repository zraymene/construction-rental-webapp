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
require("../core/lang.php");

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
                $info_msg = LANG_R("CLIENT_ADD_SUCCESS");
            else
                $error_msg = LANG_R("CLIENT_ADD_FAILURE");

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
                $info_msg = LANG_R("CLIENT_EDIT_SUCCESS");
            else
                $error_msg = LANG_R("CLIENT_EDIT_FAILURE");

            $new_client = NULL;
           
            break;
        case "delete":

            if(!$_SESSION['CLIENTS_MANGER']->delete($_POST['list_ids'],$_POST['num_ids']))
                $error_msg = LANG_R("CLIENT_DELETE_FAILURE");
            else
                $info_msg = LANG_R("CLIENT_DELETE_SUCCESS");
         
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php LANG("CLIENT_PAGE_TITLE"); ?></title>
    <script src="../../js/scripts.js"></script>
</head>
<body>
    <?php include("../header.php"); ?>

    <div class="content-wraper">
        <div class="container">
            <h1><?php LANG("CLIENT_PAGE_TABLE_TITLE"); ?>:</h1>
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
                    <th><?php LANG("CLIENT_PAGE_TABLE_FIRSTNAME"); ?></th>
                    <th><?php LANG("CLIENT_PAGE_TABLE_LASTNAME"); ?></th>
                    <th><?php LANG("CLIENT_PAGE_TABLE_EMAIL"); ?></th>
                    <th><?php LANG("CLIENT_PAGE_TABLE_PHONE"); ?></th>
                    <th><?php LANG("MATERIAL_PAGE_TABLE_RENTS_NUM"); ?></th>
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
                <button class="btn" onclick=<?php echo "location.href='index.php?page_num=". ($current_page - 1) ."'"; ?> type="button" <?php echo ($current_page == 1) ? "disabled" : ""; ?> ><?php LANG("BUTTON_PREV"); ?></button>
                <button class="btn" onclick=<?php echo "location.href='index.php?page_num=". ($current_page + 1) ."'"; ?> type="button" <?php echo ($current_page == $total_pages) ? "disabled" : ""; ?>><?php LANG("BUTTON_NEXT"); ?></button>
            </div>
            <div class="btns-wraper">
                <button type="button" onclick="toggle_display('edit_wraper');" class="btn"><?php LANG("BUTTON_EDIT"); ?></button>
                <button type="button" onclick="toggle_display('add_wraper');" class="btn"><?php LANG("BUTTON_ADD"); ?></button>
                <button type="button" onclick="delete_form_submit();" class="btn"><?php LANG("BUTTON_DELETE"); ?></button>
            </div>
            </div>
        </div>
        <br>
        
        <div id="add_wraper" class="popup-container" hidden>    
            <div class="container center" >    
                <h1><?php LANG("CLIENT_PAGE_NEW_CLIENT"); ?></h1>
                <form name="material_form" method="POST" action="index.php" onsubmit="verify_client_data(this);" enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="add" />
                    <label for="fname_field"><?php LANG("CLIENT_PAGE_TABLE_FIRSTNAME"); ?>:</label><br>
                    <input name="first_name" type="text" class="fname_field input-field"> <br>
                    <label for="lname_field"><?php LANG("CLIENT_PAGE_TABLE_LASTNAME"); ?>:</label><br>
                    <input name="last_name" type="text" class="lname_field input-field" ><br>
                    <label for="email_field"><?php LANG("CLIENT_PAGE_TABLE_EMAIL"); ?>:</label><br>
                    <input name="email" type="email" class="email_field input-field" ><br>
                    <label for="phone_field"><?php LANG("CLIENT_PAGE_TABLE_PHONE"); ?>:</label><br>
                    <input name="phone" type="number" class="phone_field input-field" ><br>
                    <div class="btns-wraper">
                        <input type="submit" value="Add" class="btn">
                        <button type="button" onclick="toggle_display('add_wraper');" class="btn"><?php LANG("BUTTON_CANCEL"); ?></button>
                    </div>
                </form>
            </div>
        </div>      

        <div id="edit_wraper" class="popup-container" hidden>    
            <div class="container center" >    
                <h1><?php LANG("CLIENT_PAGE_EDIT_CLIENT"); ?></h1>
                <form name="auth_form" method="POST" action="#" onsubmit="clients_edit_form_submit(this);" enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="edit" />
                    <input type="hidden" name="id" value="0" />
                    <label for="fname_field"><?php LANG("CLIENT_PAGE_TABLE_FIRSTNAME"); ?>:</label><br>
                    <input name="first_name" type="text" class="fname_field input-field"> <br>
                    <label for="lname_field"><?php LANG("CLIENT_PAGE_TABLE_LASTNAME"); ?>:</label><br>
                    <input name="last_name" type="text" class="lname_field input-field" ><br>
                    <label for="email_field"><?php LANG("CLIENT_PAGE_TABLE_EMAIL"); ?>:</label><br>
                    <input name="email" type="email" class="email_field input-field" ><br>
                    <label for="phone_field"><?php LANG("CLIENT_PAGE_TABLE_PHONE"); ?>:</label><br>
                    <input name="phone" type="number" class="phone_field input-field" ><br>
                    <div class="btns-wraper">
                        <input type="submit" value="Edit" class="btn">
                        <button type="button" onclick="toggle_display('edit_wraper');" class="btn"><?php LANG("BUTTON_CANCEL"); ?></button>
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