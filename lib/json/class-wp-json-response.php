<?php

class WP_JSON_Response implements WP_JSON_ResponseInterface {
	/**
	 * Constructor
	 *
	 * @param mixed $data Response data
	 * @param integer $status HTTP status code
	 * @param array $common_info other common info  
	 */
	public function __construct($data = null, $status = 200, $common_info= array()) {
		$this->data = $data;
		$this->set_status( $status );
		$this->set_common_infos ( $common_info);
	}

	/**
	 * Get common_info with the response
	 *
	 * @return array Map of header name to header value
	 */
	public function get_common_info() {
		return $this->common_info;
	}

	/**
	 * Set all common infos  
	 *
	 * @param array $headers Map of header name to header value
	 */
	public function set_common_infos ( $common_info) {
		$this->common_info = $common_info;
	}

	/**
	 * Set a single common info 
	 *
	 * @param string $key info name
	 * @param string $value info value
	 * @param boolean $replace Replace an existing header of the same name?
	 */
	public function set_info( $key, $value, $replace = true ) {
        if ( $replace ){
			$this->common_info [ $key ] = $value;
        }elseif(! isset( $this->common_info[ $key ] ) ) {
			$this->common_info [ $key ] =  $value;
		} else {
			$this->common_info [ $key ] .= ', ' . $value;
		}
	}

	/**
	 * Send navigation-related headers for post collections
	 *
	 * @param WP_Query $query
	 */
	public function query_navigation_headers( $query ) {
		$max_page = $query->max_num_pages;
		$paged    = $query->get('paged');

		if ( ! $paged ) {
			$paged = 1;
		}

		$nextpage = intval($paged) + 1;

		if ( ! $query->is_single() ) {
			if ( $paged > 1 ) {
				$request = remove_query_arg( 'page' );
				$request = add_query_arg( 'page', $paged - 1, $request );
				$this->set_info( 'prev', $paged -1 );
			}

			if ( $nextpage <= $max_page ) {
				$request = remove_query_arg( 'page' );
				$request = add_query_arg( 'page', $nextpage, $request );
				$this->set_info( 'next', $nextpage );
			}
		}

        $this->set_info('total',$query->found_posts);
        $this->set_info('total_page',$max_page );

		do_action('json_query_navigation_headers', $this, $query);
	}

	/**
	 * Get the HTTP return code for the response
	 *
	 * @return integer 3-digit HTTP status code
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set the HTTP status code
	 *
	 * @param int $code HTTP status
	 */
	public function set_status( $code ) {
		$this->status = absint( $code );
	}

	/**
	 * Get the response data
	 *
	 * @return mixed
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Set the response data
	 *
	 * @param mixed $data
	 */
	public function set_data( $data ) {
		$this->transImage($data);
		$this->data = $data;
	}
	
	/**
	 * 压缩图片
	 *
	 * @param mixed $data
	 */
	public function transImage(&$data){
		if(is_array($data) && isset($data[0])){
			foreach($data as $k => &$v){
				if(isset($v['featured_image']) && !empty($v['featured_image'])){
					foreach($v['featured_image']->data as &$img){
						$img['source'] = wpapper_gen_new_img($img['source']);
					}
				}
			}
		}else{
			if(isset($data['content']) && !empty($data['content'])){
				$content = $data['content'];
				$content = preg_replace("/<img(.*)?src=/isU",'<img src=',$content);
				$content = preg_replace('/(<img src=\")(.+?)(\".*?\>)/ise', "WP_JSON_Response::_parseimg('\\1', '\\2', '\\3')", $content);
				$data['content'] = $content;
			}
		}
	}

	public static function _parseimg($before, $img, $after) {
		$before = stripslashes($before);
		$after = stripslashes($after);
		$ret = '';
		if (in_array(strtolower(substr($img, 0, 6)), array('http:/', 'https:', 'ftp://'))) {
			$img =  wpapper_gen_new_img($img);
			if(!empty($img)){
				$ret = $before . $img . $after;
			}
		}
		return $ret;
	}

	/**
	 * Get the response data for JSON serialization
	 *
	 * It is expected that in most implementations, this will return the same as
	 * {@see get_data()}, however this may be different if you want to do custom
	 * JSON data handling.
	 *
	 * @return mixed Any JSON-serializable value
	 */
	public function jsonSerialize() {
		return $this->get_data();
	}
}
