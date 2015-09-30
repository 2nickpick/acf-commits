<?php

/**
 * Class ACF_Commit_Partials
 *
 * Encapsulates blocks of HTML content used by the plugin
 */
class ACF_Commits_Partials {

	/**
	 * Generates an individual listing for a commit object
	 *
	 * @param $commit - The commit object to display
	 */
	public static function draw_commit_listing( $commit ) {
		?>
		<div>
			<div>
				<strong>
					<a href="<?php echo admin_url( '/edit.php?post_type=acf-commit' ) ?>">
						<?php echo get_the_date( 'M d, Y H:i a', $commit->ID ) ?>
					</a>
				</strong>
			</div>
			<div><em><?php echo get_field( 'commit_message', $commit->ID ) ?></em></div>
			<div>Author: <?php the_author() ?></div>
			<hr/>
		</div>
		<?php
	}

	/**
	 *  Generates the text area for entering a commit message
	 */
	public static function draw_commit_message() {
		?>
		<div id="acf-commits_commit_message_container"
		     class="misc-pub-section misc-pub-section-last">
			<?php wp_nonce_field( 'new_commit', 'article_or_box_nonce' ); ?>
			<div class="acf-label">
				<label for="acf-commits_commit_message">
					<span class="label"><strong>Commit Message</strong></span>
					<span class="acf-required">*</span>
				</label>
			</div>
			<p>Briefly explain why you are making this change: </p>
            <textarea name="commit_message"
                      id="acf-commits_commit_message"
                      onkeyup="ACF_Commits.validate_commit_message(this.value);"></textarea>

		</div>
		<?php
	}

	/**
	 *
	 * Generates the Export feature for the full commit listing
	 * @param $post_id - Commit to show export for
	 * @param $content - The JSON representing the export
	 */
	public static function draw_export_view( $post_id, $content ) {
		?>
		<label for="acf-commits_download_export_content_<?php echo $post_id ?>">
			<a href="#TB_inline?width=600&height=425&inlineId=download_export_<?php echo $post_id ?>"
			   class="thickbox"
			   title="JSON Export">View Export</a>
		</label>
		<div id="acf-commits_download_export_<?php echo $post_id ?>"
		     class="acf-commits_download_export">
			<textarea
				rows="3"
				id="acf-commits_download_export_content_<?php echo $post_id ?>"
			    class="acf-commits_download_export_content"><?php echo esc_html( $content ); ?></textarea>
		</div>
		<?php
	}

	/**
	 * Draw the Restore action for a Commit
	 *
	 * @param $post_id - the post ID of the commit to draw the restore link for
	 */
	public static function draw_restore_link( $post_id ) {
		?>
		<a href="javascript:" onclick="ACF_Commits.restore('<?php echo $post_id ?>'); ">Restore</a>
		<?php
	}

	/**
	 * Generate and return (rather than echo) the new delete button for Commits
	 *
	 * A new delete button is required because its cluttered to ask for a commit message in the listing
	 *
	 * @param $post_id - The post ID of the commit to generate the trash link for
	 *
	 * @return string
	 */
	public static function get_trash_link( $post_id ) {
		ob_start();
		?>
		<a class="submitdelete"
		   href="<?php echo admin_url( 'edit.php?post_type=acf-field-group&page=acf_commits_trash&group_id='
		                          . $post_id ) ?>">
			Trash
		</a>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generate the admin notice for deletion of an ACF Field Group
	 *
	 * @param $acf_field_group - The recently deleted ACF Field Group
	 */
	public static function draw_trash_acf_field_group_success_notice( $acf_field_group ) {
		if ( ! empty( $acf_field_group ) ) {
			?>
			<div class="updated"><em><?php echo $acf_field_group->post_title ?></em> Deleted Successfully</div>
			<?php
		}
	}

	/**
	 * Draw the ACF Field Group deletion form
	 *
	 * @param $acf_field_group - Field Group to draw trash form for
	 */
	public static function draw_trash_acf_field_group( $acf_field_group ) {
		if ( ! empty( $acf_field_group ) ) {
			?>
			<div class="wrap">
				<h2>Delete Field Group</h2>

				<h3>Delete "<?php echo $acf_field_group->post_title; ?>"</h3>

				<form method="post" action="<?php echo admin_url( 'admin-post.php' ) ?>">
					<input type="hidden" name="action" value="delete_field_group"/>
					<input type="hidden" name="group_id" value="<?php echo intval( $_GET['group_id'] ) ?>"

					<div class="acf-label">
						<label for="commit_message">Commit Message<span class="acf-required">*</span></label>
					</div>
					<p>Briefly explain why you are making this change: </p>

					<?php
					if ( isset( $_GET['error'] ) ) {
						?>
						<div class="error">You must provide a reason for deleting this field group.</div>
						<?php
					}
					?>
					<textarea name="commit_message"
					          id="acf-commits_commit_message"
					          onkeyup="ACF_Commits.validate_commit_message(this.value)"></textarea>
					<?php submit_button( 'Delete Field Group' ) ?>
				</form>
			</div>
			<?php
		} else {
			?>
			<div class="wrap">
				<h2>Delete Field Group</h2>

				<p>Field group was not found. May already be deleted. <a
						href="<?php echo admin_url( 'edit.php?post_type=acf-field-group' ) ?>">Go back.</a></p>
			</div>
			<?php
		}
	}
}
