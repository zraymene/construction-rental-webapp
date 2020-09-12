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

refresh_mangers(MATERIALS_MANGER_FLAG | CLIENTS_MANGER_FLAG,$db_connection);

if(!isset($_SESSION['admin']))   // Check if admin is already loged in 
{
    header("Location:../auth/login.php");
}

$info_msg = $error_msg = "";

function check_img($file , $is_edit = false , $old_img_name = "")
{
    if(empty($file['name']))
        return IMG_MAT_DEFAULT;

    $img_name = "";
    $img_ext = strtolower(pathinfo(basename($file["name"]),PATHINFO_EXTENSION));

    if(!$is_edit)
        $img_name = uniqid("material-",true) . "." . $img_ext;
    else {

        if(strcmp($old_img_name , IMG_MAT_DEFAULT) != 0)
        {
            if(!unlink("imgs/{$old_img_name}"))
                $error_msg = LANG_R("MATERIAL_IMG_DELETE_ERR");
        }

        $old_img_name = explode("."  ,$old_img_name );
        $img_name .= "{$old_img_name[0]}.{$old_img_name[1]}." . $img_ext; 
       
    }

    $output_file = "imgs/" . $img_name;

    if(!getimagesize($file["tmp_name"])) {
        $error_msg = LANG_R("MATERIAL_IMG_NOT");
        return IMG_MAT_DEFAULT;
    }

    if ($file["size"] > IMG_MAX_SIZE) {
        $error_msg = LANG_R("MATERIAL_IMG_LARGE");
        return IMG_MAT_DEFAULT;
    }

    if($img_ext != "jpg" && $img_ext != "png" && $img_ext != "jpeg"){
        $error_msg =  LANG_R("MATERIAL_IMG_TYPE");
        return IMG_MAT_DEFAULT;
    }

    if (!move_uploaded_file($file["tmp_name"], $output_file)) {
        $error_msg = LANG_R("MATERIAL_IMG_UPLOAD_ERR");
        return IMG_MAT_DEFAULT;
    }
    
    return $img_name;
}

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    switch($_POST['action_type'])
    {
        case "add":        
            $img_name = check_img($_FILES['mat_img'] );
            
            $mat = new Material();
            $mat->name          = $_POST['mat_name'];
            $mat->is_free       = TRUE;
            $mat->list_clients  = array();
            $mat->image_path    = $img_name;
            
            if($_SESSION['MATERIALS_MANGER']->add($mat))
                $info_msg = LANG_R("MATERIAL_ADD_SUCCESS");
            else
                $error_msg = LANG_R("MATERIAL_ADD_FAILURE");

            $mat = $img_ext = $img_name = $output_file = NULL;

            break;
        case "edit":
            
            $new_mat = new Material();
            $new_mat->id            = $_POST['id'];
            $new_mat->name          = $_POST['mat_name'];
            
            if($_FILES['mat_img']['size'] != 0) 
            {
        
                $img_name = $_POST['mat_img_name'];

                if(!strcmp($img_name , IMG_MAT_DEFAULT))
                    $new_mat->image_path = check_img($_FILES['mat_img']);
                else
                    $new_mat->image_path = check_img($_FILES['mat_img'] , true ,$img_name);
            }

            if($_SESSION['MATERIALS_MANGER']->update($new_mat))
                $info_msg = LANG_R("MATERIAL_EDIT_SUCCESS");
            else
                $error_msg = LANG_R("MATERIAL_EDIT_FAILURE");

            $new_mat = NULL;

            break;
        case "delete":

            foreach($_POST['name_imgs'] as $img)
            {
                if(strcmp($img , IMG_MAT_DEFAULT))
                {
                   if(!unlink("imgs/".$img))
                       echo LANG_R("MATERIAL_IMG_DELETE_ERR");
                }
            }

            if(!$_SESSION['MATERIALS_MANGER']->delete($_POST['list_ids'],$_POST['num_ids']))
                $error_msg = LANG_R("MATERIAL_DELETE_FAILURE");
            else
                $info_msg = LANG_R("MATERIAL_DELETE_SUCCESS");
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo LANG("MATERIAL_PAGE_TITLE");?></title>
    <script src="../../js/scripts.js"></script>
    <style>
        table img {
            width : 100px;
            height : 100px;
        }
    </style>

</head>
<body>
    <?php include("../header.php"); ?>

    <div class="content-wraper">
        <div class="container">
            <h1><?php echo LANG("MATERIAL_PAGE_TABLE_TITLE");?></h1>
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
                    <th><?php echo LANG("MATERIAL_PAGE_TABLE_NAME");?></th>
                    <th><?php echo LANG("MATERIAL_PAGE_TABLE_FREE");?></th>
                    <th><?php echo LANG("MATERIAL_PAGE_TABLE_RENTS_NUM");?></th>
                    <th><?php echo LANG("MATERIAL_PAGE_TABLE_CLIENTS_LIST");?></th>
                    <th><?php echo LANG("MATERIAL_PAGE_TABLE_IMAGE");?></th>
                </tr>
                <?php 
                    $start = 0;

                    if(isset($_GET['page_num']))
                        $start += ($_GET['page_num'] * NUMBER_ELEMENTS_PER_PAGE) + 1; 

                    $res = $_SESSION['MATERIALS_MANGER']->select_limit($start , $start + NUMBER_ELEMENTS_PER_PAGE);
                        
                    if( $res != NULL)
                    {
                        $is_free = $cl_html = "";
                        $clients_list;

                        while($row = $res->fetch_array())
                        {  
                            $cl_html = "";
                            $clients_list = json_decode($row['list_clients']);

                            if($row['is_free'])
                            {
                                $checkbox = "";
                                $is_free = "YES";
                            }else {
                                $is_free = "NO";
                            }

                            if($clients_list != null){
                                $tmp_arr = array();
                                $cl_html .= "• ";

                                foreach($clients_list as $id) {
                                    if(array_key_exists($id,$tmp_arr))
                                        $tmp_arr[$id] += 1;
                                    else
                                        $tmp_arr[$id] = 1;
                                }

                                foreach($tmp_arr as $id => $num) {
                                    $client   = $_SESSION['CLIENTS_MANGER']->select_id($id);

                                    $cl_html .= LANG_R("RENTS_PAGE_TABLE_CLIENT")." : {$client->first_name} {$client->last_name}, ". LANG_R("MATERIAL_PAGE_TABLE_RENTED") ." {$num} ". LANG_R("REPEATITION") ." <br>";
                                    
                                    $client = null;
                                }
                            }

                            echo "<tr>\n<td><input type=\"checkbox\"/></td>
                                    <td>{$row['id']}</td>
                                    <td>{$row['name']}</td>
                                    <td> {$is_free} </td>
                                    <td>". count($clients_list) ."</td>
                                    <td> {$cl_html} </td>
                                    <td><img src='imgs/{$row['image_path']}' alt='{$row['image_path']}'></td>
                                </tr>
                                ";

                        }

                        $res->free_result();
                        $is_free = $cl_html = $clients_list = NULL;
                }
                ?>
            </table>
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
                <button type="button" onclick="delete_form_submit(1);"class="btn"><?php LANG("BUTTON_DELETE"); ?></button>
            </div>
        </div>

        <div id="add_wraper" class="popup-container" hidden>    
            <div class="container center" >    
                <h1><?php LANG("MATERIAL_PAGE_NEW_MATERIAL"); ?></h1>
                <form name="material_form" method="POST" action="index.php" onsubmit="verify_material_data(this);" enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="add" />
                    <label for="name_field"><?php LANG("MATERIAL_PAGE_TABLE_NAME"); ?>:</label><br>
                    <input name="mat_name" type="text" class="name_field input-field"> <br>
                    <label for="img_field"><?php LANG("MATERIAL_PAGE_TABLE_IMAGE"); ?>:</label><br>
                    <input name="mat_img" type="file" class="img_field" ><br>
                    <div class="btns-wraper">
                        <input type="submit" value=<?php LANG_1("BUTTON_ADD"); ?> class="btn">
                        <button type="button" onclick="toggle_display('add_wraper');" class="btn"><?php LANG("BUTTON_CANCEL"); ?></button>
                    </div>
                </form>
            </div>
        </div>

        <div id="edit_wraper" class="popup-container" hidden>
            <div class="container center" hidden>
                <h1><?php LANG("MATERIAL_PAGE_EDIT_MATERIAL"); ?>:</h1>
                <form name="auth_form" method="POST" action="#" onsubmit="material_edit_form_submit(this);" enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="edit" />
                    <input type="hidden" name="id" value="0" />
                    <input type="hidden" name="mat_img_name" value="default.png"/>
                    <label for="name_field"><?php LANG("MATERIAL_PAGE_NEW_NAME"); ?>:</label><br>
                    <input name="mat_name" type="text" class="name_field input-field"> <br>
                    <label for="img_field"><?php LANG("MATERIAL_PAGE_NEW_IMAGE"); ?>:</label><br>
                    <input name="mat_img" type="file" class="img_field" ><br>
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