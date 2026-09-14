#!/bin/bash

#####################################################################
# Simple REST API Tests using CURL for Student Management System
# 
# This script demonstrates how to test REST APIs using CURL
# Educational purpose - shows basic API testing patterns
# 
# Make sure your PHP API server is running on http://localhost:8080
# FIXED VERSION: Compatible with macOS head command
#####################################################################

API_BASE_URL="${API_BASE_URL:-http://localhost:8080}"
VERBOSE=false

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Check command line arguments
if [[ "$1" == "--verbose" ]]; then
    VERBOSE=true
fi

# Helper function to extract JSON from curl response (macOS compatible)
extract_json_data() {
    local response="$1"
    # Use sed to remove the last line instead of head -n -1
    echo "$response" | sed '$d'
}

# Helper function to extract HTTP code from curl response
extract_http_code() {
    local response="$1"
    echo "$response" | tail -n1
}

# Helper function to print test results
print_result() {
    local test_name="$1"
    local success="$2"
    local message="$3"
    local data="$4"
    
    if [[ "$success" == "true" ]]; then
        echo -e "${GREEN}✅ $test_name: $message${NC}"
    else
        echo -e "${RED}❌ $test_name: $message${NC}"
    fi
    
    if [[ "$VERBOSE" == "true" && -n "$data" ]]; then
        echo -e "${CYAN}   Response: $data${NC}"
    fi
}

# Helper function to make CURL requests
make_curl_request() {
    local url="$1"
    local method="$2"
    local data="$3"
    local content_type="application/json"
    
    if [[ -n "$data" ]]; then
        curl -s -w "\n%{http_code}" -X "$method" \
             -H "Content-Type: $content_type" \
             -d "$data" \
             "$url"
    else
        curl -s -w "\n%{http_code}" -X "$method" "$url"
    fi
}

# Test 1.1: Server Connection
test_server_connection() {
    echo -e "${BLUE}Testing server connection...${NC}"
    
    local response=$(make_curl_request "$API_BASE_URL" "GET")
    local http_code=$(extract_http_code "$response")
    local json_data=$(extract_json_data "$response")
    
    if [[ "$http_code" == "200" ]]; then
        print_result "Server Connection" "true" "Server is responding correctly!" "$json_data"
        return 0
    else
        print_result "Server Connection" "false" "Server returned status $http_code" "$json_data"
        return 1
    fi
}

# Test 1.2: API Root Endpoint
test_api_root() {
    echo -e "${BLUE}Testing API root endpoint...${NC}"
    
    local response=$(make_curl_request "$API_BASE_URL" "GET")
    local http_code=$(extract_http_code "$response")
    local json_data=$(extract_json_data "$response")
    
    if [[ "$http_code" == "200" ]]; then
        # Check if response contains expected fields
        if echo "$json_data" | grep -q '"message"' && echo "$json_data" | grep -q '"endpoints"'; then
            print_result "API Root" "true" "API root returns expected structure!" "$json_data"
            return 0
        else
            print_result "API Root" "false" "API root missing expected fields" "$json_data"
            return 1
        fi
    else
        print_result "API Root" "false" "API root returned status $http_code" "$json_data"
        return 1
    fi
}

# Test 2.1: Get All Students
test_get_all_students() {
    echo -e "${BLUE}Testing GET /students...${NC}"
    
    local response=$(make_curl_request "$API_BASE_URL/students" "GET")
    local http_code=$(extract_http_code "$response")
    local json_data=$(extract_json_data "$response")
    
    if [[ "$http_code" == "200" ]]; then
        # Basic validation - check for success field and data array
        local tests_passed=0
        local total_tests=3
        
        # Test 1: Has success field
        if echo "$json_data" | grep -q '"success":true'; then
            ((tests_passed++))
        fi
        
        # Test 2: Has data array
        if echo "$json_data" | grep -q '"data":\['; then
            ((tests_passed++))
        fi
        
        # Test 3: Has count field
        if echo "$json_data" | grep -q '"count":[0-9][0-9]*'; then
            ((tests_passed++))
        fi
        
        if [[ $tests_passed -eq $total_tests ]]; then
            print_result "Get All Students" "true" "All tests passed ($tests_passed/$total_tests)" "$json_data"
            return 0
        else
            print_result "Get All Students" "false" "Partial: $tests_passed/$total_tests tests passed" "$json_data"
            return 1
        fi
    else
        print_result "Get All Students" "false" "Unexpected response status $http_code" "$json_data"
        return 1
    fi
}

