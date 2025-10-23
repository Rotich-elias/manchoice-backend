#!/bin/bash

echo "=== Testing Guarantor Motorcycle Fields ==="
echo ""

# Test data
TOKEN="1|test_token"  # You'll need to replace with real token

echo "1. Creating a customer with guarantor motorcycle details..."
RESPONSE=$(curl -s -X POST http://192.168.100.65:8000/api/customers \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Test Customer",
    "phone": "0700000099",
    "email": "test@example.com",
    "guarantor_name": "Test Guarantor",
    "guarantor_phone": "0700000098",
    "guarantor_relationship": "Fellow Stage Member",
    "guarantor_motorcycle_number_plate": "KCA 123X",
    "guarantor_motorcycle_chassis_number": "CHASSIS123",
    "guarantor_motorcycle_model": "Boxer BM150",
    "guarantor_motorcycle_type": "Sport",
    "guarantor_motorcycle_engine_cc": "150",
    "guarantor_motorcycle_colour": "Red"
  }')

echo "$RESPONSE" | jq '.'
CUSTOMER_ID=$(echo "$RESPONSE" | jq -r '.data.id')

echo ""
echo "2. Fetching customer data back..."
curl -s -X GET "http://192.168.100.65:8000/api/customers/$CUSTOMER_ID" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | jq '.data | {
    guarantor_name,
    guarantor_motorcycle_number_plate,
    guarantor_motorcycle_chassis_number,
    guarantor_motorcycle_model,
    guarantor_motorcycle_type,
    guarantor_motorcycle_engine_cc,
    guarantor_motorcycle_colour
  }'

echo ""
echo "=== Test Complete ==="
