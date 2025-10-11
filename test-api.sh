#!/bin/bash
# Quick API Test Script

echo "=== MAN'S CHOICE API TEST ==="
echo ""

# 1. Login
echo "1. Logging in..."
LOGIN_RESPONSE=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@manschoice.com",
    "password": "password123"
  }')

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "❌ Login failed!"
    echo "$LOGIN_RESPONSE"
    exit 1
fi

echo "✅ Login successful!"
echo "Token: ${TOKEN:0:30}..."
echo ""

# 2. Get current user
echo "2. Getting current user..."
curl -s -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/user | python3 -m json.tool
echo ""

# 3. Get all customers
echo "3. Getting all customers..."
curl -s -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/customers | python3 -m json.tool
echo ""

# 4. Get all loans
echo "4. Getting all loans..."
curl -s -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/loans | python3 -m json.tool
echo ""

echo "=== TEST COMPLETE ==="
echo ""
echo "Your API is working! 🎉"
echo ""
echo "To use the API:"
echo "  Base URL: http://localhost:8000/api"
echo "  Token: Bearer $TOKEN"
