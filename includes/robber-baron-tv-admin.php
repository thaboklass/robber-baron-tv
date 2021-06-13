<?php

/*------------------------------------------------------------------*
 * The 'robber_baron_tv_admin' class
 * @author Thabo David Nyakallo Klass
/*------------------------------------------------------------------*/

if (!class_exists('robber_baron_tv_admin')) :

class robber_baron_tv_admin {
    // Set the default as not connected
    public $robber_baron_tv_connected = 'false';

    // The user's email - initialized to an empty string
    public $robber_baron_tv_email_address = '';

       
    /**
    * The 'robber_baron_tv_admin' constructor
    * 
    */
    
    public function __construct() {
        // Add RobberBaron.TV options
        $this->robber_baron_tv_add_options();

        // Set the option to true, if is in fact connected
        if (get_option('robber_baron_tv_connected') == 'true') {
            $this->robber_baron_tv_connected = 'true';
        }

        // Get the email from the options
        $this->robber_baron_tv_email_address = get_option('robber_baron_tv_email_address');

        // If the connection is not enable, try to connect
        if ($this->robber_baron_tv_email_address != '' && $this->robber_baron_tv_connected == 'false') {
            $this->robber_baron_tv_connect();
        }

        // This loads admin scripts
        add_action('admin_enqueue_scripts', array($this, 'robber_baron_tv_load_admin_scripts'));
    }
    
    
    /*------------------------------------------------------------------*
     * Menus
    /*------------------------------------------------------------------*/
    
    /**
     * Adds 'RobberBaron.TV' menu item
     *
     * Adds the 'Settings' menu titled 'Ethereum Settings'
     * as a top level menu item in the dashboard.
     *
     * @param	none
     * @return	none
    */
    
    public function robber_baron_tv_add_menu_page() {
        
        // Introduces a top-level menu page
        add_menu_page(
            'RobberBaron.TV Configuration',                                   // The text that is displayed in the browser title bar
            __('RobberBaron.TV'),                          // The text that is used for the top-level menu
            'manage_options',                                           // The user capability to access this menu
            'robber-baron-tv-configuration',                            // The name of the menu slug that accesses this menu item
            array($this, 'robber_baron_tv_configuration_display'),      // The name of the function used to display the page content
            '');
    } // end of function robber_baron_tv_add_menu_page
    
    
    
    /*------------------------------------------------------------------*
     * Sections, Settings and Fields
    /*------------------------------------------------------------------*/
    
    /**
     * Register section, fields and page
     *
     * Registers a new settings section and settings fields on the
     * 'RobberBaron.TV' page of the WordPress dashboard.
     *
     * @param	none
     * @return	none
    */
    
    public function robber_baron_tv_initialize_options() {
        // Introduce an new section that will be rendered on the new
        // settings page.  This section will be populated with settings
        // that will give the 'RobberBaron.TV' plugin its firebase
        // configuration options.
        add_settings_section(
            'robber_baron_tv_settings_section',                            // The ID to use for this section
            'RobberBaron Connection',                                      // The title of this section that is rendered to the screen
            array($this, 'robber_baron_tv_settings_section_display'),      // The function that is used to render the options for this section
            'robber-baron-tv-configuration'                                // The ID of the page on which the section is rendered
        );

        // Defines the settings field 'Your RobbberBaron.TV email'
        // which is a the RobberBaron email address of the website owner
        add_settings_field(
            'robber_baron_tv_email_address',                           // The ID of the setting field
            'Your RobbberBaron.TV email:',                             // The text to be displayed
            array($this, 'robber_baron_tv_email_address_display'),     // The function used to render the setting field
            'robber-baron-tv-configuration',                           // The ID of the page on which the setting field is rendered
            'robber_baron_tv_settings_section'                         // The section to which the setting field belongs
        );

        // Register the 'robber_baron_tv_email_address'
        // with the 'Ethereum Settings' section
        register_setting(
            'robber_baron_tv_settings',        // The section holding the settings fields
            'robber_baron_tv_email_address'    // The name of the settings field to register
        );

        // Simply displays the system environment including the connection status
        add_settings_field(
            'robber_baron_tv_connected',
            'Your RobberBaron.TV connection:',
            array($this, 'robber_baron_tv_system_environment'),
            'robber-baron-tv-configuration',
            'robber_baron_tv_settings_section'
        );
        
        // Register the 'robber_baron_tv_connected'
        // with the 'Functionality Options' section
        register_setting(
            'robber_baron_tv_settings',
            'robber_baron_tv_connected'
        );
		
		/// Send mail if there is any mail post data
        $this->robber_baron_tv_send_error_email();
    } // end of function robber_baron_tv_initialize_options
    
    
    
    /*------------------------------------------------------------------*
     * Callbacks
    /*------------------------------------------------------------------*/
    
    /**
     * This function is used to render all of the page content
     *
     * @param	none
     * @return	none
     */
    
