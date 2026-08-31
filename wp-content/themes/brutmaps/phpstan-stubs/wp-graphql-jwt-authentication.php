<?php

namespace WPGraphQL\JWT_Authentication;

/**
 * Stub for static analysis only. The real class ships in the
 * wp-graphql-jwt-authentication plugin (not a Composer package, so there is
 * no vendor stub for it) and is never loaded from this file at runtime.
 *
 * @see https://github.com/wp-graphql/wp-graphql-jwt-authentication
 */
class Auth
{
    public static function get_token(\WP_User $user, bool $cap_check = true): ?string
    {
    }

    public static function get_refresh_token(\WP_User $user, bool $cap_check = true): ?string
    {
    }

    public static function revoke_user_secret(int $user_id): void
    {
    }
}

/**
 * Stub for static analysis only, see Auth above.
 */
class ManageTokens
{
    public static function add_tokens_to_graphql_response_headers(array $headers): array
    {
    }
}
