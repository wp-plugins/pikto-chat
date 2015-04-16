<?php
/**
 * Plugin Name: Pikto Chat
 * Plugin URI: http://www.piktogramstudio.com/en/plugins/piktochat
 * Description: Piktogram studio chat
 * Version: 0.0.11
 * Author: Piktogram Studio DOO
 * Author URI: http://www.piktogramstudio.com/en/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: piktochat
 * 
 * {Plugin Name} is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version. 
 * 
 * {Plugin Name} is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *  
 * You should have received a copy of the GNU General Public License
 * along with {Plugin Name}. If not, see {License URI}.
 */

//Widget registration
add_action( 'widgets_init', array( 'Piktochat', 'pikto_chat_register' ) );

//Ajax methods
add_action( 'wp_ajax_piktochat_read_msg'  , array( 'Piktochat', 'piktochat_read_msg'   ) );
add_action( 'wp_ajax_piktochat_insert_msg', array( 'Piktochat', 'piktochat_insert_msg' ) );

//Hook for creating the table for chat messages in the database when the plugin is activated
register_activation_hook( __FILE__, array( 'Piktochat', 'piktochat_create_table' ) );

class Piktochat extends WP_Widget
{
    //Widget registration
    public static function pikto_chat_register() {
        register_widget( __CLASS__ );
    }
    
    //Widget constructor
    public function __construct() {
        parent::__construct( 'piktochat', 'Piktochat' );
    }
    
    //Widget method for displaying the button widget
    public function widget( $args, $instance ) {
        if ( is_user_logged_in() ) {
            echo $args['before_widget'];
            if ( ! empty( $instance['title'] ) ) { echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title']; }
            include 'button.php';
            echo $args['after_widget'];
            include 'view.php';
        }
    }
    
    //Widget form for choosing a number for displayed chat messages and color scheme in chat window
    public function form( $instance ) {
        $msg_num = $instance['option'];
		$color_scheme = $instance['color'];
?>
        <p class='options-widget'><?php _e( 'Choose message output:', 'piktochat' ); ?></p>
        <select id='<?php echo $this->get_field_id( 'option' ); ?>' name='<?php echo $this->get_field_name( 'option' ); ?>'>
            <option value='5'    <?php if ( $msg_num ==    5 ) { echo 'selected'; } ?>>5</option>
            <option value='50'   <?php if ( $msg_num ==   50 ) { echo 'selected'; } ?>>50</option>
            <option value='100'  <?php if ( $msg_num ==  100 ) { echo 'selected'; } ?>>100</option>
            <option value='200'  <?php if ( $msg_num ==  200 ) { echo 'selected'; } ?>>200</option>
            <option value='400'  <?php if ( $msg_num ==  400 ) { echo 'selected'; } ?>>400</option>
            <option value='1000' <?php if ( $msg_num == 1000 ) { echo 'selected'; } ?>>1000</option>
        </select>
        
        <p class='options-widget'><?php _e( 'Choose color scheme:', 'piktochat' ); ?></p>
        <select id='<?php echo $this->get_field_id( 'color' ); ?>' name='<?php echo $this->get_field_name( 'color' ); ?>'>
            <option value='pikto-color-1' <?php if ( $color_scheme ==  'pikto-color-1' ) { echo 'selected'; } ?>>Dark</option>
            <option value='pikto-color-2' <?php if ( $color_scheme ==  'pikto-color-2' ) { echo 'selected'; } ?>>Light</option>
            <option value='pikto-color-3' <?php if ( $color_scheme ==  'pikto-color-3' ) { echo 'selected'; } ?>>Colored</option>
            <option value='pikto-color-4' <?php if ( $color_scheme ==  'pikto-color-4' ) { echo 'selected'; } ?>>Colored (Inversed)</option>
        </select>
<?php
    }
    
    //Widget method for storing choosen options for message number and color scheme
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        
        $instance['option'] = ( ! empty( $new_instance['option'] ) ) ? strip_tags( $new_instance['option'] ) : 5;
        $instance['color']  = ( ! empty( $new_instance['color']  ) ) ? strip_tags( $new_instance['color']  ) : 'pikto-color-1';
        
        return $instance;
    }
    
    //Method for inserting new messages into the database
    public static function piktochat_insert_msg(){
        global $wpdb;

        $text = filter_input( INPUT_POST, 'pikto_fld_msg', FILTER_SANITIZE_SPECIAL_CHARS );

        $wpdb->insert(
            $wpdb->base_prefix . 'piktochatmessages',
            array(
                'sender'  => get_current_user_id(),
                'message' => $text
            ),
            array(
                '%d',
                '%s'
            )
        );
    }
    
    //Method for reading chat messages from the database
    public static function piktochat_read_msg() {
        global $wpdb;
        
        $msg_num = get_option( 'widget_piktochat' )[2]['option'];
        
        $result = $wpdb->get_results(
            'SELECT * '
          . 'FROM ('
              . 'SELECT * '
              . 'FROM ' . $wpdb->base_prefix . 'piktochatmessages '
              . 'ORDER BY senddate DESC '
              . 'LIMIT ' . $msg_num . ''
          . ') sub '
          . 'ORDER BY senddate ASC',
            ARRAY_A
        );
        
        $arrJson = array();
 
        $current_id = get_current_user_id();
        $last_user = 0;
        foreach( $result as $row ) {
            $userdata = get_userdata( $row['sender'] );
            $display_name = $userdata->display_name;
            $time = date( 'H:i', strtotime( $row['senddate'] ) );
            
            if( $last_user == $row["sender"] ) {
                    $display_name = '';
            } else {
                if ( $row['sender'] == $current_id ) {
                    $display_name = '<span style="padding-left:20px; font-size:15px;">' . $display_name . '</span>';
                }
            }

            $last_user = $row['sender'];
			
            $arrJson[] = array(
                'sender'   => $display_name,
                'senddate' => $time,
                'message'  => $row['message']
            );
        }
        
        echo json_encode( $arrJson );
        
        die();
    }
    
    //Method for creating a table in the database for storing chat messages upon plugin activation
    public static function piktochat_create_table() {
        global $wpdb;
        
        $charset_collate = '';
        
        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }

        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }
        
        $table_name = $wpdb->prefix . "piktochatmessages";
        
        $sql =  "CREATE TABLE $table_name (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    sender bigint(20) NOT NULL,
                    senddate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    message varchar(1024) NOT NULL,
                    PRIMARY KEY  (id)
                ) $charset_collate;";

        require_once( ABSPATH . "wp-admin/includes/upgrade.php" );
        dbDelta( $sql );   
    }
}
