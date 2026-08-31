# Brutmaps — Backend

WordPress site for Brutmaps. Application logic lives in the theme at
`wp-content/themes/brutmaps/` (namespace `Brut\`, PSR-4 autoloaded).

Part of a three-repo project:

| Repo | Role |
| --- | --- |
| **`wp-brutmaps`** (this repo) | WordPress backend — REST (`/wp-json/v1`) + GraphQL (`/graphql`) API, content/admin |
| [`r-brutmaps`](../r-brutmaps) | Web app (React/Vite) — public site, map, shop, account |
| [`expo-brutmaps`](../expo-brutmaps) | Mobile app (Expo/React Native, iOS/Android) |

Both clients consume this backend: most data (sights, architects, blog, shop,
favorites, profile) over REST, auth (`login`, `register`, `googleAuth`,
`checkEmail`, token refresh) over GraphQL. The API reference below is shared
by both clients.

## Development

All tooling runs from the theme directory:

```bash
cd wp-content/themes/brutmaps
composer install        # dependencies + git hooks (CaptainHook)
composer check          # lint + phpstan + phpunit + phpcs
```

Individual steps: `composer lint`, `composer phpstan`, `composer test`,
`composer phpcs` (autofix: `composer phpcbf`).

**Requirements:** PHP 8.1+ (production runs 8.4), Composer 2.

### Quality gates

| Tool | Config | Checks |
| --- | --- | --- |
| php-parallel-lint | — | syntax |
| PHPStan (level 5) | `phpstan.neon` | types; WP / WooCommerce / ACF / WPGraphQL stubs |
| PHPUnit 10 | `phpunit.xml` | unit tests (`tests/Unit`, Brain Monkey) |
| PHPCS | `phpcs.xml` | PSR-12 + `WordPress.Security` / DB / I18n + PHPCompatibility 8.1+ |

### Git hooks (installed automatically by `composer install`)

- **pre-commit** — lint + phpcs + phpstan on staged PHP files, then phpunit.
- **pre-push** — full `composer check`.

Bypass in an emergency with `git commit --no-verify`.

### CI

`.github/workflows/ci.yml` runs on push to `main` and on pull requests,
matrix PHP 8.1 / 8.4: lint + phpstan + phpunit + phpcs.

---

## API

- **REST:** `https://DOMAIN/wp-json/v1/…`
- **GraphQL:** `https://DOMAIN/graphql` (WPGraphQL + wp-graphql-jwt-authentication)

Authentication is JWT-based, issued by the **wp-graphql-jwt-authentication**
plugin (same as the sibling `wp-coins` project). Every GraphQL request is
authenticated from `Authorization: Bearer <authToken>` — resolvers can use
`is_user_logged_in()` / `get_current_user_id()` on any query or mutation, not
just the ones below.

**Site requirements (outside this repo):** the plugin installed + active, and
`define('GRAPHQL_JWT_AUTH_SECRET_KEY', '…')` in `wp-config.php`.

### Auth (GraphQL)

`login` and `refreshJwtAuthToken` are provided by the plugin. `register`,
`googleAuth`, `checkEmail` and `logout` are the theme's own
(`inc/GraphQL/AuthGraphQL.php`). `uploadUserPhoto` is a separate,
auth-independent mutation (`inc/GraphQL/MediaGraphQL.php`) that `register`'s
`photoUrl` comes from.

| Mutation | Arguments | Payload |
| --- | --- | --- |
| `login` *(plugin)* | `username!`, `password!` | `authToken`, `refreshToken`, `user` |
| `refreshJwtAuthToken` *(plugin)* | `jwtRefreshToken!` | `authToken` |
| `register` | `email!`, `password!`, `firstName`, `lastName`, `country`, `subscribeToNewsletter`, `photoUrl` | `authPayload { authToken refreshToken user }` |
| `googleAuth` | `email!`, `firstName`, `lastName`, `avatar` | `authPayload { authToken refreshToken user }` (logs in or registers) |
| `checkEmail` | `email!` | `result { exists message }` |
| `logout` *(auth)* | — | `success` |
| `uploadUserPhoto` | `fileBase64!`, `filename!` | `photoUrl` |

`uploadUserPhoto` takes the image as a base64 string (an optional `data:`
URI prefix is stripped if present) rather than a real multipart upload —
GraphQL has no native file-upload support here, and this avoids adding one
just for a single pre-registration photo. Replaces the old
`POST /wp-json/v1/user/photo` REST route, now removed; the separate
authenticated profile-photo replacement in `POST /profile/edit-profile`
still takes a real multipart upload and is unaffected.

```graphql
mutation {
  login(input: { clientMutationId: "1", username: "user@example.com", password: "secret" }) {
    authToken
    refreshToken
    user { email }
  }
}
```

```graphql
mutation {
  register(input: { clientMutationId: "1", email: "user@example.com", password: "secret" }) {
    authPayload { authToken refreshToken user { email } }
  }
}
```

Use `authToken` as the Bearer token; `refreshToken` is long-lived and only
valid for `refreshJwtAuthToken`. `logout` revokes the user's JWT secret,
invalidating every outstanding refresh token for that user (an already-issued
`authToken` stays valid until its own short expiry).

### Sights (GraphQL, RootQuery)

| Field | Arguments | Type | Description |
| --- | --- | --- | --- |
| `sightsMap` | `architects: [ID]`, `taxonomyTerms: [String]` | `SightsMapResult` | all sights as a GeoJSON FeatureCollection |
| `sight` | `identifier: String!` | `SightDetail` | one sight by ID or slug |
| `sightsImages` | `seed: String`, `page: Int`, `perPage: Int` | `SightsImagesResult` | paginated sight preview images |

### REST (`/wp-json/v1`)

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/architects` | list architects |
| GET | `/architects/{id}` | architect by ID |
| GET | `/architects/search` | search architects |
| GET | `/architects/popular` | popular architects |
| GET | `/architecture-types` | architecture types |
| GET | `/map/sights` | sights for the map |
| GET | `/map/sights/{identifier}` | sight by ID or slug |
| GET | `/sights-images` | sight preview images |
| GET | `/blog/posts` | blog posts |
| GET | `/blog/posts/{identifier}` | post by ID or slug |
| GET | `/shop/products` | WooCommerce products |
| GET | `/taxonomies` | taxonomies |
| GET | `/user/favorites` | user favorites *(auth)* |
| POST | `/user/favorites/toggle` | add/remove favorite *(auth)* |
| GET | `/profile/user-profile` | user profile *(auth)* |
| POST | `/profile/edit-profile` | edit profile *(auth)* |
| GET | `/profile/user-countries` | country list |
| DELETE | `/profile/delete-account` | delete account *(auth)* |
| POST | `/auth/lost-password` | request a password reset |
| POST | `/auth/reset-password` | reset password with a key |
| POST | `/auth/change-password` | change password *(auth)* |

#### Global failure response

```json
{
    "success": false,
    "statusCode": 403,
    "code": "jwt_auth_invalid_token",
    "message": "Malformed UTF-8 characters",
    "data": []
}
```

---

## Environment

This repo does **not** track WordPress core (`wp-admin/`, `wp-includes/`, root
core files) or vendor/public plugin code — both are identical, downloadable
artifacts that only produce churn when committed. Reproduce them instead of
tracking them:

- **WordPress core:** currently `6.8.2`. `wp core download --version=6.8.2`.
- **Plugins (25 of 27):** managed via Composer + [WPackagist](https://wpackagist.org)
  (the wordpress.org plugin mirror as Composer packages) for 24 of them, plus one
  GitHub-sourced plugin (`wp-graphql/wp-graphql-jwt-authentication`) via a VCS
  repository. Run `composer install` to fetch them into `wp-content/plugins/`
  (routed there by `composer/installers`). Bump a version with
  `composer require wpackagist-plugin/<slug>:^X.Y.Z`.

  The remaining 2 (`advanced-custom-fields-pro`, `woo-update-manager`) aren't on
  wordpress.org/WPackagist (licensed/marketplace-only) and stay manual — see
  [`wp-content/plugins.lock.json`](wp-content/plugins.lock.json) for every
  plugin's slug/version/source, composer-managed or not. Regenerate it after
  any plugin update: `wp plugin list --format=json`.

Three plugins are project-written (not downloadable from anywhere) and stay
tracked in this repo: `wp-content/plugins/acf-image-sidebar-meta`,
`wp-content/plugins/gallery-limit`, `wp-content/plugins/cache-cleaner`.

The theme (`wp-content/themes/brutmaps`) is tracked in this repo. Media
(`wp-content/uploads`) is still its own repo — `brutmaps-uploads`.
