<?php

namespace Update\Updater;

use Update\Response;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Class NoIPUpdater
 *
 * An updater for host managed by NoIP (http://noip.com).
 *
 * @package Update\Updater
 */
class NoIPUpdater implements Updater{

    private $user;
    private $password;

    public function setUser($name, $password){
        $this->user = $name;
        $this->password = $password;
    }

    public function updateHost($host, $ip){

        //check if user and password are set
        if (empty($this->user) || empty($this->password)){
            return new Response(false, 'Missing user credentials');
        }

        //check if host is set
        if (empty($host)){
            return new Response(false, 'Empty host');
        }

        //check if IP is valid
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE){
            return new Response(false, 'Invalid IP '.$ip);
        }

        // perform the request
        $client = new Client(['base_uri' => 'http://dynupdate.no-ip.com/nic/update']);

        try{

            $response = $client->request('GET', '/nic/update', [
                'query' => [
                    'hostname' => $host,
                    'myip' => $ip
                ],
                'headers' => [
                    'User-Agent' => 'MDDNSUpdater/v1.0 info@lucakeidel.de',
                    'Authorization' => 'Basic '.base64_encode($this->user.':'.$this->password)
                ]
            ]);

        }catch(ClientException $e){

            $response = $e->getResponse();
            $message = $response->getBody();

            $resp = null;

            if ($message === 'nohost'){
                $resp = new Response(true, 'Hostname does not exist under specified account.');
            } else if ($message === 'badauth'){
                $resp = new Response(true, 'Invalid user/password.');
            } else if ($message === 'badagent'){
                $resp = new Response(true, 'Disabled client.');
            } else if ($message === '!donator'){
                $resp = new Response(true, 'Feature not available for this user.');
            } else if ($message === 'abuse'){
                $resp = new Response(true, 'User blocked for abuse.');
            } else if ($message === '911'){
                $resp = new Response(true, 'Provider encountered a fatal error.');
            } else {
                $resp = new Response(true, $message);
            }

            return $resp;
        }

        // handle success responses
        if ($response->getStatusCode() === 200){

            if (preg_match('/^good/', $response->getBody()) || preg_match('/^nochg/', $response->getBody())){
                return $resp = new Response();
            }

        }

        return new Response(false, $response->getBody());
    }
}