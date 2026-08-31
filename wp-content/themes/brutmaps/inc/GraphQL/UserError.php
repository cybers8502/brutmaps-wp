<?php

namespace Brut\GraphQL;

use RuntimeException;

/**
 * Thrown inside GraphQL resolvers to return a user-facing error message.
 * WPGraphQL catches this and adds it to the `errors` array in the response.
 */
class UserError extends RuntimeException
{
}
