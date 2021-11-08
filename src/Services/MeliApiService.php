<?php

namespace WebDEV\Meli\Services;

use Carbon\Carbon;
use Exception;
use stdClass;
use WebDEV\Meli\Databases\Models\MeliAppToken;
use WebDEV\Meli\Databases\Repositories\MeliAppTokenRepository;
use WebDEV\Meli\Enumerators\AuthEnum;

class MeliApiService
{
    protected static $API_ROOT_URL  = "https://api.mercadolibre.com";
    protected static $OAUTH_URL     = "/oauth/token";

    protected $repository;
    protected $authClientId;
    protected $authClientSecret;
    protected $state;
    protected $redirectUri;
    protected $token;

    /**
     * Constructor
     *
     * @param string $state
     */
    public function __construct(string $state) {
        $this->authClientId = config('meli.auth.client_id');
        $this->authClientSecret = config('meli.auth.client_secret');
        $this->state = $state;
        $this->repository = new MeliAppTokenRepository();
        $this->token = $this->repository->find($state);
    }

    /**
     * Return a string with a complete Meli login url.
     * NOTE: You can modify the $AUTH_URL to change the language of login
     *
     * @param string $redirect_uri
     * @param string|null $auth_url
     * @return string
     */
    public function getAuthUrl(string $redirect_uri, string $auth_url = null): string {
        $this->redirectUri = $redirect_uri;
        $params = array("client_id" => $this->authClientId, "response_type" => "code", "redirect_uri" => $redirect_uri, 'state' => $this->state);
        return ($auth_url ?? AuthEnum::MLB) . "/authorization?" . http_build_query($params);
    }

