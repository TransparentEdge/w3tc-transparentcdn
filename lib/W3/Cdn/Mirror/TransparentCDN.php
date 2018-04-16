<?php

if (!defined('ABSPATH')) {
    die();
}

if (!defined('W3TC_CDN_TRANSPARENTCDN_PURGE_URL')) define('W3TC_CDN_TRANSPARENTCDN_PURGE_URL', 'https://api.transparentcdn.com/v1/companies/%s/invalidate/');
if (!defined('W3TC_CDN_TRANSPARENTCDN_AUTHORIZATION_URL')) define('W3TC_CDN_TRANSPARENTCDN_AUTHORIZATION_URL', ' https://api.transparentcdn.com/v1/oauth2/access_token/');

w3_require_once(W3TC_LIB_W3_DIR . '/Cdn/Mirror.php');

/**
 * Class W3_Cdn_Mirror_TransparentCDN
 */
class W3_Cdn_Mirror_TransparentCDN extends W3_Cdn_Mirror {
    var $_token;
    /**
     * PHP5 Constructor
     *
     * @param array $config
     */
    function __construct($config = array()) {
        $config = array_merge(array(
            'companyid' => '',
            'apikey' => '',
            'apisecret' => ''
        ), $config);

        parent::__construct($config);
    }

    /**
     * Purges remote files
     *
     * @param array $files
     * @param array $results
     * @return boolean
     */
    function purge($files, &$results) {
        if (empty($this->_config['companyid'])) {
            $results = $this->_get_results($files, W3TC_CDN_RESULT_HALT, __('Company id not set.', 'w3-total-cache'));
            return false;
        }

        if (empty($this->_config['apikey'])) {
            $results = $this->_get_results($files, W3TC_CDN_RESULT_HALT, __('Empty API key.', 'w3-total-cache'));
            return false;
        }
        if (empty($this->_config['apisecret'])) {
            $results = $this->_get_results($files, W3TC_CDN_RESULT_HALT, __('Empty API secret.', 'w3-total-cache'));
            return false;
        }
        // We ask for the authorization token.
        $this->_get_token();

        $invalidation_urls = array();
        foreach($files as $file){ //Oh array_map+lambdas, how I miss u...
            if(isset($file['original_url'])) $invalidation_urls[] = $file['original_url'];
        }
        if(count($invalidation_urls)==0 ) $invalidation_urls[] = "";

        if ($this->_purge_content($invalidation_urls, $error)) {
                $results[] = $this->_get_result($local_path, $remote_path, W3TC_CDN_RESULT_OK, __('OK', 'w3-total-cache'));
        } else {
                $results[] = $this->_get_result($local_path, $remote_path, W3TC_CDN_RESULT_ERROR, sprintf(__('Unable to purge (%s).', 'w3-total-cache'), $error));
        }

        return !$this->_is_error($results);
    }

    /**
     * Purges CDN completely
     * @param $results
     * @return bool
     */
    function purge_all(&$results) {
        //TODO: Implementar mediante bans el * ? 
        //return $this->purge(array(array('local_path'=>'*', 'remote_path'=> '*')), $results);
        return false;
    }

    /**
    * Obtiene el token a usar como autorizacion en las peticiones de invalidacion a transparent.
    * //TODO: Mejor control de errores.
    * @return bool
    */
    function _get_token(){
        $client_id = $this->_config['apikey'];
        $client_secret = $this->_config['apisecret'];
        $args = array(
            'method' => 'POST',
            'user-agent' => W3TC_POWERED_BY,
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => "grant_type=client_credentials&client_id=$client_id&client_secret=$client_secret");

        $response = wp_remote_request(W3TC_CDN_TRANSPARENTCDN_AUTHORIZATION_URL, $args);

        if (is_wp_error($response)) {
            $error = implode('; ', $response->get_error_messages());
            return false;
        }
        $body = $response['body'];
        $jobj = json_decode($body);
        $this->_token = $jobj->access_token;
        return true;
    }

    /**
     * Purge content
     *
     * @param string $path
     * @param int $type
     * @param string $error
     * @return boolean
     */
    function _purge_content($files, &$error) {
        $json_payload = json_encode(array('urls' => $files));

        $url = sprintf(W3TC_CDN_TRANSPARENTCDN_PURGE_URL, $this->_config['companyid']);
        $args = array(
            'method' => 'POST',
            'user-agent' => W3TC_POWERED_BY,
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => sprintf('Bearer %s', $this->_token)
            ),
            'body' => json_encode(array(
                'urls' => $files
            ))
        );

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            $error = implode('; ', $response->get_error_messages());
            return false;
        }

        switch ($response['response']['code']) {
            case 200:
                $body = json_decode($response['body']);
                if(is_array($body->urls_to_send) && count($body->urls_to_send)>0 ){
                    return true; //hemos invalidado al menos una URL.
                }
                else if(count($files) > 0 && $files[0] != ""){ //HACK!!!
                    $error = __('Invalid Request URL', 'w3-total-cache');
                    return false;
                }
                return true;

            case 400:
                if(count($files) > 0 && $files[0] == "") return true; #Caso de la prueba.
                $error = __('Invalid Request Parameter', 'w3-total-cache');
                return false;

            case 403:
                $error = __('Authentication Failure or Insufficient Access Rights', 'w3-total-cache');
                return false;

            case 404:
                $error = __('Invalid Request URI', 'w3-total-cache');
                return false;

            case 500:
                $error = __('Server Error', 'w3-total-cache');
                return false;
        }

        $error = 'Unknown error';

        return false;
    }

    /**
     * If CDN supports path of type folder/*
     * @return bool
     */
    function supports_folder_asterisk() {
        return true;
    }

    /**
     * If the CDN supports fullpage mirroring
     * @return bool
     */
    function supports_full_page_mirroring() {
        return true;
    }
}