# Test 2.2: Get Single Student
test_get_single_student() {
    local student_id="${1:-$CREATED_STUDENT_ID}"
    if [[ -z "$student_id" ]]; then
        student_id=1
    fi
    echo -e "${BLUE}Testing GET /students/$student_id...${NC}"
    
    local response=$(make_curl_request "$API_BASE_URL/students/$student_id" "GET")
    local http_code=$(extract_http_code "$response")
    local json_data=$(extract_json_data "$response")
    
    if [[ "$http_code" == "200" ]]; then
        local tests_passed=0
        local total_tests=4
        
        # Test 1: Has success field
        if echo "$json_data" | grep -q '"success":true'; then
            ((tests_passed++))
        fi
        
        # Test 2: Student has matching ID
        if echo "$json_data" | grep -q "\"id\":$student_id"; then
            ((tests_passed++))
        fi
        
        # Test 3: Student has name
        if echo "$json_data" | grep -q '"name":"[^"]*"'; then
            ((tests_passed++))
        fi
        
        # Test 4: Student has email with @
        if echo "$json_data" | grep -q '"email":"[^"]*@[^"]*"'; then
            ((tests_passed++))
        fi
        
        if [[ $tests_passed -eq $total_tests ]]; then
            print_result "Get Single Student" "true" "All tests passed ($tests_passed/$total_tests)" "$json_data"
            return 0
        else
            print_result "Get Single Student" "false" "Partial: $tests_passed/$total_tests tests passed" "$json_data"
            return 1
        fi
    else
        print_result "Get Single Student" "false" "Unexpected response status $http_code" "$json_data"
        return 1
    fi
}

# Test 2.3: Create New Student
test_create_student() {
    echo -e "${BLUE}Testing POST /students...${NC}"
    
    local new_student_json='{
        "name": "Test Student CURL",
        "email": "test.curl@university.edu",
        "major": "Software Engineering",
        "year": 1
    }'
    
    local response=$(make_curl_request "$API_BASE_URL/students" "POST" "$new_student_json")
    local http_code=$(extract_http_code "$response")
    local json_data=$(extract_json_data "$response")
    
    if [[ "$http_code" == "201" ]]; then
        local tests_passed=0
        local total_tests=5
        
        # Test 1: Success field
        if echo "$json_data" | grep -q '"success":true'; then
            ((tests_passed++))
        fi
        
        # Test 2: Name matches
        if echo "$json_data" | grep -q '"name":"Test Student CURL"'; then
            ((tests_passed++))
        fi
        
        # Test 3: Email matches
        if echo "$json_data" | grep -q '"email":"test.curl@university.edu"'; then
            ((tests_passed++))
        fi
        
        # Test 4: Has new ID
        if echo "$json_data" | grep -q '"id":[0-9][0-9]*'; then
            ((tests_passed++))
            # Extract the ID for later use
            CREATED_STUDENT_ID=$(echo "$json_data" | grep -o '"id":[0-9][0-9]*' | grep -o '[0-9][0-9]*')
        fi
        
        # Test 5: Has timestamps
        if echo "$json_data" | grep -q '"created_at"' && echo "$json_data" | grep -q '"updated_at"'; then
            ((tests_passed++))
        fi
        
        if [[ $tests_passed -eq $total_tests ]]; then
            print_result "Create Student" "true" "Created student with ID: $CREATED_STUDENT_ID" "$json_data"
            return 0
        else
            print_result "Create Student" "false" "Partial: $tests_passed/$total_tests tests passed" "$json_data"
            return 1
        fi
    else
        print_result "Create Student" "false" "Unexpected response status $http_code" "$json_data"
        return 1
    fi
}

