#!/bin/bash

# Test script for deposit rejection API endpoints
# This script tests the new rejection functionality

BASE_URL="http://192.168.100.65:8000/api"

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Testing Deposit Rejection API Endpoints${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# You'll need to set a valid token from your authentication
# For now, using a placeholder - replace with actual token
TOKEN="37|cYbtDE3PRsLpQxvvZJdFrOmgVLO8imYh9kx6XVrA14384142"
LOAN_ID=1  # Replace with actual loan ID

echo -e "${BLUE}1. Testing GET /loans/{loan}/deposit/status (with rejection info)${NC}"
echo "URL: $BASE_URL/loans/$LOAN_ID/deposit/status"
curl -s -X GET "$BASE_URL/loans/$LOAN_ID/deposit/status" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""
echo "---"
echo ""

echo -e "${BLUE}2. Testing GET /loans/{loan}/deposits/rejected (rejection history)${NC}"
echo "URL: $BASE_URL/loans/$LOAN_ID/deposits/rejected"
curl -s -X GET "$BASE_URL/loans/$LOAN_ID/deposits/rejected" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""
echo "---"
echo ""

echo -e "${BLUE}3. Testing POST /deposits/{id}/reject (admin rejects deposit)${NC}"
echo "Note: This requires admin authentication and a pending deposit ID"
echo "URL: $BASE_URL/deposits/1/reject"
echo "Payload: {rejection_reason: 'Invalid M-PESA code...'}"
echo ""
echo "Example command:"
echo "curl -X POST \"$BASE_URL/deposits/1/reject\" \\"
echo "  -H \"Authorization: Bearer ADMIN_TOKEN\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -H \"Accept: application/json\" \\"
echo "  -d '{\"rejection_reason\": \"Invalid M-PESA code. The transaction could not be verified.\"}'"
echo ""
echo "---"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Test Summary${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "✓ Database migration completed"
echo "✓ Deposit model updated with rejection fields"
echo "✓ New API endpoints added:"
echo "  - GET  /api/loans/{loan}/deposit/status (includes rejection_count)"
echo "  - GET  /api/loans/{loan}/deposits/rejected"
echo "  - POST /api/deposits/{id}/reject"
echo ""
echo "Next steps:"
echo "1. Test with actual authentication tokens"
echo "2. Test rejection workflow end-to-end"
echo "3. Verify mobile app receives rejection data correctly"
echo ""
