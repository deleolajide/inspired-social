<?php
/*
  Plugin Name: BP Group Documents
  PLugin URI: http://lenasterg.wordpress.com/2012/04/17/buddypress-group-documents-for-bp-1-5-and-wp-3-3/
  Description: This BuddyPress component creates a document storage area within each group. Original plugin is <a href="http://wordpress.org/extend/plugins/buddypress-group-documents/">no longer supported</a> so revised by Lena Stergatu (<a href="mailto://stergatu@cti.gr">stergatu@cti.gr</a>) for WP 3.3, with additional bug fixes and improvements by <a href="http://keeblesmith.com">Keeble Smith</a>.
  Version: 0.4.3.3
  Revision Date: May 13, 2012
  Requires at least: WP 3.3, BuddyPress 1.5
  Tested up to: WP 3.3.2, BuddyPress 1.5.5
  License: Example: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
  Author: Peter Anselmo, Studio66 (original author). Revisions by Lena Stergatu (<a href="mailto://stergatu@cti.gr">stergatu@cti.gr</a>) with additional bug fixing and improvements by <a href="http://keeblesmith.com">Keeble Smith</a>.
  Author URI:
  Network Only: true
 */

//some constants that can be checked when extending this plugin
define('BP_GROUP_DOCUMENTS_IS_INSTALLED', 1);
define('BP_GROUP_DOCUMENTS_VERSION', '0.4.3.3');
define('BP_GROUP_DOCUMENTS_DB_VERSION', '3');

//allow override of URL slug
if (!defined('BP_GROUP_DOCUMENTS_SLUG'))
    define('BP_GROUP_DOCUMENTS_SLUG', 'documents');

//we must hook this on to an action, otherwise it will get called before bp-custom.php
function bp_group_documents_set_constants() {

    //This is where to look for admin bulk uploads
    if (!defined('BP_GROUP_DOCUMENTS_ADMIN_UPLOAD_PATH'))
        define('BP_GROUP_DOCUMENTS_ADMIN_UPLOAD_PATH', WP_PLUGIN_DIR . '/buddypress-group-documents/uploads/');

    //Widgets can be set to only show documents in certain (site-admin specified) groups
    if (!defined('BP_GROUP_DOCUMENTS_WIDGET_GROUP_FILTER'))
        define('BP_GROUP_DOCUMENTS_WIDGET_GROUP_FILTER', true);

    //if enabled, documents can be flagged as "featured"
    //widget will have an option to only show featured docs
    if (!defined('BP_GROUP_DOCUMENTS_FEATURED'))
        define('BP_GROUP_DOCUMENTS_FEATURED', true);

    //longer text descriptions to go with the documents can be toggled on or off.
    //this will toggle both the textarea input, and the display;
    if (!defined('BP_GROUP_DOCUMENTS_SHOW_DESCRIPTIONS'))
        define('BP_GROUP_DOCUMENTS_SHOW_DESCRIPTIONS', true);

	//if enabled, and wp_editor exists, it will be used for the document comment editor
	if (!defined('BP_GROUP_DOCUMENTS_ALLOW_WP_EDITOR'))
		define('BP_GROUP_DOCUMENTS_ALLOW_WP_EDITOR', false);
}
add_action('plugins_loaded', 'bp_group_documents_set_constants');
//load i18n files if present
if (file_exists(dirname(__FILE__) . '/languages/' . get_locale() . '.mo'))
    load_textdomain('bp-group-documents', dirname(__FILE__) . '/languages/' . get_locale() . '.mo');

//Go get me some files!
require ( dirname(__FILE__) . '/include/classes.php' );
require ( dirname(__FILE__) . '/include/cssjs.php' );
require ( dirname(__FILE__) . '/include/widgets.php' );
require ( dirname(__FILE__) . '/include/notifications.php' );
require ( dirname(__FILE__) . '/include/activity.php' );
require ( dirname(__FILE__) . '/include/templatetags.php' );
require ( dirname(__FILE__) . '/include/admin-uploads.php' );
require ( dirname(__FILE__) . '/include/filters.php' );
require ( dirname(__FILE__) . '/include/ajax.php' );

