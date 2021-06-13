<?php
/*
 Plugin Name: RobberBaron.TV
 Plugin URI: https://openbeacon.biz/robberbaron-tv-wordpress-plugin/
 Description: A simple and intuitive plugin that connects your personal WordPress website with your premium content on RobberBaron.TV through a simple widget at the botton of your site's page. To get started: 1) Click the "Activate" link, 2) Go to Appearance->Widgets and drag and drop the RobberBaron.TV widget anywhere in the widget area and 3) Click on any page or post and the RobberBaron.TV widget will be at the footer, directing users to your RobberBaron content.
 Author: RobberBaron.TV
 Version: 1.0.1
 Author URI: https://robberbaron.tv/
*/


/*------------------------------------------------------------------*
 * Constants and dependencies
/*------------------------------------------------------------------*/

/**
 * Define constants
 * 
 */

define( 'ROBBER_BARON_TV_VERSION', '1.0.1' );
define( 'ROBBER_BARON_TV_ROOT' , dirname( __FILE__ ) );
define( 'ROBBER_BARON_TV_FILE_PATH' , ROBBER_BARON_TV_ROOT . '/' . basename( __FILE__ ) );
define( 'ROBBER_BARON_TV_URL' , plugins_url( '/', __FILE__ ) );


/**
 * Include other plugin dependencies
 * 
 */

require ROBBER_BARON_TV_ROOT . '/includes/robber-baron-tv-admin.php';


/*------------------------------------------------------------------*
 * The 'robber_baron_tv' class: a WordPress widget
 * @author Thabo David Nyakallo Klass
/*------------------------------------------------------------------*/

if(!class_exists('robber_baron_tv')) :

    class robber_baron_tv extends WP_Widget {
        // A boolean for the footer position
        public $robber_baron_tv_is_in_footer;

        // The initial widget body text
        public $robber_baron_tv_widget_text;

        // The RobberBaron default URI
        public $robber_baron_tv_widget_url = "https://robberbaron.tv/";

        // The user_id that will be used to point to the user's content
        public $robber_baron_tv_user_id; 
        
        
        /**
        * The 'robber_baron_tv' constructor
        * 
        */
        
        function __construct() {
            // This adds backend options that define the basic
            // functionality
            $this->robber_baron_tv_add_options();

            // Initialize it to false
            $this->robber_baron_tv_is_in_footer = false;

            // Get the user_id
            $this->robber_baron_tv_user_id = get_option('robber_baron_tv_user_id');
            
            // Backend widget text and titles - this pertains
            // to how the RobberBaron.TV widget appears in the backend
            $params = array('description' => "Connects you and your users to your premium RobberBaron content.");
            parent::__construct("robber_baron_tv", $name = __("RobberBaron.TV"), $params);
            
            // This loads all relevant external scripts
            add_action('wp_enqueue_scripts', array($this, 'robber_baron_tv_load_scripts'));
        }
        
        
        /**
        * Back-end widget form.
        *
        * @see WP_Widget::form()
        *
        * @param array $instance Previously saved values from database.
        */
        
        public function form($instance) {
            extract((array)$instance);
            ?>
            <p>
                <label for="<?php echo $this->get_field_id('title');?>">Title: </label>
                <input class="widefat"
                       id="<?php echo $this->get_field_id('title');?>"
                       name="<?php echo $this->get_field_name('title');?>"
                       value="<?php if(isset($title)) echo esc_attr($title); ?>" />
            </p>
            <?php
        }
        
        
        /**
        * Sanitize widget form values as they are saved.
        *
        * @see WP_Widget::update()
        *
        * @param array $new_instance Values just sent to be saved.
        * @param array $old_instance Previously saved values from database.
        *
        * @return array Updated safe values to be saved.
        */
        
        public function update($new_instance, $old_instance) {
            $instance = $old_instance;
            $instance['title'] = strip_tags($new_instance['title']);
            $instance['code'] = $new_instance['code'];
            return $instance;
        }
        
        
        /**
        * Front-end display of widget.
        *
        * @see WP_Widget::widget()
        *
        * @param array $args     Widget arguments.
        * @param array $instance Saved values from database.
        */
        
        public function widget($args, $instance) {
            // Check any variation of the footer position
            // because it the label varies depending on 
            // the theme.
            if (strpos($args['name'], 'Footer') !== false) {
                $this->robber_baron_tv_is_in_footer = true;
            }

            // Check any variation of the footer position
            // because it the label varies depending on 
            // the theme - in this case, look for the word
            // 'Bottom'
            if (strpos($args['name'], 'Bottom') !== false) {
                $this->robber_baron_tv_is_in_footer = true;
            }

            if (($args['name'] == 'Footer 1') || ($args['name'] == 'Footer 2') ||
                ($args['name'] == 'Content Bottom 1') || ($args['name'] == 'Content Bottom 2') ||
                ($args['name'] == 'Footer 3') || ($args['name'] == 'Footer Widgets') ||
                ($this->robber_baron_tv_is_in_footer)) {
                // If the widget is positioned in the footer, load the footer style
                // to override the existing style
                wp_register_style('robber_baron_tv_widget_css_footer', plugins_url('css/style_footer.css', __FILE__));                
                wp_enqueue_style('robber_baron_tv_widget_css_footer');
            }
            
            // This points the widget to the user's content
            if ($this->robber_baron_tv_user_id != '') {
                $this->robber_baron_tv_widget_url = "https://robberbaron.tv/dashboard-profile/" . $this->robber_baron_tv_user_id;
            }
            ?>
            <!-- The HTML code for the front-end wdiget begins here -->
            <aside id="robber_baron_tv_widget" class="widget">
                <div class="robber_barorn_tv_container text-center" id="robber_baron_tv_clickable_logo">
                    <div class="row">
                        <div>
                            <a href=<?php echo $this->robber_baron_tv_widget_url; ?> target="_blank" id="robber_baron_tv_add_class"><img alt="Watch Premium Content" class="robber_baron_tv_img" src="<?php echo plugin_dir_url( __FILE__ ) . 'robber_baron_tv_logo.png'; ?>"></a>
                        </div>     
                    </div>
                </div>
            </aside>
            <?php
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
        
        public function robber_baron_tv_load_scripts() {
            wp_register_style('robber_baron_tv_widget_css', plugins_url('css/style.css', __FILE__));
            wp_enqueue_style('robber_baron_tv_widget_css');
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
    }

endif;


/**
* Widget callback: registers RobberBaron.TV
* widget
*
* @param    none
* @return   none
*/

function robber_baron_tv_widget_reg() {
    register_widget('robber_baron_tv');
}


/**
* Deactivation callback: removes assorted data
* that will be added in later versions of RobberBaron.TV
*
* @param    none
* @return   none
*/

function robber_baron_tv_deactivate() {
    // do nothing, not yet; at least not in version 1.0.1
}

$robber_baron_tv_admin_1 = new robber_baron_tv_admin();

// This add a settings page
add_action('admin_menu', array($robber_baron_tv_admin_1, 'robber_baron_tv_add_menu_page'));

// This adds settings functionality to the settings page
add_action('admin_init', array($robber_baron_tv_admin_1, 'robber_baron_tv_initialize_options'));

add_action('widgets_init', 'robber_baron_tv_widget_reg');

register_deactivation_hook(__FILE__, 'robber_baron_tv_deactivate');
?>