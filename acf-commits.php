<?php
/**
 *
 * Plugin Name: Advanced Custom Fields: Commits
 * Plugin URI: https://github.com/2nickpick/acf-commits
 * Description: Keep track of changes from multiple users to Advanced Custom Fields Field Groups
 * Version: 0.1.0
 * Author: 2nickpick
 * Author URI: http://github.com/2nickpick
 * GitHub Plugin Name: Advanced Custom Fields: Commits
 * GitHub Plugin URI: https://github.com/2nickpick/acf-commits
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: acf-commits
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

include 'includes/acf-commits-partials.php';

/**
 * Class ACF_Commits
 *
 * Initializes the plugin
 */
class ACF_Commits {
	public function __construct() {
		load_plugin_textdomain( 'acf-commits', false, dirname( plugin_basename( __FILE__ ) . '/languages' ) );

		//register scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

		add_action( 'save_post', array( $this, 'save_acf_field_group' ) );
		add_action( 'post_submitbox_misc_actions', array( $this, 'acf_field_group_commit_message' ) );

		add_action( 'init', array( $this, 'register_acf_commit_post_type' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_acf_field_group_commits_meta_box' ) );

		add_action( 'manage_acf-commit_posts_columns', array( $this, 'add_acf_commit_admin_column_headers' ) );

		add_action(
			'manage_acf-commit_posts_custom_column',
			array( $this, 'add_acf_commit_admin_columns' ),
			10,
			2
		);

		add_filter( 'page_row_actions', array( $this, 'acf_commit_trash_action' ), 10, 2 );

		add_action( 'wp_ajax_acf_commit_import', array( $this, 'acf_commit_import' ) );

		add_action( 'admin_menu', array( $this, 'add_menu_items' ), 99 );

		add_action( 'admin_post_delete_field_group', array( $this, 'acf_commit_delete_field_group_post' ) );

		add_action( 'admin_notices', array( $this, 'acf_commit_delete_group_success' ) );
	}

	/**
	 *  Register / Enqueue Scripts for the dashboard
	 *
	 * @param $hook_suffix - the script name of the active page
	 */
	public function admin_scripts( $hook_suffix ) {
		global $post;
		wp_register_script(
			'acf-commits',
			plugins_url( '/js/ACF_Commits.js', __FILE__ ),
			array( 'jquery' )
		);
		wp_enqueue_script( 'acf-commits' );

		wp_register_script(
			'acf-commits-field-group-form',
			plugins_url( '/js/acf_field_group_form.js', __FILE__ ),
			array( 'jquery', 'acf-commits' )
		);

		if ( 'post.php' === $hook_suffix ) {
			if ( get_post_type( $post ) == 'acf-field-group' ) {
				wp_enqueue_script( 'acf-commits-field-group-form' );
			}
		}

		add_thickbox();
	}

	/**
	 *  Register / Enqueue Styles for the front end
	 */
	public function admin_styles() {
		wp_register_style(
			'acf-commits',
			plugins_url( '/css/style.css', __FILE__ ),
			null
		);
		wp_enqueue_style( 'acf-commits' );
	}

	/**
	 *  Add Commit Message Box to ACF Field Group Edit Screen
	 */
	public function acf_field_group_commit_message() {
		global $post;
		if ( get_post_type( $post ) === 'acf-field-group' ) {

			// Enqueue ACF Field Group Edit Form Javascript
			add_action( 'admin_enqueue_scripts', function () {
				wp_enqueue_script( 'acf-field-group-form' );
			} );

			ACF_Commits_Partials::draw_commit_message();
		}
	}

	/**
	 * When ACF Field Group is saved, trigger the commit
	 *
	 * @param $post_id - The post ID of the recently submitted ACF Field Group
	 */
	public function save_acf_field_group( $post_id ) {
		if ( ! isset( $_POST['post_type'] ) ) {
			return $post_id;
		}

		if ( get_post_type( $post_id ) !== 'acf-field-group' ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( $_POST['article_or_box_nonce'], 'new_commit' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( false !== wp_is_post_revision( $post_id ) ) {
			return $post_id;
		}

		if ( ! isset( $_POST['commit_message'] ) ) {
			return $post_id;
		}

		if ( ! isset( $_SESSION['commit-complete'] ) ) {
			$this->create_commit( $_POST['commit_message'], $post_id );
		}

		return $post_id;
	}

	/**
	 * Register the ACF Commit Post Type
	 */
	public function register_acf_commit_post_type() {
		$labels = array(
			'name'               => _x( 'ACF Commits', 'post type general name', 'your-plugin-textdomain' ),
			'singular_name'      => _x( 'ACF Commit', 'post type singular name', 'your-plugin-textdomain' ),
			'menu_name'          => _x( 'ACF Commits', 'admin menu', 'your-plugin-textdomain' ),
			'name_admin_bar'     => _x( 'ACF Commit', 'add new on admin bar', 'your-plugin-textdomain' ),
			'add_new'            => _x( 'Add New', 'book', 'your-plugin-textdomain' ),
			'add_new_item'       => __( 'Add New ACF Commit', 'your-plugin-textdomain' ),
			'new_item'           => __( 'New ACF Commit', 'your-plugin-textdomain' ),
			'edit_item'          => __( 'View ACF Commit', 'your-plugin-textdomain' ),
			'view_item'          => __( 'View ACF Commit', 'your-plugin-textdomain' ),
			'all_items'          => __( 'All ACF Commits', 'your-plugin-textdomain' ),
			'search_items'       => __( 'Search ACF Commits', 'your-plugin-textdomain' ),
			'parent_item_colon'  => __( 'Parent ACF Commits:', 'your-plugin-textdomain' ),
			'not_found'          => __( 'No ACF Commits found.', 'your-plugin-textdomain' ),
			'not_found_in_trash' => __( 'No ACF Commits found in Trash.', 'your-plugin-textdomain' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Version history for changes to ACF Fields.', 'elyk-textdomain' ),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'rewrite'            => array( 'slug' => 'acf-commit' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 6,
			'supports'           => array(
				'title',
				'editor',
				'author'
			)
		);

		register_post_type( 'acf-commit', $args );

		$this->register_acf_commit_custom_fields();
	}

	/**
	 * Show Commit Listing on ACF Field Group Edit
	 */
	public function add_acf_field_group_commits_meta_box() {
		add_meta_box(
			'acf_field_group_commits_meta_box', // $id
			'Commits', // $title
			array( $this, 'show_acf_field_group_commits_meta_box' ), // $callback
			'acf-field-group', // $custom_post_type
			'side', // $context
			'low' // $priority
		);
	}

	/**
	 *  Display relevant commits in Commit Listing Meta Box
	 */
	public function show_acf_field_group_commits_meta_box() {
		global $post;
		$commits = get_posts(
			array(
				'post_type'      => 'acf-commit',
				'posts_per_page' => 8,
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => 'acf_field_group',
						'value'   => $post->post_title,
						'compare' => '='
					),
					array(
						'key'     => 'acf_field_group',
						'value'   => '',
						'compare' => ''
					)
				)
			)
		);

		if ( ! empty( $commits ) ) {
			foreach ( $commits as $commit ) {
				setup_postdata( $commit );
				ACF_Commits_Partials::draw_commit_listing( $commit );
			}
		} else {
			echo '<p>There are no commits for this field group.</p>';
		}
	}

	/**
	 * Change the columns for ACF Commits
	 *
	 * @param $columns - the original columns for the post type
	 *
	 * @return array - new columns for post type
	 */
	public function add_acf_commit_admin_column_headers( $columns ) {
		unset( $columns['title'] );
		unset( $columns['date'] );
		unset( $columns['author'] );
		$columns['commit_message'] = 'Commit Message';
		$columns['group_field']    = 'ACF Group Field';
		$columns['view_export']    = 'View Export';
		$columns['restore']        = 'Restore';
		$columns['date']           = 'Date';
		$columns['author']         = 'Author';

		return $columns;
	}

	/**
	 * Generate content for new Commit post type columns
	 *
	 * @param $column - Column name to generate content for
	 * @param $post_id - Commit post ID
	 */
	public function add_acf_commit_admin_columns( $column, $post_id ) {
		switch ( $column ) {

			case 'group_field' :
				if ( get_field( 'acf_field_group', $post_id ) ) {
					echo get_field( 'acf_field_group', $post_id );
				} else {
					echo '<em>None</em>';
				}
				break;
			case 'commit_message' :
				echo get_field( 'commit_message', $post_id );
				break;
			case 'view_export' :
				$content_post = get_post( $post_id );
				$content      = $content_post->post_content;
				ACF_Commits_Partials::draw_export_view( $post_id, $content );
				break;
			case 'restore':
				ACF_Commits_Partials::draw_restore_link( $post_id );
				break;
		}
	}

	/**
	 * Modify the Trash action on ACF Field Groups to require a commit message
	 *
	 * @param $actions - ACF Field Group record action items
	 * @param $post - ACF Field Group post ID
	 */
	public function acf_commit_trash_action( $actions, $post ) {

		if ( get_post_type() === "acf-field-group" ) {

			//check capabilities
			$post_type_object = get_post_type_object( $post->post_type );
			if (
				! empty( $post_type_object )
				&& current_user_can( $post_type_object->cap->delete_post, $post->ID )
			) {
				$actions['trash'] = ACF_Commits_Partials::get_trash_link( $post->ID );
			}
		}

		return $actions;
	}

	/**
	 * Revert action for ACF Commits
	 *
	 * Delete all existing field groups and fields, and import a backup
	 */
	public function acf_commit_import() {
		$post = get_post( $_POST['post_id'] );

		// decode json
		$json = json_decode( $post->post_content, true );

		// validate json
		if ( empty( $json ) ) {
			return;
		}

		// get all previous acf posts
		$posts = get_posts(
			array(
				'post_type' => array( 'acf-field-group', 'acf-field', 'acf' )
			)
		);

		// delete al previous acf posts
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				wp_delete_post( $post );
			}
		}

		// if importing an auto-json, wrap field group in array
		if ( isset( $json['key'] ) ) {
			$json = array( $json );
		}

		// vars
		$ignored = array();
		$ref     = array();
		$order   = array();

		foreach ( $json as $field_group ) {
			// check if field group exists
			if ( acf_get_field_group( $field_group['key'] ) ) {

				// append to ignored
				$ignored[] = $field_group['title'];
				continue;
			}

			// remove fields
			$fields = acf_extract_var( $field_group, 'fields' );

			// format fields
			$fields = acf_prepare_fields_for_import( $fields );

			// save field group
			$field_group = acf_update_field_group( $field_group );

			// add to ref
			$ref[ $field_group['key'] ] = $field_group['ID'];

			// add to order
			$order[ $field_group['ID'] ] = 0;

			// add fields
			foreach ( $fields as $field ) {
				// add parent
				if ( empty( $field['parent'] ) ) {
					$field['parent'] = $field_group['ID'];
				} elseif ( isset( $ref[ $field['parent'] ] ) ) {
					$field['parent'] = $ref[ $field['parent'] ];
				}

				// add field menu_order
				if ( ! isset( $order[ $field['parent'] ] ) ) {
					$order[ $field['parent'] ] = 0;
				}

				$field['menu_order'] = $order[ $field['parent'] ];
				$order[ $field['parent'] ] ++;

				// save field
				$field = acf_update_field( $field );

				// add to ref
				$ref[ $field['key'] ] = $field['ID'];
			}
		}

		$this->create_commit( '[Reverted] ' . get_field( 'commit_message', $post->ID ) );
		die();
	}

	/**
	 * Create a new ACF Commit
	 *
	 * @param $commit_message - Detailed Message describing change to ACF Field Group
	 * @param int $post_id - Post ID of ACF Field Group
	 */
	public function create_commit( $commit_message, $post_id = 0 ) {
		$_SESSION['commit-complete'] = true;

		$field_groups = acf_get_field_groups();
		$exportJSON   = array();
		if ( ! empty( $field_groups ) ) {
			foreach ( $field_groups as $field_group ) {
				$field_group['fields'] = acf_get_fields( $field_group );
				$field_group['fields'] = acf_prepare_fields_for_export( $field_group['fields'] );
				acf_extract_var( $field_group, 'ID' );
				$exportJSON[] = $field_group;
			}
		}
		$export = acf_json_encode( $exportJSON );

		$commitID = wp_insert_post(
			array(
				'post_type'    => 'acf-commit',
				'post_status'  => 'publish',
				'post_content' => $export,
				'post_title'   => 'ACF Revision: ' . date( 'Y-m-d h:i:s' ),
			)
		);
		update_field( 'commit_message', $commit_message, $commitID );
		update_field( 'acf_field_group', get_the_title( $post_id ), $commitID );
	}

	/**
	 *  Add Menu Items for Plugins
	 */
	public function add_menu_items() {

		add_submenu_page( 'edit.php?post_type=acf-field-group', 'Modification Log', 'Modification Log',
			'manage_options', 'edit.php?post_type=acf-commit' );

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'acf_commits_trash' ) {
			add_submenu_page( 'edit.php?post_type=acf-field-group', 'Delete Field Group', 'Delete Field Group',
				'manage_options', 'acf_commits_trash', array( $this, 'acf_commits_trash_page' ) );
		}
	}

