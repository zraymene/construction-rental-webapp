<link rel="stylesheet" href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/style.css\""; ?>>
<link rel="icon" href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/dashboard.png\"";?> >

<div class="navbar_wraper">

</div>
<div class="topbar-wraper">
    <a href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/admin/auth/logout.php\"";?>><img src=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/exit.png\"";?> /></a>
</div>

<div class="sidemenu-wraper">
        <div class="sidemenu-title">
            <h1>Control Panel</h1>
        </div>
        
        <a href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/admin/rents/\""; ?>>
            <div class="sidemenu-btn-icon">
                <img src=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/contract.png\"";?> />
            </div>
            <div class="sidemenu-btn-label">
                <p>Rents</p>
            </div>
        </a>
        <hr>
        <a href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/admin/materials/\""; ?>>
            <div class="sidemenu-btn-icon">
                <img src=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/materials.png\"";?> />
            </div>
            <div class="sidemenu-btn-label">
                <p>Materials</p>
            </div>
        </a>
        <hr>
        <a href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/admin/clients/\""; ?>>
            <div class="sidemenu-btn-icon">
                <img src=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/customer.png\"";?> />
            </div>
            <div class="sidemenu-btn-label">
                <p>Clients</p>
            </div>
        </a>
        <hr>
        <a href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/admin/\""; ?>>
            <div class="sidemenu-btn-icon">
                <img src=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/pro/css/admin.png\"";?> />
            </div>
            <div class="sidemenu-btn-label">
                <p>Admins</p>
            </div>
        </a>
        <hr>

</div>