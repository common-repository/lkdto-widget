<?php
/*
Plugin Name: Lkd.to Widget
Plugin URI: http://stephensauceda.com/
Version: 0.2
Description: A WordPress widget to display your lkd.to links
Author: Stephen Sauceda
Author URI: http://stephensauceda.com/
*/

class Lkdto_Widget extends WP_Widget
{
  function Lkdto_Widget()
  {
    $widget_ops = array('classname' => 'Lkdto_Widget', 'description' => 'A WordPress widget to display your lkd.to links');
    $this->WP_Widget('Lkdto_Widget', 'lkd.to Widget', $widget_ops);
  }

  function form($instance)
  {
    $instance = wp_parse_args((array) $instance, array( 'title' => '', 'lkdto_username' => '' ));
    $title = $instance['title'];
    $lkdto_username = $instance['lkdto_username'];
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>

  <p><label for="<?php echo $this->get_field_id('lkdto_username'); ?>">Username: <input class="widefat" id="<?php echo $this->get_field_id('lkdto_username'); ?>" name="<?php echo $this->get_field_name('lkdto_username'); ?>" type="text" value="<?php echo attribute_escape($lkdto_username); ?>" /></label></p>
<?php
  }

  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    $instance['lkdto_username'] = $new_instance['lkdto_username'];
    if (get_transient('lkdto_data')) {
      delete_transient( 'lkdto_data' );
    }
    return $instance;
  }

  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);

    echo $before_widget;
    $title = empty($instance['title']) ? 'lkd.to Links' : apply_filters('widget_title', $instance['title']);
    $lkdto_username = $instance['lkdto_username'];

    if (!empty($title))
      echo $before_title . $title . $after_title;;

    // Do Your Widget Thang...

      if (!get_transient( 'lkdto_data' )) {
        $response = wp_remote_get( 'http://lkd.to/api/' . $lkdto_username );

        try {
          $json = json_decode($response['body']);
        } catch (Exception $e) {
          $json = null;
        }
        set_transient( 'lkdto_data', $json, 3 * HOUR_IN_SECONDS );
      } //end if



        echo '<ul class="lkdto-links">';


        $links = get_transient('lkdto_data')->links;

        foreach ($links as $site) {
          echo '<li class=lkdto-' . $site->slug . '>';
          echo '<a href="' . $site->url . '" title="' . $site->site->name . '">' . $site->site->name . '</a>';
          echo '</li>';
        }


        echo '</ul>';

    echo $after_widget;
  }
}
add_action( 'widgets_init', create_function('', 'return register_widget("Lkdto_Widget");') );

?>