    public function robber_baron_tv_configuration_display() {
    ?>
        <div class="wrap" id="robber_baron_tv_main_content">
            <div id="icon-options-general" class="icon32"></div>
            <h2>RobberBaron.TV</h2>
            <?php
            $active_tab = 'settings';
            if(isset($_GET['tab']) && !empty($_GET['tab'])) {
                $active_tab = esc_attr($_GET['tab']);
            } else if($active_tab == 'support') {
                $active_tab = 'support';
            } else {
                $active_tab = 'settings';
            }  // end if/else
            ?>
            <h2 class="nav-tab-wrapper">
                <a href="?page=robber-baron-tv-configuration&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Connection</a>
                <a href="?page=robber-baron-tv-configuration&tab=support" class="nav-tab <?php echo $active_tab == 'support' ? 'nav-tab-active' : ''; ?>">Support</a>
            </h2>
            <?php 
                if($active_tab == 'support') {
                ?>
                <?php
                    if(isset($_POST['robber_baron_tv_email_form_submitted']) && isset($_POST['robber_baron_tv_error_from_email'])
                        && !empty($_POST['robber_baron_tv_error_from_email']) && isset($_POST['robber_baron_tv_error_description'])
                        && !empty($_POST['robber_baron_tv_error_description'])) {

                        if(is_email($_POST['robber_baron_tv_error_from_email'])) {
                ?>
                            <div id="robber_baron_tv_upload_complete">
                                <p>Your support request has been sent. You will hear from a RobberBaron representative shortly.</p>
                            </div>
                <?php
                        } else {
                ?>
                            <div id="robber_baron_tv_email_invalid">
                                <p>The email you entered was invalid. Please try again.</p>
                            </div>
                <?php
                        }
                    }
                ?>
                <h3>Direct Support</h3>
				Are you having trouble connecting with your RobberBaron account? Are you encountering issues related to the RBTV widget? Please reach out to us below:
				<p><b>Before you continue, please download the manual by clicking here: <a href="https://robberbaron.s3.amazonaws.com/RobberBaron_Quick_Start_Guide.zip">DOWNLOAD MANUAL</a></b></p>
                <form id="robber_baron_tv_email_form" action="" method="post" enctype="multipart/form-data">
                    <?php echo wp_nonce_field('robber_baron_tv_email_form', 'robber_baron_tv_email_form_submitted'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enter your email:</th>
                            <td>
                                <input type="text" name="robber_baron_tv_error_from_email" id="robber_baron_tv_error_from_email"><br>
								<p>Enter the email you want the RobberBaron support team to reach you on.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Describe the problem:</th>
                            <td>
                                <textarea rows="4" cols="70" name="robber_baron_tv_error_description" id="robber_baron_tv_error_description"></textarea><br>
                                <p>Describe the exact nature of the problem you are experiencing in detail.</p>
                            </td>
                        </tr>
                    </table>
                    <input type="submit" class="button button-primary" name="robber_baron_tv_submit" value="Send Message" id="robber_baron_tv_submit">
                </form>
                <div id="robber_baron_tv_support_fields_not_filled">
                    <p>It appears that you may have not filled one or more fields.  Please make sure the email and error description fields are filled. Also make sure that fields are filled correctly.</p>
                </div>
                <?php
            ?>
			<?php
			} else {
            ?>
            <form method="post" id="robber_baron_tv_save_changes_form" action="options.php">
            <?php
                // Outputs pertinent nonces, actions and options for
                // the section
                settings_fields('robber_baron_tv_settings');
                
                // Renders the setting sections added to the page
                // 'Configuration Settings'
                do_settings_sections('robber-baron-tv-configuration');
                
                // Renders a submit button that saves all of the options
                // pertaining to the settings fields
                if(array_key_exists('settings-updated', $_REQUEST) && $_REQUEST['settings-updated']) {
                    ?>
                    <div id="robber_baron_tv_connection_complete">
                        <p><b>Your connection request was sent. Please refresh the page to see if the connection has been enabled.</b></p>
                    </div>
                    <?php
                } else {
                    submit_button('Connect To RBTV');
                }
            ?>
            </form>
            <div id="robber_baron_tv_support_fields_not_filled" title="Please fill all fields!">
                <p>It appears that one of your inputs contains quotes. Please remove the quotes and save again.</p>
            </div>
            <?php
            }
			?>
        </div>
    <?php
    }
    
    
    /**
     * Inline 'RobberBaron.TV Settings' description
     *
     * Displays an explanation of the role of the 'Configuration
     * Settings' section.
     *
     * @param	none
     * @return	none
     */
    
    public function robber_baron_tv_settings_section_display() {
        echo esc_html("These are your RobberBaron connection settings. These will be used to connect and update your RobberBaron connection.");
    }
	

	/**
     * Renders 'Email Address'
     *
     * Renders the input field for the 'Ethereum Address'
     * setting in the 'Ethereum Settings'
     * section.
     *
     * @param	none
     * @return	none
     */
    
    public function robber_baron_tv_email_address_display() {
		?>
			<input type="text" name="robber_baron_tv_email_address" id="robber_baron_tv_email_address" value="<?php echo esc_html(get_option('robber_baron_tv_email_address')); ?>" class="regular-text code" />
			<p>Your RobberBaron email address. Enter the address with <b>no quotes</b>.</p>
			<p>This is the email address you used when signing up for RobberBaron.</p>
		<?php    
    } // end of robber_baron_tv_email_address_display
    

    /**
     * Renders 'System Environment' section
     *
     * Renders the 'System Environment' section
     * setting in the 'Functionality Options' section.
     *
     * @param	none
     * @return	none
     */
    public function robber_baron_tv_system_environment() {
        ?>
            <h4><?php _e('System Environment');?></h4>
            <ul>
                <?php 
                if(is_array($_SERVER)):?>
                        <li><strong><?php _e('Server');?></strong> <span><?php echo $_SERVER['SERVER_SOFTWARE'];?></span></li>
                <?php
                endif;
                ?>
                <?php if(function_exists('phpversion')):?>
                <li><strong><?php _e('PHP');?></strong> <span><?php echo phpversion();?></span></li>
                <?php endif;?>
                <li><strong><?php _e('RBTV CONNECTION: ');?></strong> <span><?php if($this->robber_baron_tv_connected == 'true'){ echo '<span class="robber_baron_tv_connected_true">ENABLED</span>'; } else { echo '<span class="robber_baron_tv_connected_false">DISABLED</span>'; }?></span></li>
                <?php 
                if($this->robber_baron_tv_connected != 'true') {
                ?>
                <li><b>(When enabled, the widget will point users to your RobberBaron content.)</b></li>
                <?php
                }
                ?>
            </ul>
            
        <?php    
    } // end of robber_baron_tv_system_environment
	

	/**
    * Sends an error email to the Spreebie support team
    *
    * @param    none
    * @return   none
    */

    function robber_baron_tv_send_error_email() {
        // If the $_POST data and nonce are set, upload the data
        // within the error inputs
        if(isset($_POST['robber_baron_tv_email_form_submitted']) && wp_verify_nonce($_POST['robber_baron_tv_email_form_submitted'], 'robber_baron_tv_email_form')) {
            // the 'from' email
            
            if (isset($_POST['robber_baron_tv_error_from_email']) && !empty($_POST['robber_baron_tv_error_from_email'])
                && isset($_POST['robber_baron_tv_error_description']) && !empty($_POST['robber_baron_tv_error_description'])) {
                
                $robber_baron_tv_from_email = is_email($_POST['robber_baron_tv_error_from_email']);

                if ($robber_baron_tv_from_email) {
                    $robber_baron_tv_from_email = sanitize_email($robber_baron_tv_from_email);

                    // the to email
                    $robber_baron_tv_to_email = "thabo@openbeacon.biz";

                    $sanitized_robber_baron_tv_error_description = sanitize_text_field($_POST['robber_baron_tv_error_description']);
                    $error_description = $sanitized_robber_baron_tv_error_description;

                    $message = $error_description . " - respond to: " . $robber_baron_tv_from_email;
                    wp_mail($robber_baron_tv_to_email, "RobberBaron plugin support request.", $message);
                }
            }
        }
	}
	

    /**
    * Add options for new activation
    *
    * This checks whether or not backend options that define the basic
    * functionality have been added and if not, they are added
    * with what have been determined as the most efficient defaults
    *
    * @param	none
    * @return	none
   */
   
    public function robber_baron_tv_add_options() {
        if (!get_option('robber_baron_tv_email_address')) {
            add_option('robber_baron_tv_email_address', '');
            add_option('robber_baron_tv_connected', '');
            add_option('robber_baron_tv_user_id', '');
        }
    }


    /**
    * Load scripts
    *
    * Load all relevant styles and scripts - in this case we load just
    * one stylesheet and two javascript files
    *
    * @param	none
    * @return	none
    */
   
    public function robber_baron_tv_load_admin_scripts() {
        wp_register_style('robber_baron_tv_admin_css', plugins_url('../css/admin.css', __FILE__));        

        wp_enqueue_style('robber_baron_tv_admin_css');
    }


    /**
    * Connect the widget to the user's RobberBaron account by
    * sending a JSON reguest that will return the user's IS
    *
    * @param    none
    * @return   none
    */

    function robber_baron_tv_connect() {
        // Validate email
        $robber_baron_tv_from_email = is_email($this->robber_baron_tv_email_address);

        if ($robber_baron_tv_from_email) {
            // Construct a JSON object
            $robber_baron_email_object = (object) [
                'email' => $this->robber_baron_tv_email_address
            ];

            // Encode it to actual JSON
            $robber_baron_JSON = json_encode($robber_baron_email_object);
            
            // Initiate the JSON request
            $data = wp_remote_post('https://robberbaron.tv/api/v1/getuserid', array(
                'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
                'body'        => $robber_baron_JSON,
                'method'      => 'POST',
                'data_format' => 'body',
            ));

            // Parse the response
            $response_array = json_decode($data['body'], true);

            // If the response is 'not found', the email was wrong
            if ($response_array['uid'] != 'not found') {
                update_option('robber_baron_tv_connected', 'true');
                update_option('robber_baron_tv_user_id', $response_array['uid']);
            } else {
                update_option('robber_baron_tv_user_id', '');
            }
        }
    }
}

endif;
?>