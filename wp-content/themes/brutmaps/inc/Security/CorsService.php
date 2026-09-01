<?php

namespace Brut\Security;

class CorsService
{
    public function __construct()
    {
        add_action('rest_pre_serve_request', [$this, 'handleCors']);
        add_filter('graphql_access_control_allow_headers', [$this, 'allowGraphQLHeaders']);
        add_filter('graphql_response_headers_to_send', [$this, 'exposeGraphQLHeaders']);
    }

    public function handleCors(): void
    {

        $origin = get_http_origin();

        $allowed = [
            'https://brutmaps.com',
            'https://brutmapsdev.cybers.pro',
            'http://localhost:3033',
        ];

        if (in_array($origin, $allowed)) {
            header("Access-Control-Allow-Origin: $origin");
            header('Access-Control-Allow-Methods: POST, OPTIONS');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce');
        }
    }

    /**
     * Lets the browser send the WooCommerce (WooGraphQL) headless cart
     * session token on cross-origin GraphQL requests.
     *
     * @param string[] $headers
     * @return string[]
     */
    public function allowGraphQLHeaders(array $headers): array
    {
        $headers[] = 'woocommerce-session';

        return $headers;
    }

    /**
     * Lets the browser read the rotated cart session token back off the
     * GraphQL response (WPGraphQL's own Access-Control-Allow-Origin is
     * already "*", so no Allow-Credentials is needed here).
     *
     * @param array<string,string> $headers
     * @return array<string,string>
     */
    public function exposeGraphQLHeaders(array $headers): array
    {
        $headers['Access-Control-Expose-Headers'] = 'woocommerce-session';

        return $headers;
    }
}
