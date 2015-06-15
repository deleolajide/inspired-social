<?php
/*
Title: Other
Setting: piklist_wp_helpers
Tab: Writing
Order: 220
Tab Order: 20
*/

global $wp_version;

  piklist('field', array(
    'type' => 'select'
    ,'field' => 'screen_layout_columns_post'
    ,'label' => 'Columns on "Add New" screen'
    ,'value' => 'default'
    ,'attributes' => array(
      'class' => 'small-text'
    )
    ,'choices' => array(
      'default' => 'Default'
      ,'1' => '1'
      ,'2' => '2'
    )
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'disable_autosave'
    ,'label' => 'Disable Autosave'
    ,'description' => '"Preview mode" depends on Autosave. Disabling Autosave will also disable Preview.'
    ,'choices' => array(
      'true' => 'Stop WordPress from autosaving posts.'
    )
  ));
  
  piklist('field', array(
    'type' => 'number'
    ,'field' => 'edit_posts_per_page'
    ,'label' => 'Posts per page on edit screen.'
    ,'value' => ''
    ,'attributes' => array(
      'class' => 'small-text'
    )
  ));

if ($wp_version >= 3.5)
{

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'xml_rpc'
    ,'label' => 'XML-RPC'
    ,'choices' => array(
      'true' => 'Disable XML RPC'
    )
  ));

}