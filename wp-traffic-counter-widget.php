<?php
/*
Plugin Name: Traffic Counter Widget
Plugin URI: http://www.pixme.org/wp-content/uploads/widget-traffic-counter/
Description: Counts the number of visitors of your blog and shows the traffic information on a widget
Author: Bogdan Nicolaescu
Version: 1.0.2
Author URI: http://www.pixme.org/
*/

function traffic_counter_control() {

  $options = get_option("widget_traffic_counter");

  if ($_POST['wp_wtc_Submit']){

    $options['wp_wtc_WidgetTitle'] = htmlspecialchars($_POST['wp_wtc_WidgetTitle']);
    $options['wp_wtc_WidgetText_Visitors'] = htmlspecialchars($_POST['wp_wtc_WidgetText_Visitors']);
    $options['wp_wtc_WidgetText_LastDay'] = htmlspecialchars($_POST['wp_wtc_WidgetText_LastDay']);
    $options['wp_wtc_WidgetText_LastWeek'] = htmlspecialchars($_POST['wp_wtc_WidgetText_LastWeek']);
    $options['wp_wtc_WidgetText_LastMonth'] = htmlspecialchars($_POST['wp_wtc_WidgetText_LastMonth']);
    $options['wp_wtc_WidgetText_Online'] = htmlspecialchars($_POST['wp_wtc_WidgetText_Online']);

    update_option("widget_traffic_counter", $options);
  }

?>
  <p><strong>Use options below to translate english labels</strong></p>
  <p>
    <label for="widget_traffic_counter">Text Title: </label>
    <input type="text" id="wp_wtc_WidgetTitle" name="wp_wtc_WidgetTitle" value="<?php echo ($options['wp_wtc_WidgetTitle'] =="" ? "Blog Traffic" : $options['wp_wtc_WidgetTitle']); ?>" />
  </p>
  <p>
    <label for="widget_traffic_counter">Text Visitors: </label>
    <input type="text" id="wp_wtc_WidgetText_Visitors" name="wp_wtc_WidgetText_Visitors" value="<?php echo ($options['wp_wtc_WidgetText_Visitors'] =="" ? "Visitors" : $options['wp_wtc_WidgetText_Visitors']); ?>" />
  </p>
  <p>
    <label for="widget_traffic_counter">Text Last 24 Hours: </label>:
    <input type="text" id="wp_wtc_WidgetText_LastDay" name="wp_wtc_WidgetText_LastDay" value="<?php echo ($options['wp_wtc_WidgetText_LastDay'] =="" ? "Last 24 hours" : $options['wp_wtc_WidgetText_LastDay']); ?>" />
  </p>
  <p>
    <label for="widget_traffic_counter">Text Last 7 Days: </label>:
    <input type="text" id="wp_wtc_WidgetText_LastWeek" name="wp_wtc_WidgetText_LastWeek" value="<?php echo ($options['wp_wtc_WidgetText_LastWeek'] =="" ? "Last 7 days" : $options['wp_wtc_WidgetText_LastWeek']); ?>" />
  </p>
  <p>
    <label for="widget_traffic_counter">Text Last 30 Days: </label>:
    <input type="text" id="wp_wtc_WidgetText_LastMonth" name="wp_wtc_WidgetText_LastMonth" value="<?php echo ($options['wp_wtc_WidgetText_LastMonth'] =="" ? "Last 30 days" : $options['wp_wtc_WidgetText_LastMonth']); ?>" />
  </p>
  <p>
    <label for="widget_traffic_counter">Text Online Now: </label>:
    <input type="text" id="wp_wtc_WidgetText_Online" name="wp_wtc_WidgetText_Online" value="<?php echo ($options['wp_wtc_WidgetText_Online'] =="" ? "Online now" : $options['wp_wtc_WidgetText_Online']); ?>" />
    <input type="hidden" id="wp_wtc_Submit" name="wp_wtc_Submit" value="1" />
  </p>
<?php
}

function get_wtc_options() {

  $options = get_option("widget_traffic_counter");
  if (!is_array( $options )) {
    $options = array(
                     'wp_wtc_WidgetTitle' => 'Blog Traffic',
                     'wp_wtc_WidgetText_Visitors' => 'Visitors',
                     'wp_wtc_WidgetText_LastDay' => 'Last 24 hours',
                     'wp_wtc_WidgetText_LastWeek' => 'Last 7 days',
                     'wp_wtc_WidgetText_LastMonth' => 'Last 30 days',
                     'wp_wtc_WidgetText_Online' => 'Online now'
                    );
  }
  return $options;
}

function get_traffic ($sex, $unique) {

  global $wpdb;
  $table_name = $wpdb->prefix . "wtc_log";
  return $wpdb->get_var($wpdb->prepare("SELECT COUNT(".($unique ? "DISTINCT IP" : "*").") FROM $table_name where Time > ".(time()-$sex) ) );
}


function view() {

  global $wpdb;
  $options = get_wtc_options();

  if ($_SERVER['HTTP_X_FORWARD_FOR'])
       $ip = $_SERVER['HTTP_X_FORWARD_FOR'];
  else
       $ip = $_SERVER['REMOTE_ADDR'];

  $table_name = $wpdb->prefix . "wtc_log";
  $user_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name where ".time()." - Time <= 3 and IP = '".$ip."'"));

  if (!$user_count) {
    $data = array (
                 'IP' => $ip,
                 'Time' => time()
                );
    $format  = array ('%s','%d');
    $wpdb->insert( $table_name, $data, $format );
  }

?>

  <p><strong><?php echo $options["wp_wtc_WidgetText_Visitors"]; ?></strong></p>

  <ul>
  <li><?php echo $options["wp_wtc_WidgetText_LastDay"].": ".number_format_i18n(get_traffic(86400,false)); ?>   </li>
  <li><?php echo $options["wp_wtc_WidgetText_LastWeek"].": ".number_format_i18n(get_traffic(604800,false)); ?>  </li>
  <li><?php echo $options["wp_wtc_WidgetText_LastMonth"].": ".number_format_i18n(get_traffic(2592000,false)); ?> </li>
  <li><?php echo $options["wp_wtc_WidgetText_Online"].": ".number_format_i18n(get_traffic(600, true)); ?>    </li>
  </ul>
  <small><a href="http://www.pixme.org/tehnologie-internet/wordpress-traffic-counter-widget/4228" target="_blank">Traffic Counter</a></small>
<?php
}

function widget_traffic_counter($args) {
  extract($args);

  $options = get_wtc_options();

  echo $before_widget;
  echo $before_title.$options["wp_wtc_WidgetTitle"];
  echo $after_title;
  view();
  echo $after_widget;
}

function wp_wtc_install_db () {
   global $wpdb;

   $table_name = $wpdb->prefix . "wtc_log";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

      $sql = "CREATE TABLE " . $table_name . " (
           IP VARCHAR( 17 ) NOT NULL ,
           Time INT( 11 ) NOT NULL ,
           PRIMARY KEY ( IP , Time )
           );";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
   }
}


function traffic_counter_init() {

  wp_wtc_install_db ();
  register_sidebar_widget(__('Traffic Counter'), 'widget_traffic_counter');
  register_widget_control(__('Traffic Counter'), 'traffic_counter_control', 300, 200 );
}

function uninstall_wtc(){

  global $wpdb;
  $table_name = $wpdb->prefix . "wtc_log";
  delete_option("widget_traffic_counter");
  $wpdb->query("DROP TABLE IF EXISTS $table_name");

}

add_action("plugins_loaded", "traffic_counter_init");
register_deactivation_hook( __FILE__, 'uninstall_wtc' );
?>
