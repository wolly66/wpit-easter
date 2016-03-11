<?php
/**
 * @package WPIT Easter
 * @author Paolo Valenti
 * @version 1.0 first release
 */
/*
Plugin Name: WPIT Easter
Plugin URI: http://paolovalenti.org
Description: Find the easter date
Author: Paolo Valenti aka Wolly for WordPress Italy
Version: 1.0
Author URI: http://paolovalenti.info
Text Domain: wpit-easter
Domain Path: /languages
*/
/*
	Copyright 2013  Paolo Valenti aka Wolly  (email : wolly66@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define ( 'WPIT_WPITEASTER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define ( 'WPIT_WPITEASTER_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
define ( 'WPIT_WPITEASTER_PLUGIN_SLUG', basename( dirname( __FILE__ ) ) );
define ( 'WPIT_WPITEASTER_PLUGIN_VERSION', '1.0' );
define ( 'WPIT_WPITEASTER_PLUGIN_VERSION_NAME', 'wpit_wpiteaster_version' );



/**
 * Wpit_Easter class.
 */
class Wpit_Easter {

	//A static member variable representing the class instance
	private static $_instance = null;


	var $input_year = '';
	var $checked = '';
	var $eeaster_date_calc = '';
	var $julian_easter_date_calc = '';
	var	$gregorian_easter_date_calc = '';

	/**
	 * Wpit_Easter::__construct()
	 * Locked down the constructor, therefore the class cannot be externally instantiated
	 *
	 * @param array $args various params some overidden by default
	 *
	 * @return
	 */

	private function __construct() {

	add_shortcode( 'easter_date', array( $this, 'easter_date_calc' ) );


	}

	/**
	 * Wpit_Easter::__clone()
	 * Prevent any object or instance of that class to be cloned
	 *
	 * @return
	 */
	public function __clone() {
		trigger_error( "Cannot clone instance of Singleton pattern ...", E_USER_ERROR );
	}

	/**
	 * Wpit_Easter::__wakeup()
	 * Prevent any object or instance to be deserialized
	 *
	 * @return
	 */
	public function __wakeup() {
		trigger_error( 'Cannot deserialize instance of Singleton pattern ...', E_USER_ERROR );
	}

	/**
	 * Wpit_Easter::getInstance()
	 * Have a single globally accessible static method
	 *
	 * @param mixed $args
	 *
	 * @return
	 */
	public static function getInstance( $args = array() ) {
		if ( ! is_object( self::$_instance ) )
			self::$_instance = new self( $args );

		return self::$_instance;


	}


	/**
	 * easter_date_calc function.
	 *
	 * @access public
	 * @return void
	 */
	public function easter_date_calc(){

		$html = '';

		if (  isset( $_POST['_easter'] ) &&  wp_verify_nonce( $_POST['_easter'], '_easter' )  && ! empty( $_POST['wanted_year'] ) ){

			$this->input_year = $_POST['wanted_year'] ;

			//sanitize input. Check if is an INT and if it's 4 digit long and if it's between 1970 and 2037
			$this->check();

			if ( true == $this->checked ){

				$this->easter();

				$html .= '<p>' . __( '<strong>Catholic</strong> Easter date is: ', 'wpit-easter' ) . $this->easter_date_calc  .  '</p>';
				$html .= '<p>' . __( '<strong>Orthodox Julian calendar</strong> Easter date is: ', 'wpit-easter' ) . $this->julian_easter_date_calc  .  '</p>';
				$html .= '<p>' . __( '<strong>Orthodox Gregorian calendar</strong> Easter date is: ', 'wpit-easter' ) . $this->gregorian_easter_date_calc  .  '</p>';

					} else {

						$html .= '<p>' . __( 'Year must be a 4 digit number and between 1970 and 2037', 'wpit-easter' ) .  '</p>';
					}
		}




		$html .= '<h2>' . __( 'Find Easter date', 'wpit-easter' ) . '</h2>
		<form method="post" action="">' .
			wp_nonce_field( '_easter', '_easter' ) . '
			<label>' . __( 'Year four digit, i.e. 2016 and between 1970 and 2037', 'wpit-easter' ) . '</label>
			<p><input type="number" name="wanted_year" ></p>
			<input type="submit" name="submit" value="Calc"></td>
        </form>';

		return $html;


	}


	/**
	 * easter function.
	 *
	 * Find easter date
	 *
	 * @access private
	 * @return void
	 */
	private function easter(){

		//Catholich Easter

		$a = (int) ( $this->input_year % 19 );
		$b = (int) ( $this->input_year % 4 );
		$c = (int) ( $this->input_year % 7 );

		$d = (int) ( ( ( 19 * $a ) + 24 ) % 30 );
		$e = (int) ( ( ( 2 * $b ) + ( 4 * $c ) + ( 6 * $d ) + 5 ) % 7 );

		if ( ( $d + $e ) < 10 ){

			$easter_day = $d + $e + (int) 22;
			$easter_month = 3;

			} else {

				$easter_day =  $d + $e - (int) 9;
				$easter_month = (int) 4;

			}

		if ( 4 == $easter_month && 26 == $easter_day ){

			$easter_day = (int) 19;

		} elseif ( 4 == $easter_month && 25 == $easter_day && 28 == $d && 6 == $e && $a > 10 ){

			$easter_day = (int) 18;
		}


		$easter_datestamp = mktime( 0, 0, 0, $easter_month, $easter_day, $this->input_year );

		$this->easter_date_calc = date ("F-d-Y", $easter_datestamp );

		//Orthodox Easter



		$a = $this->input_year % 4;
		$b = $this->input_year % 7;
		$c = $this->input_year % 19;
		$d = ( 19 * $c + 15 ) % 30;
		$e = ( 2 * $a + 4 * $b - $d + 34 ) % 7;

		$month = floor( ( $d + $e + 114 ) / 31 );
		$day = ( ( $d + $e + 114 ) % 31 ) + 1;
		$day_gregorian = $day + 13;

		$easter_datestamp_julian = mktime( 0, 0, 0, $month, $day, $this->input_year );

		$easter_datestamp_gregorian = mktime( 0, 0, 0, $month, $day_gregorian, $this->input_year );

		$this->julian_easter_date_calc = date ("F-d-Y", $easter_datestamp_julian );
		$this->gregorian_easter_date_calc = date ("F-d-Y", $easter_datestamp_gregorian );


	}


	/**
	 * check function.
	 *
	 * Sanite Input, check if input is INT and if is 4 digit long
	 *
	 * @access private
	 * @return void
	 */
	private function check(){

		//check if input is an INT, return true or false
		if ( ctype_digit( $this->input_year ) ) {

       		$check_is_digit = true;

    			} else {

					$check_is_digit = false;

					$this->checked = false;

					return;

    	}

    	if ( 1970 > $this->input_year || 2037 < $this->input_year ){

	    	$this->checked = false;

	    	return;

    	}

		//check input lenght, return true or false
		$year_lenght = strlen( $this->input_year );

		if ( true == $check_is_digit && '4' == $year_lenght ) {

	    	$this->checked = true;

	      	} else {

	      		$this->checked = false;

	    }
	}

}// chiudo la classe

//istanzio la classe

$wpit_easter = Wpit_Easter::getInstance();