<?php

namespace Brut\GraphQL;

class GraphQLRegistrar
{
    public function boot(): void
    {
        add_action('graphql_register_types', [$this, 'register']);
    }

    public function register(): void
    {
        (new AuthGraphQL())->registerTypes();
        (new ObjectsGraphQL())->registerTypes();
        (new MediaGraphQL())->registerTypes();
        (new ProfileGraphQL())->registerTypes();
    }
}