    /**
     * Executes a POST Request to authorize the application and take
     * an AccessToken.
     *
     * @param string $code
     * @param string $redirectUri
     * @return stdClass
     * @throws Exception
     */
    public function authorize(string $code, string $redirectUri): stdClass {
        if($redirectUri) $this->redirectUri = $redirectUri;
        $body = [
            "grant_type" => "authorization_code",
            "client_id" => $this->authClientId,
            "client_secret" => $this->authClientSecret,
            "code" => $code,
            "redirect_uri" => $this->redirectUri
        ];

        $opts = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body
        ];
        $query = $this->execute(self::$OAUTH_URL, $opts);
        if($query->httpCode == 200) {
            $this->repository->delete($this->state);
            $token = $this->repository->create([
                'state' => $this->state,
                'access_token' => $query->response->access_token,
                'access_token_expires_at' => date('Y-m-d H:i:s', time() + $query->response->expires_in),
                'refresh_token' => $query->response->refresh_token,
                'refresh_token_expires_at' => Carbon::now()->addMonths(6)->format('Y-m-d H:i:s')
            ]);
            $this->token = $token;
        }
        return $query;
    }

    /**
     * Execute a POST Request to create a new AccessToken from an existent refresh_token
     *
     * @return stdClass
     * @throws Exception
     */
    private function refreshAccessToken(): stdClass {
        if($this->token->refresh_token) {
            $opts = [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => [
                    "grant_type" => "refresh_token",
                    "client_id" => $this->authClientId,
                    "client_secret" => $this->authClientSecret,
                    "refresh_token" => $this->token->refresh_token
                ]
            ];

            $query = $this->execute(self::$OAUTH_URL, $opts);
            if($query->httpCode == 200) {
                $token = $this->repository->update($this->state, [
                    'access_token' => $query->response->access_token,
                    'access_token_expires_at' => date('Y-m-d H:i:s', time() + $query->response->expires_in),
                    'refresh_token' => $query->response->refresh_token
                ]);
                $this->token = $token;
            }
            return $query;
        } else {
            $ret = new stdClass();
            $ret->error = 'Offline-Access is not allowed.';
            $ret->httpCode = null;
            return $ret;
        }
    }

    /**
     * Disconnect from Mercado Livre
     */
    public function disconnect(): void {
        $queryUser = $this->get('/users/me');
        $userId = $queryUser->response->id;
        $queryDelete = $this->delete("/users/{$userId}/applications/{$this->authClientId}");
        $this->repository->delete($this->state);
    }

    /**
     * @return bool
     */
    public function isConnected(): bool {
        return $this->token->access_token ?? false;
    }

    /**
     * Return the current token
     * @return MeliAppToken
     */
    public function getToken(): MeliAppToken {
        return $this->token;
    }

    /**
     * Execute a GET Request
     *
     * @param string $path
     * @param array $params
     * @param boolean $assoc
     * @return stdClass
     * @throws Exception
     */
    public function get(string $path, array $params = [], bool $assoc = false): stdClass {
        $opts = [
            CURLOPT_HTTPHEADER => $this->bearerHeader()
        ];
        return $this->execute($path, $opts, $params, $assoc);
    }

    /**
     * Execute a POST Request
     *
     * @param string $path
     * @param array $body
     * @return stdClass
     * @throws Exception
     */
    public function post(string $path, array $body = []): stdClass {
        $body = json_encode($body);
        $opts = [
            CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $this->bearerHeader()),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body
        ];
        return $this->execute($path, $opts);
    }

    /**
     * Execute a PUT Request
     *
     * @param string $path
     * @param array $body
     * @param array $params
     * @return stdClass
     * @throws Exception
     */
    public function put(string $path, array $body = [], array $params = []): stdClass {
        $body = json_encode($body);
        $opts = [
            CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $this->bearerHeader()),
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $body
        ];
        return $this->execute($path, $opts, $params);
    }

    /**
     * Execute a DELETE Request
     *
     * @param string $path
     * @param array $params
     * @return stdClass
     * @throws Exception
     */
    public function delete(string $path, array $params = []): stdClass {
        $opts = [
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => $this->bearerHeader()
        ];
        return $this->execute($path, $opts, $params);
    }

    /**
     * Execute a OPTION Request
     *
     * @param string $path
     * @param array $params
     * @return stdClass
     * @throws Exception
     */
    public function options(string $path, array $params = []): stdClass {
        $opts = [
            CURLOPT_CUSTOMREQUEST => "OPTIONS",
            CURLOPT_HTTPHEADER => $this->bearerHeader()
        ];
        return $this->execute($path, $opts, $params);
    }

    /**
     * Execute all requests and returns the json body and headers
     *
     * @param string $path
     * @param array $opts
     * @param array $params
     * @param bool $assoc
     * @return stdClass
     * @throws Exception
     */
    protected function execute(string $path, array $opts = [], array $params = [], bool $assoc = false): stdClass {
        $uri = $this->make_path($path, $params);
        $ch = curl_init($uri);
        $defaultOpts = config('meli.curl_default_opts', []);
        $options = $opts + $defaultOpts;
        curl_setopt_array($ch, $options);
        $return = new stdClass();
        $return->response = json_decode(curl_exec($ch), $assoc);
        $return->httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // if refresh_token is invalid, delete token to force new login
        if($return->httpCode == 400 && $return->response->error == 'invalid_grant') {
            $this->disconnect();
            throw new Exception($return->response->message);
        }
        return $return;
    }

    /**
     * Validate token
     * @throws Exception
     */
    protected function validateToken(): void {
        if(!$this->token->access_token) {
            throw new Exception('You are disconnected from Mercado Livre!');
        }

        $expiration = Carbon::make($this->token->access_token_expires_at);
        $now = Carbon::now();
        if($now->gt($expiration)) {
            $query = $this->refreshAccessToken();
            if($query->httpCode != 200) {
                throw new Exception('Unable to refresh access token!');
            }
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function bearerHeader(): array {
        $this->validateToken();
        return ["Authorization: Bearer {$this->token->access_token}"];
    }

    /**
     * Check and construct a real URL to make request
     *
     * @param string $path
     * @param array $params
     * @return string
     */
    private function make_path(string $path, array $params = []): string {
        if (!preg_match("/^http/", $path)) {
            if (!preg_match("/^\//", $path)) {
                $path = "/{$path}";
            }
            $uri = self::$API_ROOT_URL . $path;
        } else {
            $uri = $path;
        }

        if(!empty($params)) {
            $paramsJoined = array();
            foreach($params as $param => $value) {
                $paramsJoined[] = "$param=$value";
            }
            $params = '?' . implode('&', $paramsJoined);
            $uri = $uri . $params;
        }
        return $uri;
    }
}
