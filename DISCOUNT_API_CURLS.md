# Discount API - cURL Examples

## Base URL
Replace `YOUR_DOMAIN` with your actual domain (e.g., `http://localhost:8000` or `https://api.example.com`)

```
Base URL: YOUR_DOMAIN/api/vendor
```

## Authentication
All endpoints require Bearer token authentication. Get your token from the login endpoint:

```bash
# Login to get token
curl -X POST "YOUR_DOMAIN/api/vendor/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "phone": "1234567890",
    "password": "your_password"
  }'
```

**Note:** Replace `YOUR_TOKEN` in all requests below with the actual token from login response.

---

## 1. List All Discounts (Index)

### Basic Request
```bash
curl -X GET "YOUR_DOMAIN/api/vendor/discounts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### With Filters and Pagination
```bash
# Filter by status (active/archived) and title, with pagination
curl -X GET "YOUR_DOMAIN/api/vendor/discounts?status=active&title=Summer&per_page=15" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Available Query Parameters:
- `status`: Filter by status (`active` or `archived`)
- `title`: Search by title (partial match)
- `per_page`: Number of items per page (default: 15)

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Summer Sale",
      "percentage": "25.00",
      "vendor_id": 1,
      "is_archived": false,
      "archived_at": null,
      "created_at": "2025-11-04 09:30:00",
      "updated_at": "2025-11-04 09:30:00",
      "products": [
        {
          "id": 1,
          "name": "Product Name",
          ...
        }
      ]
    }
  ],
  "pagination": {
    "currentPage": 1,
    "total": 10,
    "perPage": 15,
    "lastPage": 1,
    "hasMorePages": false
  }
}
```

---

## 2. Create Discount (Store)

```bash
curl -X POST "YOUR_DOMAIN/api/vendor/discounts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Black Friday Sale",
    "percentage": 30.50,
    "product_ids": [1, 2, 3, 5]
  }'
```

**Request Body:**
```json
{
  "title": "Black Friday Sale",        // Required: string, max 255
  "percentage": 30.50,                 // Required: numeric, 0-100
  "product_ids": [1, 2, 3, 5]          // Required: array, min 1 product
}
```

**Example Response:**
```json
{
  "message": "Discount created successfully",
  "data": {
    "id": 1,
    "title": "Black Friday Sale",
    "percentage": "30.50",
    "vendor_id": 1,
    "is_archived": false,
    "archived_at": null,
    "created_at": "2025-11-04 09:30:00",
    "updated_at": "2025-11-04 09:30:00",
    "products": [...]
  }
}
```

---

## 3. Get Single Discount (Show)

```bash
curl -X GET "YOUR_DOMAIN/api/vendor/discounts/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "data": {
    "id": 1,
    "title": "Black Friday Sale",
    "percentage": "30.50",
    "vendor_id": 1,
    "is_archived": false,
    "archived_at": null,
    "created_at": "2025-11-04 09:30:00",
    "updated_at": "2025-11-04 09:30:00",
    "products": [...],
    "vendor": {
      "id": 1,
      "store_name": "My Store"
    }
  }
}
```

---

## 4. Update Discount

```bash
curl -X PUT "YOUR_DOMAIN/api/vendor/discounts/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Updated Black Friday Sale",
    "percentage": 35.00,
    "product_ids": [1, 2, 3, 4, 5]
  }'
```

**Alternative using PATCH:**
```bash
curl -X PATCH "YOUR_DOMAIN/api/vendor/discounts/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Updated Black Friday Sale",
    "percentage": 35.00,
    "product_ids": [1, 2, 3, 4, 5]
  }'
```

**Request Body:** (Same as Store)
```json
{
  "title": "Updated Black Friday Sale",  // Required: string, max 255
  "percentage": 35.00,                    // Required: numeric, 0-100
  "product_ids": [1, 2, 3, 4, 5]         // Required: array, min 1 product
}
```

**Example Response:**
```json
{
  "message": "Discount updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Black Friday Sale",
    "percentage": "35.00",
    ...
  }
}
```

---

## 5. Delete Discount

```bash
curl -X DELETE "YOUR_DOMAIN/api/vendor/discounts/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "message": "Discount deleted successfully"
}
```

---

## 6. Toggle Archive (Archive/Unarchive)

```bash
curl -X PATCH "YOUR_DOMAIN/api/vendor/discounts/1/toggle-archive" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Example Response (when archiving):**
```json
{
  "message": "Discount archived successfully",
  "data": {
    "id": 1,
    "title": "Black Friday Sale",
    "percentage": "30.50",
    "is_archived": true,
    "archived_at": "2025-11-04 10:00:00",
    ...
  }
}
```

**Example Response (when unarchiving):**
```json
{
  "message": "Discount unarchived successfully",
  "data": {
    "id": 1,
    "title": "Black Friday Sale",
    "percentage": "30.50",
    "is_archived": false,
    "archived_at": null,
    ...
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."],
    "percentage": ["The percentage must be at least 0."],
    "product_ids": ["At least one product is required."]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
  "message": "No query results for model [App\\Models\\Discount] 1"
}
```

---

## Complete Example Workflow

```bash
# 1. Login to get token
TOKEN=$(curl -s -X POST "YOUR_DOMAIN/api/vendor/login" \
  -H "Content-Type: application/json" \
  -d '{"phone":"1234567890","password":"your_password"}' \
  | jq -r '.token')

# 2. Create a discount
curl -X POST "YOUR_DOMAIN/api/vendor/discounts" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Winter Sale",
    "percentage": 20.00,
    "product_ids": [1, 2, 3]
  }'

# 3. List all discounts
curl -X GET "YOUR_DOMAIN/api/vendor/discounts" \
  -H "Authorization: Bearer $TOKEN"

# 4. Get specific discount (assuming ID is 1)
curl -X GET "YOUR_DOMAIN/api/vendor/discounts/1" \
  -H "Authorization: Bearer $TOKEN"

# 5. Update discount
curl -X PUT "YOUR_DOMAIN/api/vendor/discounts/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Winter Sale",
    "percentage": 25.00,
    "product_ids": [1, 2, 3, 4]
  }'

# 6. Archive discount
curl -X PATCH "YOUR_DOMAIN/api/vendor/discounts/1/toggle-archive" \
  -H "Authorization: Bearer $TOKEN"

# 7. List archived discounts
curl -X GET "YOUR_DOMAIN/api/vendor/discounts?status=archived" \
  -H "Authorization: Bearer $TOKEN"

# 8. Unarchive discount
curl -X PATCH "YOUR_DOMAIN/api/vendor/discounts/1/toggle-archive" \
  -H "Authorization: Bearer $TOKEN"

# 9. Delete discount
curl -X DELETE "YOUR_DOMAIN/api/vendor/discounts/1" \
  -H "Authorization: Bearer $TOKEN"
```

---

## Notes

1. **Authentication**: All endpoints require Bearer token in the Authorization header
2. **Content-Type**: Use `application/json` for POST/PUT/PATCH requests
3. **Accept**: Include `Accept: application/json` header for JSON responses
4. **Product IDs**: Make sure the product IDs exist in your database before creating/updating discounts
5. **Percentage**: Must be between 0 and 100 (can be decimal like 25.50)
6. **Archive**: Use the toggle-archive endpoint to archive/unarchive discounts
7. **Filtering**: Use `status=active` for non-archived discounts, `status=archived` for archived ones

