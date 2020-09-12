<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */
?>
<link rel="stylesheet" href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/style.css\""; ?>>
<link rel="icon" href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/dashboard.png\"";?> >

<div class="topbar-wraper">
    <div class="lang-selec">
        <form method="POST" action=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/admin/core/lang-changer.php\"";?>>
            <select name="wanted_lang" class="select-field">
                <option value="EN">ENG</option>
                <option value="FR">FRA</option>
            </select>
            <input type="submit" value=<?php LANG("BUTTON_CHANGE"); ?> class="btn">
        </form>
    </div>
    <a href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/admin/auth/logout.php\"";?>><img src=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/exit.png\"";?> /></a>
</div>

<div class="sidemenu-wraper">
        <div class="sidemenu-title">
            <h1><?php LANG("MENU_TITLE"); ?></h1>
        </div>
        
        <a href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/admin/rents/\""; ?>>
            <div class="sidemenu-btn-icon">
                <img src=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/contract.png\"";?> />
            </div>
            <div class="sidemenu-btn-label">
                <p><?php LANG("MENU_RENT"); ?></p>
            </div>
        </a>
        <hr>
        <a href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/admin/materials/\""; ?>>
            <div class="sidemenu-btn-icon">
                <img src=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/materials.png\"";?> />
            </div>
            <div class="sidemenu-btn-label">
                <p><?php LANG("MENU_MATERIALS"); ?></p>
            </div>
        </a>
        <hr>
        <a href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/admin/clients/\""; ?>>
            <div class="sidemenu-btn-icon">
                <img src=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/customer.png\"";?> />
            </div>
            <div class="sidemenu-btn-label">
                <p><?php LANG("MENU_CLIENTS"); ?></p>
            </div>
        </a>
        <hr>
        <?php
            if($_SESSION['admin']->is_ceo) {
                echo "<a href=\"http://{$_SERVER['HTTP_HOST']}/pro/admin/\"/>
                        <div class='sidemenu-btn-icon'>
                            <img src=\"http://{$_SERVER['HTTP_HOST']}/pro/css/admin.png\"/>
                        </div>
                        <div class='sidemenu-btn-label'>
                            <p>Admins</p>
                        </div>
                    </a>
                    <hr>";
            }
        ?>

</div>