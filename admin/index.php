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

refresh_mangers(RENTS_MANGER_FLAG | CLIENTS_MANGER_FLAG | MATERIALS_MANGER_FLAG | ADMINS_MANGER_FLAG,$db_connection);

if(!monitor_rents())
    $error_msg = LANG_R("MONITOR_ERROR");

if(!isset($_SESSION['admin']))   // Check if admin is already loged in 
{
    header("Location:auth/login.php");
}

if(!$_SESSION['admin']->is_ceo)
{
    header("Location:rents/");  // Not CEO , Send him to rents page
}

$info_msg = $error_msg = "";
 
if($_SERVER["REQUEST_METHOD"] == "POST")
{
    switch($_POST['action_type'])
    {
        case "add":

            if($_SESSION['ADMINS_MANGER']->record_count($_POST['username']) > 0)
                $error_msg = LANG_R("ADMIN_ALREADY_EXISTS");
            else {
                $admin = new Admin();
                $admin->username = $_POST['username'];
                $admin->password = $_POST['password'];
                $admin->is_ceo   = FALSE;
                
                if($_SESSION['ADMINS_MANGER']->add($admin))
                    $info_msg = LANG_R("ADMIN_ADD_SUCCESS");
                else
                    $error_msg = LANG_R("ADMIN_ADD_FAILURE");

                $admin = null;
            }

            break;
        case "edit":

            if($_SESSION['ADMINS_MANGER']->record_count($_POST['username']) > 0)    // Without counting existing one if exists
                $error_msg = LANG_R("ADMIN_ALREADY_EXISTS");
            else {
                $pass = $_POST['password'];
       
                if(!password_get_info($pass)['algo'])
                    $pass = password_hash($pass , PASSWORD_DEFAULT);

                $tmp_obj = new Admin();
                $tmp_obj->id       = $_POST['id'];
                $tmp_obj->username = $_POST['username'];
                $tmp_obj->password = $pass;

                if(!$_SESSION['ADMINS_MANGER']->update($tmp_obj))
                    $error_msg = LANG_R("ADMIN_EDIT_FAILURE");
                else
                    $info_msg = LANG_R("ADMIN_EDIT_SUCCESS");

                $tmp_obj = null;
            }

            $pass = null;
            break;
        case "delete":

            if(!$_SESSION['ADMINS_MANGER']->delete($_POST['list_ids'],$_POST['num_ids']))
                $error_msg = LANG_R("ADMIN_DELETE_FAILURE");
            else
                $info_msg = LANG_R("ADMIN_DELETE_SUCCESS");
            
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php LANG("ADMIN_PAGE_TITLE"); ?></title>
    <script src="../js/scripts.js"></script>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="content-wraper">
        <div class="container">
            <h1><?php LANG("ADMIN_PAGE_TABLE_TITLE"); ?></h1>
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

            <table id="elements_table" class="table-small" border=1>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th><?php LANG("ADMIN_PAGE_TABLE_USERNAME"); ?></th>
                    <th><?php LANG("ADMIN_PAGE_TABLE_CEO"); ?></th>
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
                    $res->free_result();
                    $is_ceo = NULL;
                ?>
            </table>
            
            <div class="btns-wraper">
                <button type="button" onclick="toggle_display('edit_wraper');" class="btn"><?php LANG("BUTTON_EDIT"); ?></button>
                <button type="button" onclick="toggle_display('add_wraper');" class="btn"><?php LANG("BUTTON_ADD"); ?></button>
                <button type="button" onclick="delete_form_submit();" class="btn"><?php LANG("BUTTON_DELETE"); ?></button>
            </div>
        </div>
              
        <div id="add_wraper" class="popup-container" hidden>        
            <div class="container center" >
                <h1><?php LANG("ADMIN_PAGE_NEW_ADMIN"); ?></h1>
                <form name="auth_form" method="POST" action="index.php" onsubmit="verify_data(this);">
                <input type="hidden" name="action_type" value="add" />
                    <label for="username_field"><?php LANG("ADMIN_PAGE_TABLE_USERNAME"); ?>:</label><br>
                    <input name="username" type="text" class="username_field input-field"> <br>
                    <label for="password_field"><?php LANG("ADMIN_PAGE_PASSWORD"); ?>:</label><br>
                    <input name="password" type="password" class="password_field input-field"><br>
                    <div class="btns-wraper">
                        <input type="submit" value=<?php LANG_1("BUTTON_ADD"); ?> class="btn">
                        <button type="button" onclick="toggle_display('add_wraper');" class="btn"><?php LANG("BUTTON_CANCEL"); ?></button>
                    </div>
                </form>
            </div>
        </div>
        
        <div id="edit_wraper" class="popup-container" hidden>    
            <div class="container center" hidden>
                <h1><?php LANG("ADMIN_PAGE_EDIT"); ?></h4>
                <form name="auth_form" method="POST" action="index.php" onsubmit="admin_edit_form_submit(this);">
                    <input type="hidden" name="action_type" value="edit" />
                    <input type="hidden" name="id" value="0" />
                    <label for="username_field"><?php LANG("ADMIN_PAGE_NEW_USERNAME"); ?></label><br>
                    <input name="username" type="text" class="username_field input-field"> <br>
                    <label for="password_field"><?php LANG("ADMIN_PAGE_NEW_PASSWORD"); ?></label><br>
                    <input name="password" type="password" class="password_field input-field"><br>
                    <div class="btns-wraper">
                        <input type="submit" value=<?php LANG_1("BUTTON_EDIT"); ?> class="btn">
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