<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class StrapiService
{
    protected string $baseUrl;
    protected ?string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.strapi.url', ''), '/');
        $this->token   = config('services.strapi.token');
    }

    protected function client()
    {
        $client = Http::baseUrl($this->baseUrl . '/api');

        if (!empty($this->token)) {
            $client = $client->withToken($this->token);
        }

        return $client;
    }

    /**
     * LANGUAGES
     */
    public function getLanguages(int $page, int $pageSize): array
    {
        $response = $this->client()->get('/languages', [
            'pagination[page]'     => $page,
            'pagination[pageSize]' => $pageSize,
        ]);

        return $response->json();
    }

    /**
     * CATEGORIES
     */
    public function getCategories(int $page, int $pageSize): array
    {
        $response = $this->client()->get('/categories', [
            'pagination[page]'     => $page,
            'pagination[pageSize]' => $pageSize,
        ]);

        return $response->json();
    }

    /**
     * ARTICLES list with filters (category, language, search)
     */
    public function getArticles(array $filters, int $page, int $pageSize): array
    {
        $query = [
            'pagination[page]'     => $page,
            'pagination[pageSize]' => $pageSize,
            'populate[category]'   => '*',
            'populate[language]'   => '*',
            'populate[author]'     => '*',
        ];

        if (!empty($filters['category_id'])) {
            $query['filters[category][id][$eq]'] = (int) $filters['category_id'];
        }

        if (!empty($filters['language_id'])) {
            $query['filters[language][id][$eq]'] = (int) $filters['language_id'];
        }

        if (!empty($filters['search'])) {
            $query['filters[title][$containsi]'] = $filters['search'];
        }

        $response = $this->client()->get('/articles', $query);

        return $response->json();
    }

    /**
     * ARTICLE by id
     */
    public function getArticleById(int $id): array
    {
        $response = $this->client()->get('/articles/' . $id, [
            'populate[category]' => '*',
            'populate[language]' => '*',
            'populate[author]'   => '*',
        ]);

        return $response->json();
    }
}
