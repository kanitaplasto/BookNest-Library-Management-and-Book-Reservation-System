<?php

require_once __DIR__ . '/ApiException.php';
require_once __DIR__ . '/ApiLogger.php';

/**
 * Searches public book metadata.
 * Google Books is used when an API key is configured; Open Library is the
 * keyless fallback so the Discover feature remains usable in a fresh local setup.
 */
class GoogleBooksService
{
    private array $config;

    public function __construct()
    {
        $configPath = __DIR__ . '/../config/config.php';
        if (!is_file($configPath)) {
            throw new ApiException('Book search is not configured. Create backend/config/config.php.', 503);
        }

        $config = require $configPath;
        $this->config = is_array($config) ? $config : [];
    }

    public function search(string $query, int $maxResults = 12): array
    {
        $query = trim($query);

        if ($query === '') {
            throw new InvalidArgumentException('Enter a book title, author, or ISBN to search.');
        }
        if (mb_strlen($query) > 120) {
            throw new InvalidArgumentException('Search query must not exceed 120 characters.');
        }
        if ($maxResults < 1 || $maxResults > 20) {
            throw new InvalidArgumentException('maxResults must be between 1 and 20.');
        }
        if (!function_exists('curl_init')) {
            ApiLogger::error('External book search failed because the PHP cURL extension is not enabled.');
            throw new ApiException('Book search is temporarily unavailable. Enable the PHP cURL extension and try again.', 503);
        }

        $apiKey = trim((string) ($this->config['google_books_api_key'] ?? ''));

        if ($apiKey !== '') {
            try {
                return $this->searchGoogleBooks($query, $maxResults, $apiKey);
            } catch (ApiException $exception) {
                // Keep the feature available if Google's key/quota/service fails.
                ApiLogger::error('Google Books failed; trying Open Library fallback. ' . $exception->getMessage());
            }
        } else {
            ApiLogger::info('Google Books API key is empty; using the Open Library fallback.');
        }

        return $this->searchOpenLibrary($query, $maxResults);
    }

    private function searchGoogleBooks(string $query, int $maxResults, string $apiKey): array
    {
        $url = 'https://www.googleapis.com/books/v1/volumes?' . http_build_query([
            'q' => $query,
            'maxResults' => $maxResults,
            'printType' => 'books',
            'projection' => 'lite',
            'key' => $apiKey
        ]);

        $decoded = $this->requestJson($url, 'Google Books');
        $items = [];

        foreach (($decoded['items'] ?? []) as $item) {
            $volume = $item['volumeInfo'] ?? [];
            $imageLinks = $volume['imageLinks'] ?? [];
            $thumbnail = $imageLinks['thumbnail'] ?? $imageLinks['smallThumbnail'] ?? null;

            if (is_string($thumbnail) && str_starts_with($thumbnail, 'http://')) {
                $thumbnail = 'https://' . substr($thumbnail, 7);
            }

            $identifiers = [];
            foreach (($volume['industryIdentifiers'] ?? []) as $identifier) {
                if (!empty($identifier['type']) && !empty($identifier['identifier'])) {
                    $identifiers[$identifier['type']] = $identifier['identifier'];
                }
            }

            $items[] = [
                'id' => $item['id'] ?? null,
                'title' => $volume['title'] ?? 'Untitled book',
                'authors' => $volume['authors'] ?? [],
                'publisher' => $volume['publisher'] ?? null,
                'publishedDate' => $volume['publishedDate'] ?? null,
                'description' => isset($volume['description']) ? trim(strip_tags($volume['description'])) : null,
                'categories' => $volume['categories'] ?? [],
                'pageCount' => $volume['pageCount'] ?? null,
                'thumbnail' => $thumbnail,
                'infoLink' => $volume['infoLink'] ?? null,
                'isbn10' => $identifiers['ISBN_10'] ?? null,
                'isbn13' => $identifiers['ISBN_13'] ?? null,
                'averageRating' => $volume['averageRating'] ?? null
            ];
        }

        ApiLogger::info('Google Books search completed; query_length=' . mb_strlen($query));

        return [
            'source' => 'Google Books',
            'totalItems' => (int) ($decoded['totalItems'] ?? 0),
            'items' => $items
        ];
    }

