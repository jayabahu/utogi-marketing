<?php

if( !function_exists("marketing_settings_page") ) {
    function marketing_settings_page(){
        $activeTab = 'setup';

        if( isset( $_GET[ 'tab' ] ) ) {
            $activeTab = $_GET[ 'tab' ];
        } // end if

        ?>

        <div style="display: flex; flex-direction: row">
            <div style="width: 45px; margin-top: 5px">
                <img width="40" src="<?php echo UM_PLUGIN_URL . 'src/assets/icon.png'; ?>" alt="">
            </div>
            <h1>Utogi Marketing Settings</h1>
        </div>
        <hr>
        <h2 class="nav-tab-wrapper">
            <a href="?page=utogi_settings&tab=setup" class="nav-tab <?php echo $activeTab == 'setup' ? 'nav-tab-active' : ''; ?>">Setup</a>
            <a href="?page=utogi_settings&tab=import" class="nav-tab <?php echo $activeTab == 'import' ? 'nav-tab-active' : ''; ?>">Import</a>
            <a href="?page=utogi_settings&tab=webhook" class="nav-tab <?php echo $activeTab == 'webhook' ? 'nav-tab-active' : ''; ?>">Webhook</a>
        </h2>
        <?php settings_errors(); ?>

        <?php if ($activeTab === 'setup'): ?>

        <form method="post" action="options.php">
            <?php

            require plugin_dir_path( __FILE__ ) . '/setup.php';

            ?>
        </form>

        <?php endif; ?>

        <?php if ($activeTab === 'import'): ?>
            <form method="post" action="">
                <?php

                require plugin_dir_path( __FILE__ ) . '/'. $activeTab.'.php';

                ?>
            </form>
        <?php endif; ?>

        <?php if ($activeTab === 'webhook'): ?>

            <form method="post" action="options.php">
                <?php

                require plugin_dir_path( __FILE__ ) . '/webhook.php';

                ?>
            </form>

        <?php endif; ?>




    <?php }
}