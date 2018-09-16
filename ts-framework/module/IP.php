<?
namespace tsframe\module;

class IP{
	/**
	 * dtr_pton
	 *
	 * Converts a printable IP into an unpacked binary string
	 *
	 * @author Mike Mackintosh - mike@bakeryphp.com
	 * @param string $ip
	 * @return string $bin
	 */
	public static function pton($ip){

	    if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
	        return current( unpack( "A4", inet_pton( $ip ) ) );
	    }
	    elseif(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
	        return current( unpack( "A16", inet_pton( $ip ) ) );
	    }

	    return false;
	}

	/**
	 * dtr_ntop
	 *
	 * Converts an unpacked binary string into a printable IP
	 *
	 * @author Mike Mackintosh - mike@bakeryphp.com
	 * @param string $str
	 * @return string $ip
	 */
	public static function ntop( $str ){
	    if( strlen( $str ) == 16 OR strlen( $str ) == 4 ){
	        return inet_ntop( pack( "A".strlen( $str ) , $str ) );
	    }

	    //throw new \Exception( "Please provide a 4 or 16 byte string" );
	    return NULL;
	}

	public static function current(){
		return $_SERVER['REMOTE_ADDR'];
	}
}