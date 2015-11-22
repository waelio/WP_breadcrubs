<?php
/**
 * Created by PhpStorm.
 * User: wahbehw
 * Date: 24/10/2015
 * Time: 9:52 AM
 */

/**
 * @param $object int|object|null
 * @param null $landscape
 * @param null $tail
 * @return null|string
 */
function the_bread_crumbs($object , $landscape = null ,$tail = null){
    /**
     * defaulting all values to null
     */
    $f_home = null;$f_ancestors = null;$f_self = null;$f_children = null;$f_siblings = null;$f_tail = null;$rs = null;

    /**
     * ol open and close tags
     */
    $f_open  = '<ol class="breadcrumb">';
    $f_close = '</ol>';
    $home_li = '<li><a href="'.home_url() .'">'.  __('FM Home','fm-functions').'</a></li>';

    /**
     * Process The desired object, post or category. If null then return the home and tail
     */
    switch(true) {
        case (is_object($object)):
            if (property_exists($object,'term_id')/*is_numeric($object->term_id)*/){
                $object_type = $object->taxonomy;
            }else{
                $object_type = $object->post_type;
            }
            break;

        case (is_numeric($object)):
            $post = get_post($object);
            if ((is_object($post)) && (is_single($post))) {
                $object = $post;
                $object_type = $object->post_type;
            } else {
                $object = get_category($object);
                if(is_object($object))
                    $object_type = $object->taxonomy;
                else
                    return false;
            }
            break;

        case(is_null($object)):
            return $f_open.$home_li.'<li><a>'.$tail.'</a></li>'.$f_close;
            break;

        default:
            return new WP_Error( 'What the ... !', __( "Give me something to work with, an object (post or category) or an int (cat_ID or post ID)", "php-function" ) );

    }

    /**
     * converting args to array if they are a string
     */
    $return_array = false;
    if (is_array($landscape)){
        $return_array = true;
    }elseif(is_string($landscape)){
        $landscape = explode(' ',$landscape);
        $return_array = false;
    }

    /**
     * Getting the view parameters for processing
     */
    $display_home       = (in_array('home'      ,$landscape));
    $display_ancestors  = (in_array('ancestors' ,$landscape));
    $display_self       = (in_array('self'      ,$landscape));
    $display_children   = (in_array('children'  ,$landscape));
    $display_siblings   = (in_array('siblings'  ,$landscape));


    /**
     * Home Link
     */
    if($display_home){
        $f_home .= $home_li;
    }
    /**
     * optional Tail
     */
    if (!is_null($tail)) {
        $f_tail .= '<li><a>'.$tail.'</a></li>';
    }
    /**
     * Process breadcrumbs based on type
     */
    switch($object_type){
        case 'post':
            if ($display_ancestors) {
                $ancestors = get_ancestors( $object->ID , 'post' );
                foreach ($ancestors as $ancestor) {
                    $f_ancestors .= '<li><a href="'. get_permalink($ancestor).'">'. get_nice_name($ancestor) .'</a></li>';
                }
            }
            if ($display_self){
                $f_self .= '<li><a href="'.get_permalink( $object->cat_ID ) .'">'.get_nice_name($object->cat_ID).'</a></li>';
            }
            if ($display_children){
                $children = get_children(array('post_parent' => $object->ID ));
                foreach ($children as $child) {
                    if (get_post_type( $child->ID ) =='post')
                        $f_children .= '<li><a href="'.get_permalink($child->ID).'">'.get_nice_name($child->ID).'</a></li>';
                }
            }
            if ($display_siblings){
                $parent_id = wp_get_post_parent_id($object->ID);
                $siblings = get_children(array('post_parent' => $parent_id ));
                foreach($siblings as $sibling){
                    if (get_post_type( $sibling->ID ) =='post')
                        $f_siblings .= ($sibling->ID != $object->ID) ? '<li><a href="'.get_permalink($sibling->ID).'">'.get_nice_name($sibling->ID).'</a></li>':'';
                }
            }
            break;
        case 'page':
            if ($display_ancestors) {
                $ancestors = get_ancestors( $object->ID , 'page' );
                foreach ($ancestors as $ancestor) {
                    $f_ancestors .= '<li><a href="'. get_permalink($ancestor).'">'. get_nice_name($ancestor) .'</a></li>';
                }
            }
            if ($display_self){
                $f_self .= '<li><a href="'.get_permalink( $object->cat_ID ) .'">'.get_nice_name($object->cat_ID).'</a></li>';
            }
            if ($display_children){
                $children = get_children(array('post_parent' => $object->ID ));
                foreach ($children as $child) {
                    if (get_post_type( $child->ID ) =='page')
                        $f_children .= '<li><a href="'.get_permalink($child->ID).'">'.get_nice_name($child->ID).'</a></li>';
                }
            }
            if ($display_siblings){
                $parent_id = wp_get_post_parent_id($object->ID);
                $siblings = get_children(array('post_parent' => $parent_id ));
                foreach($siblings as $sibling){
                    if (get_post_type( $sibling->ID ) =='page')
                        $f_siblings .= ($sibling->ID != $object->ID) ? '<li><a href="'.get_permalink($sibling->ID).'">'.get_nice_name($sibling->ID).'</a></li>':'';
                }
            }
            break;
        case 'category':
            if ($display_ancestors) {
                $ancestors = get_ancestors( $object->cat_ID,'category' );
                while (count($ancestors) > 0) {
                    $category_id = array_pop($ancestors);
                    (is_null($category_id)) ?:
                        $f_ancestors .= '<li><a href="' . get_category_link($category_id) . '">' . get_cat_name($category_id) . '</a></li>';
                }
            }
            if ($display_self){
                if (get_category_link( $object->cat_ID ) != '') {
                    $f_self .= '<li><a href="'.get_category_link( $object->cat_ID ) .'">'.get_cat_name($object->cat_ID).'</a></li>';
                }
            }
            if ($display_children) {
                $children = get_categories(array( 'orderby' => 'name', 'parent' => $object->term_id));
                foreach($children as $child){
                    $f_children .= '<li><a href="' . get_category_link($child->term_id) . '">' . get_cat_name($child->term_id) . '</a></li>';
                }
            }
            if ($display_siblings){
                $siblings = get_categories(array( 'order'=>'ASC' ,'orderby' => 'name', 'parent' => $object->category_parent));
                foreach($siblings as $sibling){
                    $f_siblings .= '<li><a href="' . get_category_link($sibling->term_id) . '">' . get_cat_name($sibling->term_id) . '</a></li>';
                }
            }
            break;
    }

    if($return_array){
        $breadcrumbs = array();
        $breadcrumbs['open'] = $f_open;
        for($i=0;$i<count($landscape);$i++){
            $breadcrumbs[$landscape[$i]] =  ${'f_'.$landscape[$i] };
        }
        $breadcrumbs ['tail']= $f_tail;
        $breadcrumbs ['close']=$f_close;
    }else{
        /**
         * Build the Breadcrumbs in the same order we received the parameters
         */
        $breadcrumbs = $f_open;
        for($i=0;$i<count($landscape);$i++){
            $breadcrumbs .=  ${'f_'.$landscape[$i] };
        }
        $breadcrumbs .= $f_tail.$f_close;
    }

    return $breadcrumbs;
}