//only get the group admin page if they have options to edit
if (get_option('bp_group_documents_use_categories') ||
        ('mods_decide' == get_option('bp_group_documents_upload_permission') )) {
    require ( dirname(__FILE__) . '/include/group-admin.php' );
}

//only get the forum extension if it's been specified by the admin
if (get_option('bp_group_documents_forum_attachments')) {
    require( dirname(__FILE__) . '/include/group-forum-attachments.php' );
}

/* * ***********************************************************************
 * ********************SETUP AND INSTALLATION*******************************
 * *********************************************************************** */

/**
 * bp_group_documents_install()
 *
 * Installs and/or upgrades the database tables
 * This will only run if the database version constant is
 * greater than the stored database version value
 */
function bp_group_documents_install() {
    global $wpdb, $bp;

    if (!empty($wpdb->charset))
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

    $sql[] = "CREATE TABLE {$bp->group_documents->table_name} (
		  		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		  		user_id bigint(20) NOT NULL,
		  		group_id bigint(20) NOT NULL,
		  		created_ts int NOT NULL,
				modified_ts int NOT NULL,
				file VARCHAR(255) NOT NULL,
				name VARCHAR(255) NULL,
				description TEXT NULL,
				featured BOOL DEFAULT FALSE,
				download_count bigint(20) NOT NULL DEFAULT 0,
			    KEY user_id (user_id),
			    KEY group_id (group_id),
				KEY created_ts (created_ts),
				KEY modified_ts (modified_ts),
				KEY download_count (download_count)
		 	   ) {$charset_collate};";

    require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );
    dbDelta($sql);

    update_site_option('bp-group-documents-db-version', BP_GROUP_DOCUMENTS_DB_VERSION);
}

/**
 * bp_group_documents_setup_globals()
 *
 * Sets up global variables for group documents
 */
function bp_group_documents_setup_globals() {
    global $bp, $wpdb;

    /* For internal identification */
    $bp->group_documents->id = 'group_documents';
    $bp->group_documents->table_name = $wpdb->base_prefix . 'bp_group_documents';
    $bp->group_documents->format_notification_function = 'bp_group_documents_format_notifications';
    $bp->group_documents->slug = BP_GROUP_DOCUMENTS_SLUG;

    /* Register this in the active components array */
    $bp->active_components[$bp->group_documents->slug] = $bp->group_documents->id;

    switch (substr(BP_VERSION, 0, 3)) {
        case '1.2':
            if ('BuddyPress Classic' == get_current_theme()) {
                define('BP_GROUP_DOCUMENTS_THEME_VERSION', '1.1');
            } else {
                define('BP_GROUP_DOCUMENTS_THEME_VERSION', '1.2');
            }
            break;
        case '1.1':
            define('BP_GROUP_DOCUMENTS_THEME_VERSION', '1.1');
            break;
    }

    do_action('bp_group_documents_globals_loaded');
}

add_action('plugins_loaded', 'bp_group_documents_setup_globals', 5);
add_action('admin_menu', 'bp_group_documents_setup_globals', 2);

/**
 * bp_group_documents_check_installed()
 *
 * Adds the site administrator menu.
 * Checks to see if the DB tables exist or if we are running an old version
 * of the component. If the value has increased, it will run the installation function.
 * Also sets defaults for site admin options
 */
function bp_group_documents_check_installed() {
    global $wpdb, $bp;

    if (!current_user_can('manage_options'))
        return false;

    //Add the component's administration tab under the "BuddyPress" menu for site administrators

    include_once ( dirname(__FILE__) . '/include/admin.php' );


    /* Need to check db tables are current */
    if (get_site_option('bp-group-documents-db-version') < BP_GROUP_DOCUMENTS_DB_VERSION)
        bp_group_documents_install();

    //set admin option defaults if they're not already defined
    add_option('bp_group_documents_nav_page_name',  __( 'Documents', 'bp-group-documents' ));
    add_option('bp_group_documents_valid_file_formats', 'odt,rtf,txt,doc,docx,xls,xlsx,ppt,pps,pptx,pdf,jpg,jpeg,gif,png,zip,tar,gz');
    add_option('bp_group_documents_items_per_page', 20);
    add_option('bp_group_documents_upload_permission', 'members');
    add_option('bp_group_documents_display_icons', true);
    add_option('bp_group_documents_use_categories', false);
    add_option('bp_group_documents_enable_all_groups', true);
    add_option('bp_group_documents_progress_bar', true);
    add_option('bp_group_documents_forum_attachments', false);
}

