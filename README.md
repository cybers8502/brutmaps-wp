# Documentation

## API Documentation

### **Requests**

`BASE_URL: https//DOMAIN/wp-json/`

#### Global Failure Auth Response
```
{
    "success": false,
    "statusCode": 403,
    "code": "jwt_auth_invalid_token",
    "message": "Malformed UTF-8 characters",
    "data": []
}
```
---
#### Login

##### Request

`POST /jwt-auth/v1/token`

**Parameters** | **Description**
-------------  | ---------------
**`username`** | Required
**`password`** | Required

##### Response
```
{
     "success": true,
     "statusCode": 200,
     "code": "jwt_auth_valid_credential",
     "message": "Credential is valid",
     "data": {
         "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODg4OFwvYnJ1dG1hcHMtd3AiLCJpYXQiOjE2MTM1NTg0ODUsIm5iZiI6MTYxMzU1ODQ4NSwiZXhwIjoxNjE0MTYzMjg1LCJkYXRhIjp7InVzZXIiOnsiaWQiOjF9fX0.3uN-aVoqPKcd7Pd1DTl2CsEkM6mWSOvA0RA4yaRcGFQ",
         "id": 1,
         "email": "max@designstudio.ag",
         "nicename": "designstudio_developer",
         "firstName": "",
         "lastName": "",
         "displayName": "designstudio_developer"
     }
 }
```
---

#### Registration

##### Request

`POST /maps/data/v1/api/registration`

**Parameters** | **Description**
-------------  | ---------------
**`username`** | Required
**`password`** | Required

##### Response
```
{
     "success": true,
     "data": {
         "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODg4OFwvYnJ1dG1hcHMtd3AiLCJpYXQiOjE2MTM1NTg0ODUsIm5iZiI6MTYxMzU1ODQ4NSwiZXhwIjoxNjE0MTYzMjg1LCJkYXRhIjp7InVzZXIiOnsiaWQiOjF9fX0.3uN-aVoqPKcd7Pd1DTl2CsEkM6mWSOvA0RA4yaRcGFQ",
         "id": 1
     }
 }
```
---

---

## Environment

This repo does **not** track WordPress core (`wp-admin/`, `wp-includes/`, root
core files) or vendor/public plugin code — both are identical, downloadable
artifacts that only produce churn when committed. Reproduce them instead of
tracking them:

- **WordPress core:** currently `6.8.2`. `wp core download --version=6.8.2`.
- **Plugins:** see [`wp-content/plugins.lock.json`](wp-content/plugins.lock.json)
  for the full list of installed plugins with version + source (wordpress.org,
  vendor site, or license). Reinstall with `wp plugin install <slug> --version=X.Y.Z`
  for anything on wordpress.org; licensed/vendor plugins per their `source`.
  Regenerate the lock file after any plugin update:
  `wp plugin list --format=json`.

Three plugins are project-written (not downloadable from anywhere) and stay
tracked in this repo: `wp-content/plugins/acf-image-sidebar-meta`,
`wp-content/plugins/gallery-limit`, `wp-content/plugins/cache-cleaner`.

The theme (`wp-content/themes/brutmaps`) and media (`wp-content/uploads`) are
each their own repo — `brutmaps-wp-theme` and `brutmaps-uploads`.
