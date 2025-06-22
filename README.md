# PHP cURL Example for Search API

This is a PHP script that uses cURL to send a search request to an external API, retrieve HTML content from the response, extract links, and further retrieve metadata from those links, such as thumbnail and video URLs.

## Requirements

- PHP 7.0 or higher
- cURL enabled in PHP
- `DOMDocument` and `DOMXPath` extensions enabled in PHP

## Usage

1. Clone this repository to your local machine:

    ```bash
    git clone https://github.com/YOUR_USERNAME/php-curl-example.git
    cd php-curl-example
    ```

2. Create a new PHP file in your project directory, e.g., `search-api.php`.
3. Paste the provided PHP code into the `search-api.php` file.
4. Call this file via a browser or command line. For example, to search for a term, use:

    ```
    http://yourdomain.com/search-api.php?search=term
    ```

   Replace `term` with your search keyword.

5. The script will return JSON data with the search results, including links and metadata (thumbnails and video URLs).

## How It Works

- The script first receives a search term from the `GET` query parameter `search`.
- It sends a `POST` request to `https://fibwatch.art/aj/search` with the search term and a hash.
- The response is parsed to extract all `<a>` tags with their `href` attributes and text content.
- For each link, it makes a request to retrieve the metadata from the linked page, specifically looking for `thumbnail` and `video` URLs.
- The results are returned as a JSON response.

## Example Output

```json
{
  "data": [
    {
      "text": "Example Video 1",
      "url": "https://fibwatch.art/example1",
      "thumbnail_url": "https://example.com/thumbnail1.jpg",
      "video_url": "https://example.com/video1.mp4"
    },
    {
      "text": "Example Video 2",
      "url": "https://fibwatch.art/example2",
      "thumbnail_url": "https://example.com/thumbnail2.jpg",
      "video_url": "https://example.com/video2.mp4"
    }
  ]
}