add_action('admin_menu', 'bp_group_documents_check_installed', 50);

/** bp_group_documents_check_legacy_paths()
 *
 * checks if there are any documents in the old location (documents folder in plugin)
 * and if so, moves them to the new location (wp-content/blogs.dir)
 *
 * This will only fire when the admin page is viewed to save on overhead
 */
function bp_group_documents_check_legacy_paths() {

    if (defined('BP_GROUP_DOCUMENTS_PATH')) {
        $legacy_path = BP_GROUP_DOCUMENTS_PATH;
    } else {
        $legacy_path = WP_PLUGIN_DIR . '/buddypress-group-documents/documents/';
    }

    if ($dh = @opendir($legacy_path)) {
        $moved_count = 0;
        while (false !== ($file = readdir($dh) )) {
            if ($file != "." && $file != "..") {
                $document = new BP_Group_Documents();
                if ($document->populate_by_file($file)) {
                    rename($legacy_path . $file, $document->get_path(0, 1));
                    ++$moved_count;
                }
            }
        }
    }
}

add_action('bp_group_documents_admin', 'bp_group_documents_check_legacy_paths');


/* * ************************************************************************
 * ************************LOADING AND STAGING*******************************
 * ************************************************************************* */

/**
 * bp_group_documents_setup_nav()
 *
 * Sets up the navigation items for the component.  
 * Adds documents item under the group navigation
 */
function bp_group_documents_setup_nav() {

    global $bp, $current_blog, $group_object;

    if (!class_exists('BP_Groups_Group'))
        return false;

    /*
      if ( $group_id = BP_Groups_Group::group_exists($bp->current_action) ) {

      /* This is a single group page.
      $bp->is_single_item = true;
      $bp->groups->current_group = &new BP_Groups_Group( $group_id );

      }
     */

    //if documents have been explicitly deactivated for this group, do not show menu
    if (!get_option('bp_group_documents_enable_all_groups') && groups_get_groupmeta($bp->groups->current_group->id, 'group_documents_documents_disabled'))
        return false;

    $groups_link = $bp->root_domain . '/' . $bp->groups->slug . '/' . $bp->groups->current_group->slug . '/';

    /* Add the subnav item only to the single group nav item */
    if (bp_is_group()) {
        bp_core_new_subnav_item(array(
             'name' =>get_option('bp_group_documents_nav_page_name'). ' <span>('.BP_Group_Documents::get_total( $bp->groups->current_group->id ).')</span>',
//            'name' => __( 'Documents', 'bp-group-documents' ). ' <span>('.BP_Group_Documents::get_total( $bp->groups->current_group->id ).')</span>',
            'slug' => $bp->group_documents->slug,
            'parent_slug' => bp_get_current_group_slug(),
            'parent_url' => bp_get_group_permalink(groups_get_current_group()),
            'screen_function' => 'bp_group_documents_display',
            'position' => 35,
            'user_has_access' => $bp->groups->current_group->user_has_access,
            'item_css_id' => 'group-documents')
        );
        do_action('bp_group_documents_nav_setup');
    }
}

add_action('bp_actions', 'bp_group_documents_setup_nav', 100);
//add_action('wp', 'bp_group_documents_setup_nav', 2);
add_action('admin_menu', 'bp_group_documents_setup_nav', 2);

/**
 * bp_group_document_set_cookies()
 *
 * Set any cookies for our component.  This will usually be for list filtering and sorting.
 * We must create a dedicated function for this, to fire before the headers are sent
 * (doing this in the template object with the rest of the filtering/sorting is too late)
 */
