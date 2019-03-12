<?php
/*
Plugin Name: wshbr-wordpress-review
Plugin URI: https://github.com/Machigatta/wshbr-wordpress-review
Description: Make your post to a simple review
Author: Machigatta
Author URI: https://machigatta.com/
Version: 2.1
Stable Tag: 2.1
 */
class wsreview
{
    public function __construct()
    {
        //Action-Binds for Wordpress-Frontend
        add_action('the_content', array($this,'addContent'));
        add_action('the_excerpt', array($this,'disablePlugin'));
        //Add Something to the scripts-loader
        add_action('wp_enqueue_scripts', array($this, 'addStylesAndScripts'));
        add_action( 'admin_enqueue_scripts', array($this, 'addStylesAndScripts') );
        
    }

    //Add Content to page
    function addContent($content) {
        $options = get_option('wsreview_settings');
        $post_id = get_the_ID();
        $post_object = $this->getPostObject($post_id);
        $plugin = $this->renderPlugin($options, $post_object);
        if(is_single()){
            $content = $content . $plugin;
        }
    
        return $content;
    }
    function disablePlugin($excerpt) {
		return preg_replace('/<article>.*<\/article>/', '', $excerpt);
	}
    //Add Styles and Scripts to the plugin in the right version
    public function addStylesAndScripts()
    {
        $options = get_option('wsreview_settings');
        wp_enqueue_style('wsreview-font', 'https://fonts.googleapis.com/css?family=Open+Sans');
        wp_enqueue_style('wsreview-style', trailingslashit(plugin_dir_url(__FILE__)) . 'assets/css/style.css', array(), "0.2.0");
        wp_enqueue_script('wsreview-script', trailingslashit(plugin_dir_url(__FILE__)) . 'assets/js/wsreview.js', array('jquery'), "0.1.5");
    }
    //Draw the plugin
    public function renderPlugin($options, $post_object)
    {
        $ret = "";
        if(get_post_meta($post_object["id"],'isReview')){
            
            $ret .= '<div class="review-container row">
                        <div class="col-md-2">
                            <div class="rating">
                                <small>'. get_post_meta($post_object["id"],'reviewValue')[0] .' / 10</small>
                            </div>
                            <img src="'.  get_avatar_url( $post_object["author_id"], 32 ).'" class="img-responsive">
                            <p class="review-author">'.$post_object["author"].'</p>
                        </div>
                        <div class="col-md-10">
                            <p class="align-justify review-short">
                                '. get_post_meta($post_object["id"],'reviewShort')[0] .'
                            </p>
                        </div>
                    </div>';
            
        }
        return $ret;
	}
    //Taken from a gist to retrieve a usable post-object for other reasons
    public function getPostObject($post_id)
    {
        $post_url = get_permalink($post_id);
        $title = strip_tags(get_the_title($post_id));
        $tagObjects = get_the_tags($post_id);
        $single = is_single();
        $tags = "";
        if (!empty($tagObjects)) {
            $tags .= $tagObjects[0]->name;
            for ($i = 1; $i < count($tagObjects); $i++) {
                $tags .= "," . $tagObjects[$i]->name;
            }
        }
        $category = get_the_category($post_id);
        $categories = "";
        if (!empty($category)) {
            $categories .= $category[0]->name;
            for ($i = 1; $i < count($category); $i++) {
                $categories .= "," . $category[$i]->name;
            }
        }
        $author = get_the_author();
        $date = get_the_date('U', $post_id) * 1000;
        $comments = get_comments_number($post_id);
        $author_id = get_post_field( 'post_author', $post_id );
        $post_object = array(
            'id' => $post_id,
            'url' => $post_url,
            'title' => $title,
            'tags' => $tags,
            'categories' => $categories,
            'comments' => $comments,
            'date' => $date,
            'author' => $author,
            'author_id' => $author_id,
            'single' => $single,
            'img' => get_the_post_thumbnail_url($post_id),
        );
        return $post_object;
    }
    public function renderPluginAsWidget($postCount){
        ?>
    <div>
        <span class="sidebar-head">» Reviews</span>
            <div class="sidebar-block review-block">
                <center>
                <?php
                        $the_query = new WP_Query( array(
                            'post_type' => 'post',
                            'post_status' => 'publish',
                            'posts_per_page' => $postCount,
                            'category_name' => "review",
                            'orderby' => 'id')
                        );

                        if ( $the_query->have_posts() ) {
                            echo '<div class="review-border"></div>';
                            while ( $the_query->have_posts() ) {
                                
                                echo '<div class="reviewBlock">';
                                $the_query->the_post();
                                //  if($sliderCaption == ""){
                                //      echo $sliderCaption = get_the_title();
                                //  }
                                echo '<a href="'. esc_url( get_permalink()).'" target="_blank">';
                                echo '<div class="sidebar-picture" data-content="'.get_the_title().'">';
                                if(has_post_thumbnail()){
                                    the_post_thumbnail("",array( 'class' => 'img-responsive no-spoiler-image' ));
                                    }
                                echo '</div></a><p class="reviewPragraph">'.get_the_excerpt().'</p></div>';
                            }
                            /* Restore original Post Data */
                            wp_reset_postdata();
                            echo '<div class="review-border"></div>';
                        } else {
                            // no posts found
                        }
                        ?>      
                </center>
            </div>
        </div>

    <?php
    }
}
//base init
$wsreview = new wsreview();

function addMetaBox(){
    add_meta_box('wsreview', __('wshbr-review','post-expirator'), 'wsreview_meta_box', 'post', 'normal', 'core');
}

function wsreview_meta_box($post) { 
    
    wp_nonce_field( plugin_basename( __FILE__ ), 'wsreview_nonce' );
    $isReview = get_post_meta($post->ID,"isReview",true);
    // Get default month
    echo "<div>
    <input id='isReview' type=\"checkbox\" name='isReview' value='true'";
    echo ($isReview == "1") ? "checked='checked'" : "";
    echo ">markiert als Review
    <div id='reviewOptions'>
    <hr>
    <h4>".__("Rating","wsreview")."</h4>
    <div class='wsreview-slider-container'>
        <output id='wsreview-number' for='wsreview-slider'>0</output>
        <input id='wsreview-slider' name='reviewValue' type='range' min='0' max='10' value='".get_post_meta($post->ID,"reviewValue")[0] ."' step='0.5' style='width:100%'>
	</div>
    <h4>".__("Short-Review","wsreview")."</h4>
    <textarea rows=\"10\" cols=\"30\" name=\"reviewShort\" style='width:100%'>".get_post_meta($post->ID,"reviewShort",true)."</textarea></div>
    </div>";
}
add_action ('add_meta_boxes','addMetaBox');

//On Save, save data
function wsreview_field_save($post_id) {
    // check if this isn't an auto save
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;
    // security check
    if ( !wp_verify_nonce( $_POST['wsreview_nonce'], plugin_basename( __FILE__ ) ) )
        return;

    if ( isset( $_POST['isReview'] ) ) :
        if($_POST['isReview'] == "true"){
            update_post_meta( $post_id, 'isReview', "1");
            update_post_meta( $post_id, 'reviewShort', $_POST['reviewShort']);
            update_post_meta( $post_id, 'reviewValue', $_POST['reviewValue']);
        }else{
            update_post_meta( $post_id, 'isReview', "0");	
            update_post_meta( $post_id, 'reviewShort', $_POST['reviewShort']);
            update_post_meta( $post_id, 'reviewValue', $_POST['reviewValue']);
        }
    endif;
        
}
add_action( 'save_post', 'wsreview_field_save');
?>
