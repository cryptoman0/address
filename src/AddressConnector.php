<?php

namespace Cryptoman\Address;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AddressConnector
{
    /**
     * Base URI for API
     * @var Client
     */
    private $client;
    /**
     * Current coin (btc, eth, ltc, etc...)
     * @var string
     */
    private $coin;
    /**
     * X-Auth-token for use API
     * You can get in personal area
     * @var string
     */
    private $api_token;
    /**
     * Secret-token for signing requests with params
     * You can get in personal area
     * @var string
     */
    private $secret_token;

    /**
     * Wallet constructor for API
     * @param string $coin
     * @param string $api_token
     * @param string|null $secret_token
     */
    public function __construct(string $coin, $api_token, $secret_token = null)
    {
        $this->coin = $coin;
        $this->api_token = $api_token;
        $this->secret_token = $secret_token;

        $this->client = new Client([
            'base_uri' => 'https://api.address.so/api/'
        ]);
    }

    /**
     * Get all coins.
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCoins(): array
    {
        return $this->request('GET', $this->makeUrl('coins', 'all'));
    }

    /**
     * Get coin info by symbol.
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCoin(): array
    {
        return $this->request('GET', $this->makeUrl('coins', 'read'));
    }

    /**
     * Get all wallets by coin.
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWallets() : array
    {
        return $this->request('GET', $this->makeUrl('wallet', 'all'));
    }

    /**
     * Get wallet by id and coin.
     * @param int $wallet_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWallet(int $wallet_id) : array
    {
        return $this->request('GET', $this->makeUrl('wallet', 'read', $wallet_id));
    }

    /**
     * Create new wallet by coin.
     * @param string $wallet_name
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createWallet(string $wallet_name)
    {
        $params = [
            'label' => $wallet_name
        ];

        return $this->request('POST', $this->makeUrl('wallet', 'create'), $params);
    }

    /**
     * Update wallet by id and coin.
     * @param int $wallet_id
     * @param string $wallet_name
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateWallet(int $wallet_id, string $wallet_name) : array
    {
        $params = [
            'label' => $wallet_name
        ];

        return $this->request('PUT', $this->makeUrl('wallet', 'update', $wallet_id), $params);

    }

    /**
     * Delete wallet by id and coin.
     * @param int $wallet_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteWallet(int $wallet_id) : array
    {
        return $this->request('DELETE', $this->makeUrl('wallet', 'delete', $wallet_id));
    }

    /**
     * Get all account transactions.
     * @param int $wallet_id
     * @param int $limit
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWalletTransactions(int $wallet_id, int $limit = 100) : array
    {
        $params = [
            'limit' => $limit
        ];

        return $this->request('GET', $this->makeUrl('wallet', 'transactions', $wallet_id), $params);
    }

    /**
     * Send from wallet by coin.
     * @param int $wallet_id
     * @param float $amount
     * @param string $recepient
     * @param string|null $odd_address
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendFromWallet(int $wallet_id, float $amount, string $recepient, string $odd_address = null) : array
    {
        $params = [
            'amount' => $amount,
            'recepient' => $recepient,
        ];

        if ($odd_address) {
            $params = array_merge($params, [
                'odd_address' => $odd_address
            ]);
        }

        return $this->request('POST', $this->makeUrl('wallet', 'send', $wallet_id), $params, true);
    }

    /**
     * Get all accounts by coin.
     * @param int $wallet_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAccounts(int $wallet_id) : array
    {
        return $this->request('GET', $this->makeUrl('account', 'all', $wallet_id));
    }

    /**
     * Get account by id.
     * @param int $wallet_id
     * @param int $account_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAccount(int $wallet_id, int $account_id) : array
    {
        return $this->request('GET', $this->makeUrl('account', 'read', $wallet_id, $account_id));
    }

    /**
     * Create new account by coin.
     * @param int $wallet_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createAccount(int $wallet_id) : array
    {
        return $this->request('POST', $this->makeUrl('account', 'create', $wallet_id));
    }

    /**
     * Delete account by id and coin.
     * @param int $wallet_id
     * @param int $account_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteAccount(int $wallet_id, int $account_id) : array
    {
        return $this->request('DELETE', $this->makeUrl('account', 'delete', $wallet_id, $account_id));
    }

    /**
     * Archive accounts
     * @param int $wallet_id
     * @param array $accounts
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function archiveAccounts(int $wallet_id, array $accounts): array
    {
        $params = [
            'accounts' => $accounts
        ];

        return  $this->request('DELETE', $this->makeUrl('account', 'archive', $wallet_id), $params);
    }

    /**
     * Get all account transactions.
     * @param int $wallet_id
     * @param int $account_id
     * @param int $limit
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAccountTransactions(int $wallet_id, int $account_id, int $limit = 100) : array
    {
        $params = [
            'limit' => $limit
        ];

        return $this->request('GET', $this->makeUrl('account', 'transactions', $wallet_id, $account_id), $params);
    }

    /**
     * Send from account.
     * @param int $wallet_id
     * @param int $account_id
     * @param float $amount
     * @param string $recepient
     * @param string|null $odd_address
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendFromAccount(int $wallet_id, int $account_id, float $amount, string $recepient, string $odd_address = null) : array
    {
        $params = [
            'amount' => $amount,
            'recepient' => $recepient
        ];

        if ($odd_address) {
            $params = array_merge($params, [
                'odd_address' => $odd_address
            ]);
        }

        return $this->request('POST', $this->makeUrl('account', 'send', $wallet_id, $account_id), $params, true);
    }

    /**
     * Set permissions to wallet for any user
     * Accept array of Available permissions: view, order, transfer, admin
     * @param int $wallet_id
     * @param int $user_id
     * @param array $permissions
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function setPermissions(int $wallet_id, int $user_id, array $permissions = []) : array
    {
        $params = [
            'user_id' => $user_id,
            'permissions' => $permissions
        ];

        return $this->request('POST', $this->makeUrl('wallet', 'permissions', $wallet_id), $params);
    }

    /**
     * Remove all permissions for wallet for any user
     * @param int $wallet_id
     * @param int $user_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function removeAllPermissions(int $wallet_id, int $user_id) : array
    {
        $params = [
            'user_id' => $user_id,
            'permissions' => ['0']
        ];

        return $this->request('POST', $this->makeUrl('wallet', 'permissions', $wallet_id), $params);
    }

    /**
     * Make url for request.
     * @param string $type
     * @param string $method
     * @param int|null $wallet_id
     * @param int|null $account_id
     * @return string
     */
    private function makeUrl(string $type, string $method, int $wallet_id = null, int $account_id = null) : string
    {
        $coins = 'coins';
        $coin = sprintf('coins/%s/', $this->coin);
        $wallets = 'wallets/';
        $wallet = sprintf('%s/', $wallet_id);
        $accounts = 'accounts/';
        $account = sprintf('%s/', $account_id);
        $transactions = 'transactions/';
        $send = 'send/';
        $permissions = 'permissions/';
        $archive = 'archive/';

        $urls = [
            'coins' => [
                'all' => $coins,
                'read' => $coin
            ],
            'wallet' => [
                'all' => $coin . $wallets,
                'create' => $coin . $wallets,
                'read' => $coin . $wallets . $wallet,
                'update' => $coin . $wallets . $wallet,
                'delete' => $coin . $wallets . $wallet,
                'send' => $coin . $wallets . $wallet . $send,
                'permissions' => $coin . $wallets . $wallet . $permissions,
                'transactions' => $coin . $wallets . $wallet . $transactions,
            ],
            'account' => [
                'all' => $coin . $wallets . $wallet . $accounts,
                'create' => $coin . $wallets . $wallet . $accounts,
                'read' => $coin . $wallets . $wallet . $accounts . $account,
                'delete' => $coin . $wallets . $wallet . $accounts . $account,
                'archive' => $coin . $wallets . $wallet . $accounts . $archive,
                'send' => $coin . $wallets . $wallet . $accounts . $account . $send,
                'transactions' => $coin . $wallets . $wallet . $accounts . $account . $transactions,
            ],
        ];

        return $urls[$type][$method];
    }

    /**
     * Request method (Guzzle HTTP client)
     * @param string $method
     * @param string $url
     * @param array $params
     * @param bool $sign
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function request(string $method, string $url, array $params = [], bool $sign = false) : array
    {
        try {
            $query['form_params'] = $params;
            $query['headers']['X-Api-Token'] = $this->api_token;

            if ($sign) {
                $query['form_params']['sign'] = $this->signParams($params);
            }

            $request = $this->client->request($method, $url, $query);

            if ($request) {
                $response = $request->getBody()->getContents();
            }
        } catch (RequestException $exception) {
            throw new RequestException($exception->getMessage(), $exception->getRequest());
        }

        $data = json_decode($response, true);

        return $data;
    }

    /**
     * Sign params with secret key.
     * @param array $params
     * @return string
     */
    private function signParams(array $params) : string
    {
        return hash_hmac('sha256', http_build_query($params), $this->secret_token);
    }

}