	/*
	 * The Page for accepting a commit message for a delete field group request
	 */
	public function acf_commits_trash_page() {
		$acf_field_group = get_post( $_REQUEST['group_id'] );
		ACF_Commits_Partials::draw_trash_acf_field_group( $acf_field_group );
	}

	/**
	 * Handle a delete field group request
	 */
	public function acf_commit_delete_field_group_post() {
		$acf_field_group = get_post( $_REQUEST['group_id'] );
		if ( ! empty( $_POST['commit_message'] ) ) {
			if ( ! isset( $_SESSION['commit-complete'] ) ) {
				$this->create_commit( '[Deleted] ' . $_POST['commit_message'], $acf_field_group->ID );
				wp_trash_post( $acf_field_group->ID );
				wp_redirect( admin_url( 'edit.php?post_type=acf-field-group&group_deleted=' . $acf_field_group->ID ) );
				exit();
			}
		}

		wp_redirect( admin_url( 'edit.php?post_type=acf-field-group&page=acf_commits_trash&error=1&group_id='
		                        . $acf_field_group->ID ) );
		exit();
	}

	/**
	 *  Add a success admin notice if a group was deleted successfully
	 */
	public function acf_commit_delete_group_success() {
		if ( ! empty( $_GET['group_deleted'] ) ) {
			$acf_field_group = get_post( $_GET['group_deleted'] );
			ACF_Commits_Partials::draw_trash_acf_field_group_success_notice( $acf_field_group );
		}
	}

