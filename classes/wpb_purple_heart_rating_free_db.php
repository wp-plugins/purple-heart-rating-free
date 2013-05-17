<?php
/**
 * @package    WPBuddy Plugin
 * @subpackage Purple Heart Rating (Free)
 */
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WPB_Purple_Heart_Rating_Free_Db {

	/**
	 * The name of the database ip table
	 */
	const IP_TABLE = 'wpbph_ip_table';


	/**
	 * Creates the database tables
	 * @global wpdb $wpdb
	 * @return bool|int
	 * @since 1.0
	 */
	public static function create_db_tables() {
		global $wpdb;
		if( ! $wpdb instanceof wpdb ) return false;
		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . self::IP_TABLE . "` ( "
			. "`wpbph_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, "
			. "`wpbph_ip` tinytext NOT NULL, "
			. "`wpbph_post_id` bigint(20) unsigned NOT NULL, "
			. "`wpbph_comment_id` bigint(20) unsigned NOT NULL, "
			. "`wpbph_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, "
			. "PRIMARY KEY (`wpbph_id`) "
			. ") ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; " . chr( 10 );

		return $wpdb->query( $sql );
	}


	/**
	 * Removes the database tables
	 * @global wpdb $wpdb
	 * @return bool
	 * @since 1.0
	 */
	public static function remove_db_tables() {
		global $wpdb;
		if( ! $wpdb instanceof wpdb ) return false;

		$sql = "DROP TABLE `" . $wpdb->prefix . self::IP_TABLE . "`;";
		return $wpdb->query( $sql );

	}


	/**
	 * Writes a value into the database
	 *
	 * @param int    $post_id
	 *
	 * @param string $post_type
	 *
	 * @global wpdb  $wpdb
	 * @uses  WPB_Purple_Heart_Rating_Free::get_user_ip_addr
	 * @uses  WPB_Purple_Heart_Rating_Free_Db::clean_up
	 *
	 * @return bool|false|int
	 * @since 1.0
	 */
	public static function log_rating( $post_id, $post_type = 'post' ) {
		global $wpdb;

		if( ! $wpdb instanceof wpdb ) return false;

		// cleaning up the database
		self::clean_up();

		if( 'comment' == $post_type ) {
			return $wpdb->insert( $wpdb->prefix . self::IP_TABLE, array(
				'wpbph_ip'         => WPB_Purple_Heart_Rating_Free::get_user_ip_addr(),
				'wpbph_comment_id' => $post_id
			) );
		}

		return $wpdb->insert( $wpdb->prefix . self::IP_TABLE, array(
			'wpbph_ip'      => WPB_Purple_Heart_Rating_Free::get_user_ip_addr(),
			'wpbph_post_id' => $post_id
		) );
	}


	/**
	 * Checks if the ip address has rated the post already
	 *
	 * @since 1.0
	 * @uses  WPB_Purple_Heart_Rating_Free::get_user_ip_addr
	 *
	 * @param                  $post_id
	 * @param null|string      $ip_addr
	 * @param null|int         $time_back
	 *
	 * @param string           $post_type
	 *
	 * @return bool
	 */
	public static function has_rated( $post_id, $ip_addr = null, $time_back = null, $post_type = 'post' ) {
		global $wpdb;
		if( ! $wpdb instanceof wpdb ) return false;

		if( is_null( $time_back ) ) $time_back = self::get_time_back();
		if( is_null( $ip_addr ) ) $ip_addr = WPB_Purple_Heart_Rating_Free::get_user_ip_addr();

		$time_back = time() - $time_back;

		if( 'comment' == $post_type ) {
			return (bool) $wpdb->get_var( "SELECT COUNT(wpbph_id) FROM `" . $wpdb->prefix . self::IP_TABLE . "` "
				. "WHERE `wpbph_ip` = '" . $ip_addr . "' "
				. "AND `wpbph_comment_id` = " . intval( $post_id ) . " "
				. "AND ( `wpbph_time` BETWEEN FROM_UNIXTIME(" . $time_back . ") AND NOW() )" );
		}

		return (bool) $wpdb->get_var( "SELECT COUNT(wpbph_id) FROM `" . $wpdb->prefix . self::IP_TABLE . "` "
			. "WHERE `wpbph_ip` = '" . $ip_addr . "' "
			. "AND `wpbph_post_id` = " . intval( $post_id ) . " "
			. "AND ( `wpbph_time` BETWEEN FROM_UNIXTIME(" . $time_back . ") AND NOW() )" );
	}


	/**
	 * Cleaning up the database with old entries
	 * @since 1.0
	 * @return bool
	 */
	private static function clean_up() {
		global $wpdb;
		if( ! $wpdb instanceof wpdb ) return false;

		$time_back = self::get_time_back();
		$time_back = time() - $time_back;

		return (bool) $wpdb->query( 'DELETE FROM `' . $wpdb->prefix . self::IP_TABLE . '` WHERE `wpbph_time` < FROM_UNIXTIME(' . $time_back . ')' );
	}


	/**
	 * Returns the time (in seconds) to get back in time
	 *
	 * @return int|string
	 * @since 1.0
	 *
	 */
	public static function get_time_back() {
		$time_back = WPB_Purple_Heart_Rating_Free::get_option_static( 'ip_save_time' );
		if( is_int( $time_back ) && 0 == $time_back ) return 0;
		if( '' == $time_back ) return 31536000; // 1 year in seconds
		return $time_back;
	}


	/**
	 * Checks if a table exsits in the db
	 *
	 * @param string $table_name
	 *
	 * @return bool
	 */
	public static function table_exists( $table_name ) {
		global $wpdb;
		if( ! $wpdb instanceof wpdb ) return;

		$table_name_in_db = $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpdb->prefix . $table_name . '"' );

		if( $table_name_in_db == $wpdb->prefix . $table_name ) return true;

		return false;
	}

	/**
	 *
	 * Imports the GD Star Ratings into Purple Heart
	 *
	 * @param string $table_name
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public static function import_from_gd_rating( $table_name = 'gdsr_data_article' ) {
		global $wpdb;
		if( ! $wpdb instanceof wpdb ) return false;


		/**
		 * Import the article ratings from GD tables
		 */
		$results = $wpdb->get_results( 'SELECT post_id, user_votes, user_voters, visitor_votes, visitor_voters, user_recc_plus, user_recc_minus, visitor_recc_plus, visitor_recc_minus FROM `' . $wpdb->prefix . $table_name . '`', OBJECT );

		foreach( $results as $row ) {

			// this is where the magic is happening :-)

			$total_votes       = $row->user_votes + $row->visitor_votes;
			$total_users_voted = $row->user_voters + $row->visitor_voters;

			$rating = WPB_Purple_Heart_Rating_Free::get_rating( $row->post_id );

			// avoid division by zero
			if( $total_users_voted > 0 ) {
				// get the oks
				$oks = pow( $total_votes, 2 ) / ( 10 * $total_users_voted );

				// this are the bads
				$bads = $total_votes - $oks;
			}
			else {
				$oks  = 0;
				$bads = 0;
			}

			$rating['ok']  = $oks;
			$rating['bad'] = $bads;

			// now add the numbers from the plus/minus system
			$rating['ok']  = $rating['ok'] + $row->user_recc_plus + $row->visitor_recc_plus;
			$rating['bad'] = $rating['bad'] + $row->user_recc_minus + $row->visitor_recc_minus;

			// Round to beautiful numbers
			$rating['ok']  = round( $rating['ok'], PHP_ROUND_HALF_UP );
			$rating['bad'] = round( $rating['bad'], PHP_ROUND_HALF_UP );

			WPB_Purple_Heart_Rating_Free::set_ratings( $row->post_id, $rating );
		}

		return true;
	}


	/**
	 * Import the comment ratings from the GD Star Rating table
	 *
	 * @param string $table_name
	 *
	 * @since 1.0
	 * @return bool
	 */
	public static function import_comments_from_gd_rating( $table_name = 'gdsr_data_comment' ) {
		global $wpdb;
		if( ! $wpdb instanceof wpdb ) return false;

		$results = $wpdb->get_results( 'SELECT comment_id, (user_votes + visitor_votes) as total_votes, (user_voters + visitor_voters) as total_users_voted, (user_recc_plus + visitor_recc_plus) as total_oks, (user_recc_minus + visitor_recc_minus) as total_bads FROM `' . $wpdb->prefix . $table_name . '`', OBJECT );

		foreach( $results as $row ) {

			// avoid division by zero
			if( $row->total_users_voted > 0 ) {

				// get the oks
				$oks = pow( $row->total_votes, 2 ) / ( 10 * $row->total_users_voted );

				// this are the bads
				$bads = $row->total_votes - $oks;
			}
			else {
				$oks  = 0;
				$bads = 0;
			}

			$rating['ok']  = $oks;
			$rating['bad'] = $bads;

			// now add the numbers from the plus/minus system
			$rating['ok']  = $rating['ok'] + $row->total_oks;
			$rating['bad'] = $rating['bad'] + $row->total_bads;

			// Round to beautiful numbers
			$rating['ok']  = round( $rating['ok'], PHP_ROUND_HALF_UP );
			$rating['bad'] = round( $rating['bad'], PHP_ROUND_HALF_UP );

			$new_rating = $rating['ok'] - $rating['bad'];

			// update total number of raters
			update_comment_meta( $row->comment_id, 'wpbph_raters_total', $row->total_users_voted );

			// update rating
			update_comment_meta( $row->comment_id, 'wpbph_rating_count', $new_rating );
		}

		return true;
	}


	/**
	 * Returns the number of total comment ratings
	 * @return int|null|string
	 * @since 1.0
	 */
	public static function get_total_comment_ratings() {
		global $wpdb;
		if( ! $wpdb instanceof wpdb ) return 0;

		return $wpdb->get_var( 'SELECT SUM( `meta_value` ) FROM `' . $wpdb->commentmeta . '` WHERE `meta_key` = "wpbph_rating_count"' );
	}


	/**
	 * Returns the total number of comment voters
	 * @since 1.0
	 * @return int|null|string
	 */
	public static function get_total_comment_voters() {
		global $wpdb;
		if( ! $wpdb instanceof wpdb ) return 0;

		return $wpdb->get_var( 'SELECT SUM( `meta_value` ) FROM `' . $wpdb->commentmeta . '` WHERE `meta_key` = "wpbph_raters_total"' );
	}


	/**
	 * Returns the total number of positive and negative votes
	 * @since 1.0
	 * @return array
	 * @todo return total number of ratings
	 */
	public static function get_total_ratings() {
		global $wpdb;
		if( ! $wpdb instanceof wpdb ) return 0;

		$posts = get_posts( array(
			'meta_query' => array(
				'key'     => 'wpbph_ratings',
				'compare' => '='
			),
		) );
	}


}