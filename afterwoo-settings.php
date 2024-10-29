<?php
class awooSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'AfterWoo - Order Export from WooCommerce to Afterbuy', 
            'Afterbuy', 
            'manage_options', 
            'awoo_settings', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'awoo_setup_option' );
        ?>
        <div class="wrap">
            <h1>AfterWoo - Order Export from WooCommerce to Afterbuy</h1>
            <h2>Afterbuy Order-Export</h2>
                <b>First: You need to order an API-Access in Afterbuy (Shop-Schnittstelle). 
                After ordering you will acquire the credentials per Mail from Afterbuy. <br/>
                Second: Please Provide the needed information below, in order to export you orders to the ERP "Afterbuy". 
                Note: Every Order with the status "Completed" and only those will be exported! <br/>
                If your order has any different status, you can change the status to "Completed" and this plugin will export it. <br/> 
                Note: Kundenerkennung = via E-Mail, Artikelerkennung = via Afterbuy-Artikelnummer. </b>
                <br/>
                <br/>
                Dieses Plugin wurde von einem Studenten entwickelt und wird völlig unentgeldlich angeboten.Keine Werbung, keine Einschränkungen.
                <br/>
                Über Spenden für die Kaffeekasse freut man sich immer. Kontakt: afterwoo@protonmail.com 
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="836F2ESXWWT4Q">
            <input type="image" src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="Eine Spende für den Studenten :) .">
            <img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
            </form>

            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'awoo_option_group' );
                do_settings_sections( 'awoo_settings' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'awoo_option_group', // Option group
            'awoo_setup_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'awoo_section_id', // ID
            'AfterWoo - Setup - Required!', // Title
            array( $this, 'print_section_info' ), // Callback
            'awoo_settings' // Page
        );  

        add_settings_field(
            'awoo_partner_id', // ID
            'Afterbuy - Partner ID', // Title 
            array( $this, 'partner_id_callback' ), // Callback
            'awoo_settings', // Page
            'awoo_section_id' // Section           
        );      

        add_settings_field(
            'awoo_partner_password', 
            'Afterbuy - Partner Password', 
            array( $this, 'awoo_parpass_callback' ), 
            'awoo_settings', 
            'awoo_section_id'
        );
        
        add_settings_field(
            'awoo_user_id', // ID
            'Afterbuy - User ID', // Title 
            array( $this, 'awoo_uid_callback' ), // Callback
            'awoo_settings', // Page
            'awoo_section_id' // Section           
        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['awoo_partner_id'] ) )
            $new_input['awoo_partner_id'] = absint( $input['awoo_partner_id'] );

        if( isset( $input['awoo_partner_password'] ) )
            $new_input['awoo_partner_password'] = sanitize_text_field( $input['awoo_partner_password'] );
        
        if( isset( $input['awoo_user_id'] ) )
            $new_input['awoo_user_id'] = sanitize_text_field( $input['awoo_user_id'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function awoo_uid_callback()
    {
        printf(
            '<input type="text" id="awoo_user_id" name="awoo_setup_option[awoo_user_id]" value="%s" />',
            isset( $this->options['awoo_user_id'] ) ? esc_attr( $this->options['awoo_user_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function partner_id_callback()
    {
        printf(
            '<input type="text" id="awoo_partner_id" name="awoo_setup_option[awoo_partner_id]" value="%s" />',
            isset( $this->options['awoo_partner_id'] ) ? esc_attr( $this->options['awoo_partner_id']) : ''
        );
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function awoo_parpass_callback()
    {
        printf(
            '<input type="text" id="awoo_partner_password" name="awoo_setup_option[awoo_partner_password]" value="%s" />',
            isset( $this->options['awoo_partner_password'] ) ? esc_attr( $this->options['awoo_partner_password']) : ''
        );
    }
}

if( is_admin() )
    $awoo_settings_page = new awooSettingsPage();