#Documentation
##API Documentation

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
