<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Yegob_WP_Service
{
    const API_URL = "https://yegob.rw/wp-json";

    private $apiKey;

    private $client;
    public function __construct(
        HttpClientInterface $httpClient,
        string $apiUsername,
        string $apiPassword
    ){
        $cache = new FilesystemAdapter();
        $this->client = $httpClient;
        $this->apiKey = $cache->get('yegob_wp_api_token',
            function(ItemInterface $item) use ( $httpClient, $apiUsername, $apiPassword) {
                $item->expiresAfter(432000); // token that expires after 5 days
                $response = $this->client->request(
                    'POST',
                    self::API_URL."/jwt-auth/v1/token",
                    [
                        'body' => [
                            'username' => $apiUsername,
                            'password' => $apiPassword
                        ]
                    ]
                );

                if(Response::HTTP_OK === $response->getStatusCode()){
                    return $response->toArray()['data']['token'];
                }
                return "";
            }
        );
    }

    public function request(string $endpoint, array $options = [], string $method = 'GET'){
        return $this->client->request(
            $method,
            self::API_URL.'/wp/v2'.$endpoint,
            array_merge(
                ['auth_bearer' => $this->getApiKey()],
                $options
            )
        );
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getUsersFromWP(int $page = 1): array{
        $response = $this->request(
            '/users',
            [
                'query' => [
                    'page' => $page,
                    'per_page' => 50,
                    'roles' => 'editor',
                    'orderby' => 'id'
                ]
            ]
        );
        dd($response->getStatusCode());
        if($response->getStatusCode() === Response::HTTP_OK){
            return $response->toArray();
        }
        return [];
    }

    public function getPostFromWP(int $postId){
        $response = $this->request("/posts/$postId");

        if($response->getStatusCode() === Response::HTTP_OK){
            return $response->toArray();
        }
        return null;
    }

    public function getPostsFromWP(int $page = 1, int $postsPerPage = 20): array
    {
        $response = $this->request(
            '/posts',
            [
                'query' => [
                    'page' => $page,
                    'per_page' => $postsPerPage,
                ]
            ]
        );
        if($response->getStatusCode() === Response::HTTP_OK){
            return $response->toArray();
        }
        return [];
    }
}