# Test 2.4: Update Student
test_update_student() {
    local student_id="${1:-$CREATED_STUDENT_ID}"
    
    if [[ -z "$student_id" ]]; then
        print_result "Update Student" "false" "No student ID available for update test"
        return 1
    fi
    
    echo -e "${BLUE}Testing PUT /students/$student_id...${NC}"
    
    local update_json='{
        "name": "Updated Test Student CURL",
        "email": "test.curl.updated@university.edu",
        "major": "Data Science",
        "year": 2
    }'
    
    local response=$(make_curl_request "$API_BASE_URL/students/$student_id" "PUT" "$update_json")
    local http_code=$(extract_http_code "$response")
    local json_data=$(extract_json_data "$response")
    
    if [[ "$http_code" == "200" ]]; then
        local tests_passed=0
        local total_tests=3
        
        # Test 1: Success field
        if echo "$json_data" | grep -q '"success":true'; then
            ((tests_passed++))
        fi
        
        # Test 2: Name was updated
        if echo "$json_data" | grep -q '"name":"Updated Test Student CURL"'; then
            ((tests_passed++))
        fi
        
        # Test 3: Major was updated
        if echo "$json_data" | grep -q '"major":"Data Science"'; then
            ((tests_passed++))
        fi
        
        if [[ $tests_passed -eq $total_tests ]]; then
            print_result "Update Student" "true" "All tests passed ($tests_passed/$total_tests)" "$json_data"
            return 0
        else
            print_result "Update Student" "false" "Partial: $tests_passed/$total_tests tests passed" "$json_data"
            return 1
        fi
    else
        print_result "Update Student" "false" "Unexpected response status $http_code" "$json_data"
        return 1
    fi
}

# Test 2.5: Delete Student
test_delete_student() {
    local student_id="${1:-$CREATED_STUDENT_ID}"
    
    if [[ -z "$student_id" ]]; then
        print_result "Delete Student" "false" "No student ID available for delete test"
        return 1
    fi
    
    echo -e "${BLUE}Testing DELETE /students/$student_id...${NC}"
    
    local response=$(make_curl_request "$API_BASE_URL/students/$student_id" "DELETE")
    local http_code=$(extract_http_code "$response")
    local json_data=$(extract_json_data "$response")
    
    if [[ "$http_code" == "200" ]]; then
        # Verify deletion by trying to get the student
        local verify_response=$(make_curl_request "$API_BASE_URL/students/$student_id" "GET")
        local verify_code=$(extract_http_code "$verify_response")
        
        if [[ "$verify_code" == "404" ]]; then
            print_result "Delete Student" "true" "Student $student_id deleted successfully and verified!" "$json_data"
            return 0
        else
            print_result "Delete Student" "false" "Student still exists after deletion (status: $verify_code)"
            return 1
        fi
    else
        print_result "Delete Student" "false" "Unexpected response status $http_code" "$json_data"
        return 1
    fi
}

# --- Failure-case tests ---

# Test 3.1: Unknown Resource
test_unknown_resource() {
    echo -e "${BLUE}Testing GET /teachers (unknown resource)...${NC}"

    local response=$(make_curl_request "$API_BASE_URL/teachers" "GET")
    local http_code=$(extract_http_code "$response")
    local json_data=$(extract_json_data "$response")

    if [[ "$http_code" == "404" ]]; then
        print_result "Unknown Resource" "true" "Correctly returned 404" "$json_data"
        return 0
    else
        print_result "Unknown Resource" "false" "Expected 404, got $http_code" "$json_data"
        return 1
    fi
}

# Test 3.2: Invalid (non-numeric) ID
test_invalid_id() {
    echo -e "${BLUE}Testing GET /students/abc (invalid id)...${NC}"

    local response=$(make_curl_request "$API_BASE_URL/students/abc" "GET")
    local http_code=$(extract_http_code "$response")
    local json_data=$(extract_json_data "$response")

    if [[ "$http_code" == "400" ]]; then
        print_result "Invalid ID" "true" "Correctly returned 400" "$json_data"
        return 0
    else
        print_result "Invalid ID" "false" "Expected 400, got $http_code" "$json_data"
        return 1
    fi
}