	/*
	 * ACF Commit post type's custom fields exported from ACF
	*/
	public function register_acf_commit_custom_fields() {

		if ( function_exists( 'acf_add_local_field_group' ) ) {
			acf_add_local_field_group( array(
					'key'                   => 'group_55e6538ab5072',
					'title'                 => 'ACF Commit',
					'fields'                => array(
						array(
							'key'               => 'field_55e653974a880',
							'label'             => 'Commit Message',
							'name'              => 'commit_message',
							'type'              => 'text',
							'instructions'      => 'Briefly explain why you are making this change',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => '',
							'placeholder'       => '',
							'prepend'           => '',
							'append'            => '',
							'maxlength'         => '',
							'readonly'          => 0,
							'disabled'          => 0,
						),
						array(
							'key'               => 'field_55e657fc8961e',
							'label'             => 'ACF Field Group',
							'name'              => 'acf_field_group',
							'type'              => 'text',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => '',
							'placeholder'       => '',
							'prepend'           => '',
							'append'            => '',
							'maxlength'         => '',
							'readonly'          => 0,
							'disabled'          => 0,
						),
					),
					'location'              => array(
						array(
							array(
								'param'    => 'post_type',
								'operator' => '==',
								'value'    => 'acf-commit',
							),
						),
					),
					'menu_order'            => 0,
					'position'              => 'normal',
					'style'                 => 'default',
					'label_placement'       => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen'        => '',
					'active'                => 1,
					'description'           => '',
				)
			);
		}
	}
}

new ACF_Commits();