    private function searchOpenLibrary(string $query, int $maxResults): array
    {
        $url = 'https://openlibrary.org/search.json?' . http_build_query([
            'q' => $query,
            'limit' => $maxResults,
            'fields' => 'key,title,author_name,publisher,first_publish_year,cover_i,isbn'
        ]);

        $decoded = $this->requestJson($url, 'Open Library');
        $items = [];

        foreach (($decoded['docs'] ?? []) as $book) {
            $isbnList = is_array($book['isbn'] ?? null) ? $book['isbn'] : [];
            $isbn10 = null;
            $isbn13 = null;

            foreach ($isbnList as $isbn) {
                $normalizedIsbn = strtoupper(preg_replace('/[^0-9X]/i', '', (string) $isbn));
                if ($isbn10 === null && strlen($normalizedIsbn) === 10) {
                    $isbn10 = $normalizedIsbn;
                }
                if ($isbn13 === null && strlen($normalizedIsbn) === 13) {
                    $isbn13 = $normalizedIsbn;
                }
            }

            $coverId = isset($book['cover_i']) ? (int) $book['cover_i'] : 0;
            $key = isset($book['key']) ? (string) $book['key'] : '';

            $items[] = [
                'id' => $key !== '' ? $key : null,
                'title' => $book['title'] ?? 'Untitled book',
                'authors' => $book['author_name'] ?? [],
                'publisher' => $book['publisher'][0] ?? null,
                'publishedDate' => isset($book['first_publish_year']) ? (string) $book['first_publish_year'] : null,
                'description' => null,
                'categories' => [],
                'pageCount' => null,
                'thumbnail' => $coverId > 0 ? 'https://covers.openlibrary.org/b/id/' . $coverId . '-M.jpg?default=false' : null,
                'infoLink' => str_starts_with($key, '/') ? 'https://openlibrary.org' . $key : null,
                'isbn10' => $isbn10,
                'isbn13' => $isbn13,
                'averageRating' => null
            ];
        }

        ApiLogger::info('Open Library fallback search completed; query_length=' . mb_strlen($query));

        return [
            'source' => 'Open Library',
            'totalItems' => (int) ($decoded['numFound'] ?? count($items)),
            'items' => $items
        ];
    }

    /** Sends a bounded GET request and validates the external JSON response. */
    private function requestJson(string $url, string $provider): array
    {
        $curl = curl_init($url);
        if ($curl === false) {
            ApiLogger::error($provider . ' request could not initialize cURL.');
            throw new ApiException('Book search is temporarily unavailable. Please try again later.', 503);
        }

        $userAgent = trim((string) ($this->config['open_library_user_agent'] ?? 'BookNestLibrary/1.0 (student project)'));
        if ($userAgent === '') {
            $userAgent = 'BookNestLibrary/1.0 (student project)';
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => $userAgent
        ]);

        $body = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($body === false) {
            ApiLogger::error($provider . ' request failed: ' . $curlError);
            throw new ApiException('Book search is temporarily unavailable. Please try again later.', 503);
        }

        if ($httpStatus < 200 || $httpStatus >= 300) {
            ApiLogger::error($provider . ' returned HTTP ' . $httpStatus . ' for a search request.');
            throw new ApiException('Book search is temporarily unavailable. Please try again later.', 503);
        }

        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            ApiLogger::error($provider . ' returned invalid JSON: ' . $exception->getMessage());
            throw new ApiException('Book search returned an invalid response. Please try again later.', 503);
        }

        if (!is_array($decoded)) {
            ApiLogger::error($provider . ' returned an unexpected JSON structure.');
            throw new ApiException('Book search returned an invalid response. Please try again later.', 503);
        }

        return $decoded;
    }
}