# Test 3.3: Missing Required Fields
test_missing_fields() {
    echo -e "${BLUE}Testing POST /students with {} (missing fields)...${NC}"

    local response=$(make_curl_request "$API_BASE_URL/students" "POST" '{}')
    local http_code=$(extract_http_code "$response")
    local json_data=$(extract_json_data "$response")

    if [[ "$http_code" == "400" ]] && \
       echo "$json_data" | grep -q '"success":false' && \
       echo "$json_data" | grep -q '"error":'; then
        print_result "Missing Fields" "true" "Correctly returned 400" "$json_data"
        return 0
    else
        print_result "Missing Fields" "false" "Expected 400, got $http_code" "$json_data"
        return 1
    fi
}

# Shared check for invalid request bodies: status and error response shape
test_bad_request_body() {
    local test_name="$1"
    local method="$2"
    local path="$3"
    local data="$4"

    echo -e "${BLUE}Testing $test_name...${NC}"
    local response=$(make_curl_request "$API_BASE_URL$path" "$method" "$data")
    local http_code=$(extract_http_code "$response")
    local json_data=$(extract_json_data "$response")

    if [[ "$http_code" == "400" ]] && \
       echo "$json_data" | grep -q '"success":false' && \
       echo "$json_data" | grep -q '"error":'; then
        print_result "$test_name" "true" "Got 400 with an error response" "$json_data"
        return 0
    else
        print_result "$test_name" "false" "Expected 400 with success:false and an error message, got $http_code" "$json_data"
        return 1
    fi
}

test_malformed_json() {
    test_bad_request_body "Malformed JSON" "POST" "/students" '{bad json'
}

test_array_body() {
    test_bad_request_body "Array Body" "POST" "/students" '[1,2]'
}

test_invalid_field_values() {
    test_bad_request_body "Invalid Field Values" "POST" "/students" '{"name":" ","email":"bad"}'
}

test_incomplete_put() {
    test_bad_request_body "Incomplete PUT" "PUT" "/students/999999" '{"name":"Test","email":"test@example.com"}'
}

# Helper: request capturing full headers (for Allow header checks)
make_curl_request_with_headers() {
    local url="$1"
    local method="$2"
    curl -s -i -X "$method" "$url"
}

# Test 3.4: Method Not Allowed on an item path (expects an Allow header)
test_method_not_allowed_item() {
    echo -e "${BLUE}Testing POST /students/1 (method not allowed)...${NC}"

    local raw=$(make_curl_request_with_headers "$API_BASE_URL/students/1" "POST")
    local http_code=$(echo "$raw" | head -n 1 | grep -o '[0-9][0-9][0-9]')
    local allow_value=$(echo "$raw" | awk -F': ' 'tolower($1) == "allow" {gsub("\\r", "", $2); print $2}')

    if [[ "$http_code" == "405" && "$allow_value" == "GET, PUT, DELETE" ]]; then
        print_result "Method Not Allowed (item)" "true" "Got 405 with Allow: $allow_value"
        return 0
    else
        print_result "Method Not Allowed (item)" "false" "Expected 405 + Allow header, got $http_code"
        return 1
    fi
}

# Test 3.5: Method Not Allowed on the collection path
test_method_not_allowed_collection() {
    echo -e "${BLUE}Testing PUT /students (method not allowed)...${NC}"

    local raw=$(make_curl_request_with_headers "$API_BASE_URL/students" "PUT")
    local http_code=$(echo "$raw" | head -n 1 | grep -o '[0-9][0-9][0-9]')
    local allow_value=$(echo "$raw" | awk -F': ' 'tolower($1) == "allow" {gsub("\\r", "", $2); print $2}')

    if [[ "$http_code" == "405" && "$allow_value" == "GET, POST" ]]; then
        print_result "Method Not Allowed (collection)" "true" "Got 405 with Allow: $allow_value"
        return 0
    else
        print_result "Method Not Allowed (collection)" "false" "Expected 405 + Allow header, got $http_code"
        return 1
    fi
}

