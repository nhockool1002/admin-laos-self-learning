# Badge Management API Documentation

## Base URL
```
https://your-domain.com/api/v1
```

## Authentication
Some endpoints require authentication. You can implement API key authentication by modifying the controller methods.

## Endpoints

### 1. Get All Badges
Get a list of all available badges.

**Endpoint:** `GET /badges`

**Parameters:**
- None required

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "First Badge",
      "description": "Description of the first badge",
      "image_url": "/assets/images/badge1.png",
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### 2. Get Badge Details
Get details of a specific badge.

**Endpoint:** `GET /badges/{id}`

**Parameters:**
- `id` (integer, required): Badge ID

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "First Badge",
    "description": "Description of the first badge",
    "image_url": "/assets/images/badge1.png",
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Badge not found"
}
```

### 3. Get User Badges
Get all badges awarded to a specific user.

**Endpoint:** `GET /users/{userId}/badges`

**Parameters:**
- `userId` (integer, required): User ID

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "badge_id": 1,
      "awarded_at": "2024-01-15T14:30:00Z",
      "badges": {
        "id": 1,
        "name": "First Badge",
        "description": "Description of the first badge",
        "image_url": "/assets/images/badge1.png"
      }
    }
  ]
}
```

### 4. Award Badge to User
Award a badge to a user. Prevents duplicate awards.

**Endpoint:** `POST /badges/award`

**Headers:**
- `Content-Type: application/json`

**Body:**
```json
{
  "user_id": 123,
  "badge_id": 1
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 123,
    "badge_id": 1,
    "awarded_at": "2024-01-15T14:30:00Z"
  }
}
```

**Error Response (422 - Already has badge):**
```json
{
  "success": false,
  "message": "User already has this badge"
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Failed to award badge"
}
```

### 5. Revoke Badge from User
Remove a badge from a user.

**Endpoint:** `POST /badges/revoke`

**Headers:**
- `Content-Type: application/json`

**Body:**
```json
{
  "user_id": 123,
  "badge_id": 1
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Badge revoked successfully"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Failed to revoke badge"
}
```

## Admin Endpoints

The following endpoints are available for admin panel management (require admin authentication):

### Badge Management
- `GET /supabase/badges` - Get all badges
- `POST /supabase/badges` - Create new badge (with file upload)
- `GET /supabase/badges/{id}` - Get badge details
- `PUT /supabase/badges/{id}` - Update badge
- `DELETE /supabase/badges/{id}` - Delete badge

### User Badge Management
- `GET /supabase/user-badges` - Get all user badges
- `GET /supabase/user-badges/{userId}` - Get badges for specific user
- `POST /supabase/user-badges/award` - Award badge to user
- `POST /supabase/user-badges/revoke` - Revoke badge from user
- `GET /supabase/users-with-badges` - Get users with badge details

## Example Usage

### JavaScript/Node.js
```javascript
// Get all badges
const response = await fetch('https://your-domain.com/api/v1/badges');
const data = await response.json();
console.log(data.data); // Array of badges

// Award a badge
const awardResponse = await fetch('https://your-domain.com/api/v1/badges/award', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    user_id: 123,
    badge_id: 1
  })
});
const awardData = await awardResponse.json();
```

### PHP
```php
// Get all badges
$response = file_get_contents('https://your-domain.com/api/v1/badges');
$data = json_decode($response, true);
print_r($data['data']);

// Award a badge
$data = json_encode([
    'user_id' => 123,
    'badge_id' => 1
]);

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => $data
    ]
];

$context = stream_context_create($options);
$result = file_get_contents('https://your-domain.com/api/v1/badges/award', false, $context);
```

### Python
```python
import requests

# Get all badges
response = requests.get('https://your-domain.com/api/v1/badges')
data = response.json()
print(data['data'])

# Award a badge
award_data = {
    'user_id': 123,
    'badge_id': 1
}
response = requests.post('https://your-domain.com/api/v1/badges/award', json=award_data)
result = response.json()
```

## Error Codes
- `200` - Success
- `404` - Resource not found
- `422` - Validation error or business logic error (e.g., duplicate badge)
- `500` - Server error

## Notes
- All timestamps are in ISO 8601 format
- Images are served from `/assets/images/` directory
- Badge names should be unique
- User can only have one instance of each badge
- Badge images should be under 2MB and in formats: PNG, JPG, GIF, SVG