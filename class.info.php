<? // UTF-8 編碼
//
// Info
//
// Copyright (c) 2012 Chen Guan-Ting
//
// Permission is hereby granted, free of charge, to any person obtaining a copy of
// this software and associated documentation files (the "Software"), to deal in
// the Software without restriction, including without limitation the rights to
// use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
// of the Software, and to permit persons to whom the Software is furnished to do
// so, subject to the following conditions:
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.
//

//
// How To Use
//
// $info = new Info();
// var_dump($info->agent());
// var_dump($info->ip());

//
// Object
//
class Info {
	public function __construct () {
		// nothing..
	}

	public function __destruct () {
		// nothing..
	}

	public function ip () {
		// 如果 PAAS 商 有提供的 HTTP_X_REAL_IP，務必以廠商提供的為主，修正 REMOTE_ADDR 所取得的 IP
		$ip = true == isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
		return ( true == $this->is_ipv6($ip) ) ? $this->ipv6_format($ip) : $ip;
	}

	public function ipv6_format ( $old_ip ) {
		$new_ip = '';
		$is_zero_compress = false;
		$has_extracted = false;
		$has_ipv4_mapped = false;
		$colon_symbol_count = 7;

		if ( false !== strpos($old_ip, '::') ) {
			$is_zero_compress = true;
			$has_ipv4_mapped = 3 == substr_count($old_ip, '.') ? true : false;
			$colon_symbol_count = substr_count($old_ip, ':') - 2;
		}

		$old_ip = explode(':', strtoupper($old_ip));

		for ( $index=0; $index<8; $index++ ) {
			if ( NULL != $old_ip[$index] ) {
				$new_ip .= ( 0 == $index ? '' : ':' ).str_pad($old_ip[$index], 4, '0', STR_PAD_LEFT);
			} else {
				if ( true == $is_zero_compress && false == $has_extracted ) {
					$max_pad_count = ( 0 == $index && 0 == $colon_symbol_count && false == $has_ipv4_mapped ) ? 7-$colon_symbol_count : 7-$colon_symbol_count-1;

					for ( $pad_count=0; $pad_count<$max_pad_count; $pad_count++ ) {
						$new_ip .= ( 0 == $index && 0 == $pad_count ? '' : ':' ).'0000';
					}

					$has_extracted = true;
				}
			}
		}

		return $new_ip;
	}
	
	public function ip_prefix () {
		$ip = $this->ip();
		$ip_prefix = NULL;

		if ( false == $this->is_ipv6($ip) ) {
			$ip_prefix = substr($ip, 0, strpos($ip, '.'));
		} else {
			$ip_prefix = substr($ip, 0, strpos($ip, ':', strpos($ip, ':') + 1));
		}

		return $ip_prefix;
	}

	public function is_ipv6 ( $ip ) {
		return false !== strpos($ip, ':');
	}

	public function forward () {
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}

	public function agent () {
		return $_SERVER['HTTP_USER_AGENT'];
	}

	public function referer () {
		return $_SERVER['HTTP_REFERER'];
	}
};
?>