# Check if CURL is available
check_curl() {
    if ! command -v curl &> /dev/null; then
        echo -e "${RED}ERROR: curl is not installed or not in PATH${NC}"
        echo "Please install curl to run these tests"
        echo ""
        echo "Installation:"
        echo "  Ubuntu/Debian: sudo apt-get install curl"
        echo "  CentOS/RHEL:   sudo yum install curl"
        echo "  MacOS:         curl is usually pre-installed"
        echo "  Windows:       Download from https://curl.se/windows/"
        exit 1
    else
        echo -e "${GREEN}curl is installed at:$(command -v curl)${NC}"
    fi
}

# Main test runner
run_all_tests() {
    echo -e "${MAGENTA}🚀 Starting REST API Tests using CURL${NC}"
    echo -e "${CYAN}API Base URL: $API_BASE_URL${NC}"
    echo ""
    
    local passed_tests=0
    local total_tests=0
    
    # Test 1: Server connection
    ((total_tests++))
    if test_server_connection; then
        ((passed_tests++))
    fi
    echo ""
    
    # Test 2: API root
    ((total_tests++))
    if test_api_root; then
        ((passed_tests++))
    fi
    echo ""
    
    # Test 3: Get all students
    ((total_tests++))
    if test_get_all_students; then
        ((passed_tests++))
    fi
    echo ""
    
    # Test 4: Create student (capture ID for subsequent tests)
    ((total_tests++))
    if test_create_student; then
        ((passed_tests++))
    fi
    echo ""
    
    # Tests 5-7: Read, update, and delete the created student
    if [[ -n "$CREATED_STUDENT_ID" ]]; then
        # Test 5: Get single student (using captured ID)
        ((total_tests++))
        if test_get_single_student "$CREATED_STUDENT_ID"; then
            ((passed_tests++))
        fi
        echo ""

        # Test 6: Update student (using captured ID)
        ((total_tests++))
        if test_update_student "$CREATED_STUDENT_ID"; then
            ((passed_tests++))
        fi
        echo ""
        
        # Test 7: Delete student (using captured ID)
        ((total_tests++))
        if test_delete_student "$CREATED_STUDENT_ID"; then
            ((passed_tests++))
        fi
        echo ""
    fi
    
    # Tests 8-16: Failure cases (routing, input, and method validation)
    ((total_tests++))
    if test_unknown_resource; then
        ((passed_tests++))
    fi
    echo ""

    ((total_tests++))
    if test_invalid_id; then
        ((passed_tests++))
    fi
    echo ""

    ((total_tests++))
    if test_missing_fields; then
        ((passed_tests++))
    fi
    echo ""

    ((total_tests++))
    if test_malformed_json; then
        ((passed_tests++))
    fi
    echo ""

    ((total_tests++))
    if test_array_body; then
        ((passed_tests++))
    fi
    echo ""

    ((total_tests++))
    if test_invalid_field_values; then
        ((passed_tests++))
    fi
    echo ""

    ((total_tests++))
    if test_incomplete_put; then
        ((passed_tests++))
    fi
    echo ""

    ((total_tests++))
    if test_method_not_allowed_item; then
        ((passed_tests++))
    fi
    echo ""

    ((total_tests++))
    if test_method_not_allowed_collection; then
        ((passed_tests++))
    fi
    echo ""
    
    # Summary
    echo -e "${MAGENTA}📊 Test Summary${NC}"
    echo -e "${CYAN}Total Tests: $total_tests${NC}"
    echo -e "${GREEN}Passed: $passed_tests${NC}"
    echo -e "${RED}Failed: $((total_tests - passed_tests))${NC}"
    
    if [[ $passed_tests -eq $total_tests ]]; then
        echo -e "${GREEN}🎉 All tests passed!${NC}"
        exit 0
    else
        echo -e "${YELLOW}⚠️  Some tests failed. Check the output above.${NC}"
        exit 1
    fi
}

# Script entry point
echo -e "${CYAN}Simple REST API Testing Tool - CURL Version (macOS Compatible)${NC}"
echo ""

# Check prerequisites
check_curl

# Run the tests
run_all_tests