function bp_group_documents_set_cookies() {
    if (isset($_GET['order'])) {
        setcookie('bp-group-documents-order', $_GET['order'], time() + 60 * 60 + 24); //expires in one day
    }
    if (isset($_GET['category'])) {
        setcookie('bp-group-documents-category', $_GET['category'], time() + 60 * 60 * 24);
    }
}

add_action('plugins_loaded', 'bp_group_documents_set_cookies');

/**
 * bp_group_documents_register_taxonomies()
 *
 * registers the taxonomies to use with the Wordpress Custom Taxonomy API
 */
function bp_group_documents_register_taxonomies() {
    register_taxonomy('group-documents-category', 'group-document', array('hierarchical' => true, 'label' => __('Group Document Categories', 'bp-group-documents'), 'query_var' => false));
}

add_action('init', 'bp_group_documents_register_taxonomies');

/**
 * bp_group_documents_display()
 *
 * Sets up the plugin template file and calls the dislay output function
 */
function bp_group_documents_display() {
    global $bp;

    do_action('bp_group_documents_display');

    add_action('bp_template_content_header', 'bp_group_documents_display_header');
    add_action('bp_template_title', 'bp_group_documents_display_title');
    add_action('bp_template_content', 'bp_group_documents_display_content');

    // Load the plugin template file.
    // BP 1.2 breaks it out into a group-specific template
    // BP 1.1 includes a generic "plugin-template file
    //this is a roundabout way of doing it, because I can't find a way to use bp_core_template
    //to either return a useful value or handle an array of templates
    $templates = array('groups/single/plugins.php', 'plugin-template.php');
    if (strstr(locate_template($templates), 'groups/single/plugins.php')) {
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'groups/single/plugins'));
    } else {
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'plugin-template'));
    }
}

function bp_group_documents_display_header() {
    _e('Group Documents', 'bp-group-documents');
}

function bp_group_documents_display_title() {
    _e('Document List', 'bp-group-documents');
}

/* * **************************************************************************
 * ***********************BEGIN MAIN DISPLAY***********************************
 * ************************************************************************** */

