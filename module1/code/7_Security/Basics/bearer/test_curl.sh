#!/bin/bash

# Bearer Token Authentication - Automated Test Script
# Logs in, captures the token from the response, and feeds it into every
# following request automatically. Each response is checked against an
# expected HTTP status and message - no manual reading required.

set -u

RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

BASE_URL="http://localhost:8080"
PASS_COUNT=0
FAIL_COUNT=0

# Runs a curl request, then checks both the HTTP status and a substring
# in the response body. Prints PASS/FAIL instead of leaving it to the reader.
run_test() {
    local description="$1"
    local expected_status="$2"
    local expected_substring="$3"
    shift 3

    local response
    response=$(curl -s -w "\n%{http_code}" "$@")
    local status="${response##*$'\n'}"
    local body="${response%$'\n'*}"

    echo -e "${BLUE}${description}${NC}"
    echo "  Response: $body"
    echo "  Status:   $status"

    if [[ "$status" == "$expected_status" && "$body" == *"$expected_substring"* ]]; then
        echo -e "  ${GREEN}PASS${NC}"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        echo -e "  ${RED}FAIL${NC} (expected status $expected_status, body containing \"$expected_substring\")"
        FAIL_COUNT=$((FAIL_COUNT + 1))
    fi
    echo ""
}

echo "Bearer Token Authentication - Automated Tests"
echo "=============================================="
echo ""

echo -e "${BLUE}Step 1: Login and capture the token automatically${NC}"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/login.php" \
     -H "Content-Type: application/json" \
     -d '{"username":"john","password":"Secret123"}')
echo "  Response: $LOGIN_RESPONSE"

TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

if [[ -n "$TOKEN" ]]; then
    echo -e "  ${GREEN}PASS${NC} (captured token: $TOKEN)"
    PASS_COUNT=$((PASS_COUNT + 1))
else
    echo -e "  ${RED}FAIL${NC} (no token in login response)"
    FAIL_COUNT=$((FAIL_COUNT + 1))
fi
echo ""

echo -e "${BLUE}Step 2: Access protected API with the captured token${NC}"
run_test "  Valid token" \
    200 '"authenticated_user":"john"' \
    -H "Authorization: Bearer $TOKEN" "$BASE_URL/protected_api.php"

echo -e "${BLUE}Step 3: Access protected API with an invalid token${NC}"
run_test "  Invalid token" \
    401 "Invalid or expired token" \
    -H "Authorization: Bearer not-a-real-token" "$BASE_URL/protected_api.php"

echo -e "${BLUE}Step 4: Access protected API with no token at all${NC}"
run_test "  Missing token" \
    401 "Bearer token required" \
    "$BASE_URL/protected_api.php"

echo "=============================================="
echo -e "Results: ${GREEN}${PASS_COUNT} passed${NC}, ${RED}${FAIL_COUNT} failed${NC}"
echo ""

echo "Valid test accounts:"
echo "- john  / Secret123"
echo "- admin / Admin123"

if [[ $FAIL_COUNT -gt 0 ]]; then
    exit 1
fi
