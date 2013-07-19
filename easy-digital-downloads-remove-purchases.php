<?php
/*
Plugin Name: Easy Digital Downloads - Remove Purchases
Plugin URI: http://www.nasteriadis.gr/easy-digital-downloads-remove-purchases
Description: This plugin is an extension of the easy digital downloads and enables the user to delete transactions stores in the db both completely or partially.
Version: 1.0
Author: Nick C. Asteriadis
Author URI: http://www.nasteriadis.gr/
Depends: Easy Digital Downloads
License:

  Copyright 2013 (nickast@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

require_once('delete-functions.php');

add_filter('edd_reports_contextual_help', 'add_delete_help_screen',10,1);
function add_delete_help_screen($screen){
	$screen->add_help_tab( 
		array(
			'id'	    => 'edd-reports-delete',
			'title'	    => __( 'Delete', 'eddrp' ),
			'content'	=>
				'<p>' . __( 'This screen allows you to delete the past transaction.', 'edd' ) . '</p>' . 
				'<p>' . __( '<strong>All</strong> - You can delete all the past transaction from the db', 'edd' ) . '</p>' .
				'<p>' . __( '<strong>Partial Delete</strong> - You can select which transaction you want to delete', 'edd' ) . '</p>'
		) 
	);
}

add_action('edd_reports_tabs', 'add_delete_screen_option');
function add_delete_screen_option(){
	global $edd_options;

	$current_page = admin_url( 'edit.php?post_type=download&page=edd-reports' );
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'reports';
	
	?>
	<a href="<?php echo add_query_arg( array( 'tab' => 'delete', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'delete' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Delete', 'edd' ); ?></a>
	<?php
}

function edd_reports_tab_delete() {
	?>
	<div class="metabox-holder">
		<div id="post-body">
			<div id="post-body-content">

				<?php do_action( 'edd_reports_tab_delete_content_top' ); ?>

				<div class="postbox">
					<h3><span><?php _e( 'Delete all past transaction', 'edd' ); ?></span></h3>
					<div class="inside">
						<p><?php _e( 'By clicking here you can delete all the past transction.', 'edd' ); ?></p>
						<p><a class="button" href="<?php echo wp_nonce_url( add_query_arg( array( 'edd-action' => 'delete_transactions' ) ), 'edd_delete_transactions' ); ?>"><?php _e( 'Delete Transactions', 'edd' ); ?></a></p>
					</div><!-- .inside -->
				</div><!-- .postbox -->

				
				<div class="postbox">
					<h3><span><?php _e('Export Download History in CSV', 'edd'); ?></span></h3>
					<div class="inside">
						<p><?php _e( 'Download a CSV of all file downloads for a specific month and year.', 'edd' ); ?></p>
						<p>
							<form method="post">
								<?php echo EDD()->html->year_dropdown(); ?>
								<?php echo EDD()->html->month_dropdown(); ?>
								<input type="hidden" name="edd-action" value="downloads_history_export"/>
								<input type="submit" value="<?php _e( 'Generate CSV', 'edd' ); ?>" class="button-secondary"/>
							</form>
						</p>
					</div><!-- .inside -->
				</div><!-- .postbox -->
				

				<?php do_action( 'edd_reports_tab_delete_content_bottom' ); ?>

			</div><!-- .post-body-content -->
		</div><!-- .post-body -->
	</div><!-- .metabox-holder -->
	<?php
}
add_action( 'edd_reports_tab_delete', 'edd_reports_tab_delete' );

function edd_delete_transactions( $data ) {
	$edd_delete_transaction = $_GET['_wpnonce'];

	if ( wp_verify_nonce( $edd_delete_transaction, 'edd_delete_transactions' ) ) {
		global $wpdb;

		//get all downloads meta
		$all_downloads = new WP_Query(array('post_type' => 'download', 'posts_per_page' => -1));
		if(!$all_downloads)
			return;

		$download_ids = array();
		foreach ($all_downloads->posts as $download) {
			array_push($download_ids, $download->ID);
		}

		delete_transaction_postmeta_records($download_ids);

		//get all logs meta
		$all_logs = new WP_Query(array('post_type' => 'edd_log', 'posts_per_page' => -1));
		if(!$all_logs)
			return;

		$log_ids = array();
		foreach ($all_logs->posts as $log) {
			array_push($log_ids, $log->ID);
		}

		delete_logs_postmeta_records($log_ids);

		delete_transactions_and_logs();

	} else {
		echo 'unable to verify nonce';
	}
}
add_action( 'edd_delete_transactions', 'edd_delete_transactions' );