function bp_group_documents_display_content() {
    global $bp;

    //instanciating the template will do the heavy lifting with all the superglobal variables
    $template = new BP_Group_Documents_Template();
    ?>

    <div id="bp-group-documents">

        <?php do_action('template_notices') // (error/success feedback)    ?>

        <?php if ('1.1' != substr(BP_VERSION, 0, 3)) { ?>
            <h3><?php _e('Document List', 'bp-group-documents'); ?></h3>
        <?php } ?>

        <?php //-----------------------------------------------------------------------LIST VIEW--   ?>
        <div class="item-list-tabs no-ajax" id="subnav" role="navigation">
      
        <?php if (get_option('bp_group_documents_use_categories')) { ?>
            <div id="bp-group-documents-categories">
                <form id="bp-group-documents-category-form" method="get" action="<?php echo $template->action_link; ?>">
                    &nbsp; <?php echo __('Category:', 'bp-group-documents'); ?>
                    <select name="category">
                        <option value="" ><?php echo __('All', 'bp-group-documents'); ?></option>
                        <?php foreach ($template->get_group_categories() as $category) { ?>
                            <option value="<?php echo $category->term_id; ?>" <?php if ($template->category == $category->term_id)
                echo 'selected="selected"'; ?>><?php echo $category->name; ?></option>
                                <?php } ?>
                    </select>
                    <input type="submit" class="button" value="<?php echo __('Go', 'bp-group-documents'); ?>" />
                </form>
            </div>
        <?php } ?>

        <div id="bp-group-documents-sorting">
            <form id="bp-group-documents-sort-form" method="get" action="<?php echo $template->action_link; ?>">
                <?php _e('Order by:', 'bp-group-documents'); ?>
                <select name="order">
                    <option value="newest" <?php if ('newest' == $template->order)
            echo 'selected="selected"'; ?>><?php _e('Newest', 'bp-group-documents'); ?></option>
                    <option value="alpha" <?php if ('alpha' == $template->order)
                        echo 'selected="selected"'; ?>><?php _e('Alphabetical', 'bp-group-documents'); ?></option>
                    <option value="popular" <?php if ('popular' == $template->order)
                        echo 'selected="selected"'; ?>><?php _e('Most Popular', 'bp-group-documents'); ?></option>
                </select>
                <input type="submit" class="button" value="<?php _e('Go', 'bp-group-documents'); ?>" />
            </form>
        </div>

        <?php if ($template->document_list && count($template->document_list >= 1)) { ?>

            <div class="pagination no-ajax">
                <div id="group-documents-page-count" class="pag-count">
                    <?php $template->pagination_count(); ?>
                </div>
                <?php if ($template->show_pagination()) { ?>
                    <div id="group-documents-page-links" class="pagination-links">
                        <?php $template->pagination_links(); ?>
                    </div>
                <?php } ?>
            </div>
		<?php } ?>

		</div> <!-- // subnav -->

		<?php if ($template->document_list && count($template->document_list >= 1)) { ?>

            <?php if ('1.1' == substr(BP_VERSION, 0, 3)) { ?>
                <ul id="forum-topic-list" class="item-list">
                <?php } else { ?>
                    <ul id="bp-group-documents-list" class="item-list">
                    <?php } ?>

                    <?php
                    //loop through each document and display content along with admin options
                    $count = 0;
                    foreach ($template->document_list as $document_params) {
                        $document = new BP_Group_Documents($document_params['id'], $document_params);
                        ?>

                        <li <?php if (++$count % 2)
                echo 'class="alt"'; ?> >
                                <?php if (get_option('bp_group_documents_display_icons'))
                                $document->icon(); ?>

                            <a class="bp-group-documents-title" id="group-document-link-<?php echo $document->id; ?>" href="<?php $document->url(); ?>" target="_blank"><?php echo $document->name; ?>

                                <?php
                                if (get_option('bp_group_documents_display_file_size')) {
                                    echo ' <span class="group-documents-filesize">(' . get_file_size($document) . ')</span>';
                                }
                                ?></a> &nbsp;<span class="group-documents-meta">
                                <?php 
                                printf(__('Uploaded by %s on %s', 'bp-group-documents'), bp_core_get_userlink($document->user_id), date_i18n(get_option('date_format'), $document->created_ts)); ?></span>
                            <?php
                            if (BP_GROUP_DOCUMENTS_SHOW_DESCRIPTIONS && $document->description) {
								if (BP_GROUP_DOCUMENTS_ALLOW_WP_EDITOR)
                            		echo '<br /><span class="group-documents-description">' . stripslashes($document->description) . '</span>';
                            	else
                            		echo '<br /><span class="group-documents-description">' . nl2br($document->description) . '</span>';
                            }
                            ?><?php
                //eleni add this in order to display the Addthis button on 3/2/2011	
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                if (is_plugin_active('buddypress-addthis-ls/bp-addthis-ls.php')) {
                    echo get_bp_addthis_ls_button($document->get_url(), $document->name);
                }
//   end eleni add
                            ?><?php
                //show edit and delete options if user is privileged
                echo '<div class="admin-links">';
                if ($document->current_user_can('edit')) {
                    $edit_link = wp_nonce_url($template->action_link . 'edit/' . $document->id, 'group-documents-edit-link').'#edit-document-form';
                    echo "<a href='$edit_link'>" . __('Edit', 'bp-group-documents') . "</a> | ";
                }
                if ($document->current_user_can('delete')) {
                    $delete_link = wp_nonce_url($template->action_link . 'delete/' . $document->id, 'group-documents-delete-link');
                    echo "<a href='$delete_link' class='bp-group-documents-delete'>" . __('Delete', 'bp-group-documents') . "</a>";
                }

                echo '</div>';
                echo '</li>';
            }
                            ?>
                </ul>

            <?php } else { ?>
                <div id="message" class="info">
                    <p><?php if ($template->category > 0)
							_e('There are no documents in the selected category.', 'bp-group-documents');
						else
							_e('There are no documents uploaded for this group.', 'bp-group-documents');
					?></p>
                </div>

            <?php } ?>

            <?php //-------------------------------------------------------------------DETAIL VIEW--    ?>

            <?php if ($template->show_detail) { ?>

                <?php if ($template->operation == 'add') { ?>
                    <div id="bp-group-documents-upload-new">
                <?php } else { ?>
			<div id="bp-group-documents-edit"><a name="edit-document-form"></a>
                <?php } ?>

                        <h3><?php echo $template->header ?></h3>

                        <form method="post" id="bp-group-documents-form" class="standard-form" action="<?php echo $template->action_link; ?>" enctype="multipart/form-data">
                        <input type="hidden" name="bp_group_documents_operation" value="<?php echo $template->operation; ?>" />
                        <input type="hidden" name="bp_group_documents_id" value="<?php echo $template->id; ?>" />

                        <?php if ($template->operation == 'add') : ?>
							<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo return_bytes(ini_get('post_max_size')); ?>" />
							<label class="bp-group-documents-file-label"><?php _e('Choose File:', 'bp-group-documents'); ?></label>
                            <input type="file" name="bp_group_documents_file" class="bp-group-documents-file" />
							<p class="bp-group-documents-valid-file-formats">
								<?php
								$valid_file_formats1 = get_option('bp_group_documents_valid_file_formats');
								_e('Valid File Formats', 'bp-group-documents');
								echo ':<br />' . str_replace(',', ', ', $valid_file_formats1);
								?>
							</p>
                        <?php else: ?>
							<label>Document:</label><span><?php echo $template->name;?></span>
						<?php endif; ?>
						<?php if (BP_GROUP_DOCUMENTS_FEATURED) { ?>
							<label class="bp-group-documents-featured-label"><?php _e('Featured Document', 'bp-group-documents'); ?>: </label>
                            <input type="checkbox" name="bp_group_documents_featured" class="bp-group-documents-featured" value="1" <?php if ($template->featured) echo 'checked="checked"'; ?>/>
						<?php } ?>
                        <div id="document-detail-clear" class="clear"></div>
                        <div class="bp-group-documents-document-info">
                            <label><?php _e('Display Name:', 'bp-group-documents'); ?></label>
                            <input type="text" name="bp_group_documents_name" id="bp-group-documents-name" value="<?php echo $template->name ?>" />
                            <?php if (BP_GROUP_DOCUMENTS_SHOW_DESCRIPTIONS) { ?>
                                <label><?php _e('Description:', 'bp-group-documents'); ?></label>
								<?php if (BP_GROUP_DOCUMENTS_ALLOW_WP_EDITOR) :
									if ( function_exists( 'wp_editor' ) ) {
										wp_editor( $template->description, 'bp_group_documents_description', array(
											'media_buttons' => false,
											'dfw'		=> false) );
									}
									else 
										the_editor( $template->description, 'bp_group_documents_description', 'bp_group_documents_description', false ); 
								else: ?>
									<textarea name="bp_group_documents_description" id="bp-group-documents-description" rows="5" cols="100"><?php echo $template->description; ?></textarea>
								<?php endif;
							} ?>
                        </div>

                        <?php if (get_option('bp_group_documents_use_categories')) { ?>
                            <div class="bp-group-documents-category-wrapper">
                                <label><?php _e('Category:', 'bp-group-documents'); ?></label>
								<?php
								$group_categories = $template->get_group_categories(false);
								if (count($group_categories) > 0): ?>
									<div class="bp-group-documents-category-list">
										<ul>
											<?php foreach ($template->get_group_categories(false) as $category) { ?>
												<li><input type="checkbox" name="bp_group_documents_categories[]" value="<?php echo $category->term_id; ?>" <?php if ($template->doc_in_category($category->term_id))
									echo 'checked="checked"'; ?> /><?php echo $category->name; ?></li>
												<?php } ?>
										</ul>	
									</div>
								<?php endif; ?>
                                <input type="text" name="bp_group_documents_new_category" class="bp-group-documents-new-category" />
                            </div><!-- .bp-group-documents-category-wrapper -->
                        <?php } ?>
						<input type="submit" class="button" value="<?php _e('Save', 'bp-group-documents'); ?>" />
                        </form>
                    </div><!--end #post-new-topic-->

                    <?php if ($template->operation == 'add') { ?>
                        <a class="button" id="bp-group-documents-upload-button" href="" style="display:none;"><?php _e('Upload a New Document', 'bp-group-documents'); ?></a>
                    <?php } ?>

                <?php } ?>

            </div><!--end #group-documents-->
            <?php
        }

        /*         * ***********************************************************************
         * **********************EVERYTHING ELSE************************************
         * *********************************************************************** */

        /*
         * bp_group_documents_delete()
         *
         * after perfoming several validation checks, deletes both the uploaded
         * file and the reference in the database
         */

        function bp_group_documents_delete($id) {
            if (!ctype_digit($id)) {
                bp_core_add_message(__('The item to delete could not be found', 'bp-group-documents'), 'error');
                return false;
            }

            //check nonce
            if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'group-documents-delete-link')) {
                bp_core_add_message(__('There was a security problem', 'bp-group-documents'), 'error');
                return false;
            }

            $document = new BP_Group_Documents($id);
            if ($document->current_user_can('delete')) {
                if ($document->delete()) {
                    do_action('bp_group_documents_delete_success', $document);
                    return true;
                }
            }
            return false;
        }

        /*
         * bp_group_documents_check_ext()
         *
         * checks whether the passed filename ends in an extension
         * that is allowed by the site admin
         */

        function bp_group_documents_check_ext($filename) {

            if (!$filename) {
                return false;
            }

            $valid_formats_string = get_option('bp_group_documents_valid_file_formats');
            $valid_formats_array = explode(',', $valid_formats_string);

            $extension = substr($filename, (strrpos($filename, ".") + 1));
            $extension = strtolower($extension);

            if (in_array($extension, $valid_formats_array)) {
                return true;
            }
            return false;
        }

        /*
         * get_file_size()
         *
         * returns a human-readable file-size for the passed file
         * adapted from a function in the PHP manual comments
         */

        function get_file_size($document, $precision = 1) {

            $units = array('b', 'k', 'm', 'g');

            $bytes = file_exists($document->get_path(1)) ? filesize($document->get_path(1)) : 0;
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);

            $bytes /= pow(1024, $pow);

            return round($bytes, $precision) . $units[$pow];
        }

        /**
         * return_bytes()
         *
         * taken from the PHP manual examples.  Returns the number of bites
         * when given an abrevition (eg, max_upload_size)
         */
        function return_bytes($val) {
            $val = trim($val);
            $last = strtolower($val[strlen($val) - 1]);
            switch ($last) {
                // The 'G' modifier is available since PHP 5.1.0
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }

            return $val;
        }

        /**
         * bp_group_documents_remove_data()
         *
         * Cleans out both the files and the database records when a group is deleted
         */
        function bp_group_documents_remove_data($group_id) {

            $results = BP_Group_Documents::get_list_by_group($group_id);
            if (count($results) >= 1) {
                foreach ($results as $document_params) {
                    $document = new BP_Group_Documents($document_params['id'], $document_params);
                    $document->delete();
                    do_action('bp_group_documents_delete_with_group', $document);
                }
            }
        }

        add_action('groups_group_deleted', 'bp_group_documents_remove_data');

        /*
         * eleni add on 2/4/2012 in order to display the admin pages
         */

        function bp_group_documents_extension_admin() {
            add_submenu_page('bp-general-settings', __('Group Documents Admin', 'bp-group-documents'), __('Group Documents', 'bp-group-documents'), 'manage_options', 'bp-group-documents-settings', 'bp_group_documents_admin');
        }

        add_action('network_admin_menu', 'bp_group_documents_check_installed', 50);
        add_action('network_admin_menu', 'bp_group_documents_extension_admin', 60);
        add_action('admin_menu', 'bp_group_documents_extension_admin', 60);
        ?>
