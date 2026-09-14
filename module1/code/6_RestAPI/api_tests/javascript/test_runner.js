#!/usr/bin/env node
/**
 * Simple REST API Tests for Student Management System
 * 
 * This script demonstrates how to test REST APIs using Node.js
 * Run with: node test_runner.js
 * 
 * Make sure your PHP API server is running on http://localhost:8080
 */

// Simple HTTP client using Node.js built-in modules
const http = require('http');
const https = require('https');

// Configuration
const API_BASE_URL = 'http://localhost:8080';
const COLORS = {
    RESET: '\x1b[0m',
    RED: '\x1b[31m',
    GREEN: '\x1b[32m',
    YELLOW: '\x1b[33m',
    BLUE: '\x1b[34m',
    MAGENTA: '\x1b[35m',
    CYAN: '\x1b[36m'
};

// Helper function to make HTTP requests
function makeRequest(url, method = 'GET', data = null, rawData = false) {
    return new Promise((resolve, reject) => {
        const urlObj = new URL(url);
        const options = {
            hostname: urlObj.hostname,
            port: urlObj.port,
            path: urlObj.pathname + urlObj.search,
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        let requestBody = null;
        if (data !== null) {
            requestBody = rawData ? String(data) : JSON.stringify(data);
            options.headers['Content-Length'] = Buffer.byteLength(requestBody);
        }

        const client = urlObj.protocol === 'https:' ? https : http;
        const req = client.request(options, (res) => {
            let responseData = '';

            res.on('data', (chunk) => {
                responseData += chunk;
            });

            res.on('end', () => {
                try {
                    const parsedData = JSON.parse(responseData);
                    resolve({
                        status: res.statusCode,
                        data: parsedData,
                        headers: res.headers,
                        ok: res.statusCode >= 200 && res.statusCode < 300
                    });
                } catch (error) {
                    resolve({
                        status: res.statusCode,
                        data: responseData,
                        headers: res.headers,
                        ok: false,
                        error: 'Invalid JSON response'
                    });
                }
            });
        });

        req.on('error', (error) => {
            reject({
                status: 0,
                error: error.message,
                ok: false
            });
        });

        if (requestBody !== null) {
            req.write(requestBody);
        }

        req.end();
    });
}

// Helper function to print colored output
function printResult(testName, success, message, data = null) {
    const color = success ? COLORS.GREEN : COLORS.RED;
    const symbol = success ? '✅' : '❌';
    
    console.log(`${color}${symbol} ${testName}: ${message}${COLORS.RESET}`);
    
    if (data && process.argv.includes('--verbose')) {
        console.log(`${COLORS.CYAN}   Data: ${JSON.stringify(data, null, 2)}${COLORS.RESET}`);
    }
}

// Test functions
async function testServerConnection() {
    console.log(`${COLORS.BLUE}Testing server connection...${COLORS.RESET}`);
    
    try {
        const result = await makeRequest(API_BASE_URL);
        
        if (result.ok) {
            printResult('Server Connection', true, 'Server is responding correctly!', result.data);
            return true;
        } else {
            printResult('Server Connection', false, `Server returned status ${result.status}`, result.data);
            return false;
        }
    } catch (error) {
        printResult('Server Connection', false, `Connection failed: ${error.error}`, error);
        return false;
    }
}

async function testGetAllStudents() {
    console.log(`${COLORS.BLUE}Testing GET /students...${COLORS.RESET}`);
    
    try {
        const result = await makeRequest(`${API_BASE_URL}/students`);
        
        if (result.ok && result.data.success && Array.isArray(result.data.data)) {
            const students = result.data.data;
            const countMatches = result.data.count === students.length;
            const hasValidStructure = students.length === 0 || 
                (students[0].id && students[0].name && students[0].email);
            
            if (countMatches && hasValidStructure) {
                printResult('Get All Students', true, 
                    `Retrieved ${students.length} students with valid structure`, result.data);
                return true;
            } else {
                printResult('Get All Students', false, 
                    'Response structure validation failed', result.data);
                return false;
            }
        } else {
            printResult('Get All Students', false, 'Unexpected response format', result.data);
            return false;
        }
    } catch (error) {
        printResult('Get All Students', false, `Request failed: ${error.error}`, error);
        return false;
    }
}

async function testGetSingleStudent(studentId = 1) {
    console.log(`${COLORS.BLUE}Testing GET /students/${studentId}...${COLORS.RESET}`);
    
    try {
        const result = await makeRequest(`${API_BASE_URL}/students/${studentId}`);
        
        if (result.ok && result.data.success && result.data.data) {
            const student = result.data.data;
            const hasRequiredFields = student.id === studentId && 
                student.name && 
                student.email && 
                student.major && 
                typeof student.year === 'number';
            
            if (hasRequiredFields) {
                printResult('Get Single Student', true, 
                    `Retrieved student: ${student.name}`, result.data);
                return true;
            } else {
                printResult('Get Single Student', false, 
                    'Student missing required fields', result.data);
                return false;
            }
        } else {
            printResult('Get Single Student', false, 
                'Could not retrieve student', result.data);
            return false;
        }
    } catch (error) {
        printResult('Get Single Student', false, `Request failed: ${error.error}`, error);
        return false;
    }
}

async function testCreateStudent() {
    console.log(`${COLORS.BLUE}Testing POST /students...${COLORS.RESET}`);
    
    const newStudent = {
        name: 'Test Student CLI',
        email: 'test.cli@university.edu',
        major: 'Software Engineering',
        year: 1
    };
    
    try {
        const result = await makeRequest(`${API_BASE_URL}/students`, 'POST', newStudent);
        
        if (result.status === 201 && result.data.success && result.data.data) {
            const created = result.data.data;
            const isValid = created.name === newStudent.name && 
                created.email === newStudent.email &&
                created.major === newStudent.major &&
                created.year === newStudent.year &&
                created.id > 0;
            
            if (isValid) {
                printResult('Create Student', true, 
                    `Created student with ID: ${created.id}`, result.data);
                return created.id;
            } else {
                printResult('Create Student', false, 
                    'Created student has incorrect data', result.data);
                return false;
            }
        } else {
            printResult('Create Student', false, 
                'Student creation failed', result.data);
            return false;
        }
    } catch (error) {
        printResult('Create Student', false, `Request failed: ${error.error}`, error);
        return false;
    }
}

async function testUpdateStudent(studentId) {
    console.log(`${COLORS.BLUE}Testing PUT /students/${studentId}...${COLORS.RESET}`);
    
    const updateData = {
        name: 'Updated Test Student',
        email: 'test.cli.updated@university.edu',
        major: 'Data Science',
        year: 2
    };
    
    try {
        const result = await makeRequest(`${API_BASE_URL}/students/${studentId}`, 'PUT', updateData);
        
        if (result.ok && result.data.success && result.data.data) {
            const updated = result.data.data;
            const isValid = updated.name === updateData.name && 
                updated.email === updateData.email &&
                updated.major === updateData.major &&
                updated.year === updateData.year;
            
            if (isValid) {
                printResult('Update Student', true, 
                    `Updated student: ${updated.name}`, result.data);
                return true;
            } else {
                printResult('Update Student', false, 
                    'Update data validation failed', result.data);
                return false;
            }
        } else {
            printResult('Update Student', false, 
                'Student update failed', result.data);
            return false;
        }
    } catch (error) {
        printResult('Update Student', false, `Request failed: ${error.error}`, error);
        return false;
    }
}

async function testDeleteStudent(studentId) {
    console.log(`${COLORS.BLUE}Testing DELETE /students/${studentId}...${COLORS.RESET}`);
    
    try {
        const result = await makeRequest(`${API_BASE_URL}/students/${studentId}`, 'DELETE');
        
        if (result.ok && result.data.success) {
            // Verify deletion by trying to get the student
            const verifyResult = await makeRequest(`${API_BASE_URL}/students/${studentId}`);
            
            if (verifyResult.status === 404) {
                printResult('Delete Student', true, 
                    `Student ${studentId} deleted successfully`, result.data);
                return true;
            } else {
                printResult('Delete Student', false, 
                    'Student still exists after deletion', verifyResult.data);
                return false;
            }
        } else {
            printResult('Delete Student', false, 
                'Student deletion failed', result.data);
            return false;
        }
    } catch (error) {
        printResult('Delete Student', false, `Request failed: ${error.error}`, error);
        return false;
    }
}

// --- Failure-case tests ---

async function testUnknownResource() {
    console.log(`${COLORS.BLUE}Testing GET /teachers (unknown resource)...${COLORS.RESET}`);

    try {
        const result = await makeRequest(`${API_BASE_URL}/teachers`);
        if (result.status === 404) {
            printResult('Unknown Resource', true, 'Correctly returned 404', result.data);
            return true;
        }
        printResult('Unknown Resource', false, `Expected 404, got ${result.status}`, result.data);
        return false;
    } catch (error) {
        printResult('Unknown Resource', false, `Request failed: ${error.error}`, error);
        return false;
    }
}

async function testInvalidId() {
    console.log(`${COLORS.BLUE}Testing GET /students/abc (invalid id)...${COLORS.RESET}`);

    try {
        const result = await makeRequest(`${API_BASE_URL}/students/abc`);
        if (result.status === 400) {
            printResult('Invalid ID', true, 'Correctly returned 400', result.data);
            return true;
        }
        printResult('Invalid ID', false, `Expected 400, got ${result.status}`, result.data);
        return false;
    } catch (error) {
        printResult('Invalid ID', false, `Request failed: ${error.error}`, error);
        return false;
    }
}

async function testMissingFields() {
    console.log(`${COLORS.BLUE}Testing POST /students with {} (missing fields)...${COLORS.RESET}`);

    try {
        const result = await makeRequest(`${API_BASE_URL}/students`, 'POST', {});
        if (isExpectedError(result, 400)) {
            printResult('Missing Fields', true, 'Correctly returned 400', result.data);
            return true;
        }
        printResult('Missing Fields', false, `Expected 400, got ${result.status}`, result.data);
        return false;
    } catch (error) {
        printResult('Missing Fields', false, `Request failed: ${error.error}`, error);
        return false;
    }
}

function isExpectedError(result, status) {
    return result.status === status &&
        result.data &&
        result.data.success === false &&
        typeof result.data.error === 'string' &&
        result.data.error.length > 0;
}

async function testBadRequestBody(testName, method, path, data, rawData = false) {
    console.log(`${COLORS.BLUE}Testing ${testName}...${COLORS.RESET}`);

    try {
        const result = await makeRequest(`${API_BASE_URL}${path}`, method, data, rawData);
        if (isExpectedError(result, 400)) {
            printResult(testName, true, 'Got 400 with an error response', result.data);
            return true;
        }
        printResult(testName, false,
            `Expected 400 with success:false and an error message, got ${result.status}`, result.data);
        return false;
    } catch (error) {
        printResult(testName, false, `Request failed: ${error.error}`, error);
        return false;
    }
}

async function testMalformedJson() {
    return testBadRequestBody('Malformed JSON', 'POST', '/students', '{bad json', true);
}

async function testArrayBody() {
    return testBadRequestBody('Array Body', 'POST', '/students', [1, 2]);
}

async function testInvalidFieldValues() {
    return testBadRequestBody('Invalid Field Values', 'POST', '/students',
        { name: ' ', email: 'bad' });
}

async function testIncompletePut() {
    return testBadRequestBody('Incomplete PUT', 'PUT', '/students/999999',
        { name: 'Test', email: 'test@example.com' });
}

async function testMethodNotAllowedItem() {
    console.log(`${COLORS.BLUE}Testing POST /students/1 (method not allowed)...${COLORS.RESET}`);

    try {
        const result = await makeRequest(`${API_BASE_URL}/students/1`, 'POST', {});
        const allow = result.headers && result.headers['allow'];
        if (result.status === 405 && allow === 'GET, PUT, DELETE') {
            printResult('Method Not Allowed (item)', true,
                `Got 405 with Allow: ${result.headers['allow']}`, result.data);
            return true;
        }
        printResult('Method Not Allowed (item)', false,
            `Expected 405 + Allow header, got ${result.status}`, result.data);
        return false;
    } catch (error) {
        printResult('Method Not Allowed (item)', false, `Request failed: ${error.error}`, error);
        return false;
    }
}

async function testMethodNotAllowedCollection() {
    console.log(`${COLORS.BLUE}Testing PUT /students (method not allowed)...${COLORS.RESET}`);

    try {
        const result = await makeRequest(`${API_BASE_URL}/students`, 'PUT', {});
        const allow = result.headers && result.headers['allow'];
        if (result.status === 405 && allow === 'GET, POST') {
            printResult('Method Not Allowed (collection)', true,
                `Got 405 with Allow: ${result.headers['allow']}`, result.data);
            return true;
        }
        printResult('Method Not Allowed (collection)', false,
            `Expected 405 + Allow header, got ${result.status}`, result.data);
        return false;
    } catch (error) {
        printResult('Method Not Allowed (collection)', false, `Request failed: ${error.error}`, error);
        return false;
    }
}

// Main test runner
async function runAllTests() {
    console.log(`${COLORS.MAGENTA}🚀 Starting REST API Tests for Student Management System${COLORS.RESET}`);
    console.log(`${COLORS.CYAN}API Base URL: ${API_BASE_URL}${COLORS.RESET}`);
    console.log('');
    
    let passedTests = 0;
    let totalTests = 0;
    
    // Test 1: Server connection
    totalTests++;
    if (await testServerConnection()) {
        passedTests++;
    }
    console.log('');
    
    // Test 2: Get all students
    totalTests++;
    if (await testGetAllStudents()) {
        passedTests++;
    }
    console.log('');
    
    // Test 3: Create student (capture ID for subsequent tests)
    totalTests++;
    const createdStudentId = await testCreateStudent();
    if (createdStudentId) {
        passedTests++;
    }
    console.log('');
    
    // Tests 4-6: Read, update, and delete the created student
    if (createdStudentId) {
        // Test 4: Get single student (using created ID)
        totalTests++;
        if (await testGetSingleStudent(createdStudentId)) {
            passedTests++;
        }
        console.log('');

        // Test 5: Update student (using created ID)
        totalTests++;
        if (await testUpdateStudent(createdStudentId)) {
            passedTests++;
        }
        console.log('');
        
        // Test 6: Delete student (using created ID)
        totalTests++;
        if (await testDeleteStudent(createdStudentId)) {
            passedTests++;
        }
        console.log('');
    }
    
    // Tests 7-15: Failure cases (routing, input, and method validation)
    totalTests++;
    if (await testUnknownResource()) {
        passedTests++;
    }
    console.log('');

    totalTests++;
    if (await testInvalidId()) {
        passedTests++;
    }
    console.log('');

    totalTests++;
    if (await testMissingFields()) {
        passedTests++;
    }
    console.log('');

    totalTests++;
    if (await testMalformedJson()) {
        passedTests++;
    }
    console.log('');

    totalTests++;
    if (await testArrayBody()) {
        passedTests++;
    }
    console.log('');

    totalTests++;
    if (await testInvalidFieldValues()) {
        passedTests++;
    }
    console.log('');

    totalTests++;
    if (await testIncompletePut()) {
        passedTests++;
    }
    console.log('');

    totalTests++;
    if (await testMethodNotAllowedItem()) {
        passedTests++;
    }
    console.log('');

    totalTests++;
    if (await testMethodNotAllowedCollection()) {
        passedTests++;
    }
    console.log('');
    
    // Summary
    console.log(`${COLORS.MAGENTA}📊 Test Summary${COLORS.RESET}`);
    console.log(`${COLORS.CYAN}Total Tests: ${totalTests}${COLORS.RESET}`);
    console.log(`${COLORS.GREEN}Passed: ${passedTests}${COLORS.RESET}`);
    console.log(`${COLORS.RED}Failed: ${totalTests - passedTests}${COLORS.RESET}`);
    
    if (passedTests === totalTests) {
        console.log(`${COLORS.GREEN}🎉 All tests passed!${COLORS.RESET}`);
    } else {
        console.log(`${COLORS.YELLOW}⚠️  Some tests failed. Check the output above.${COLORS.RESET}`);
        process.exitCode = 1;
    }
}

// Command line interface
if (require.main === module) {
    console.log(`${COLORS.CYAN}Simple REST API Testing Tool${COLORS.RESET}`);
    console.log(`${COLORS.YELLOW}Usage: node test_runner.js [--verbose]${COLORS.RESET}`);
    console.log('');
    
    runAllTests().catch(error => {
        console.error(`${COLORS.RED}Fatal error: ${error.message}${COLORS.RESET}`);
        process.exit(1);
    });
}

module.exports = {
    makeRequest,
    testServerConnection,
    testGetAllStudents,
    testGetSingleStudent,
    testCreateStudent,
    testUpdateStudent,
    testDeleteStudent,
    testUnknownResource,
    testInvalidId,
    testMissingFields,
    testMalformedJson,
    testArrayBody,
    testInvalidFieldValues,
    testIncompletePut,
    testMethodNotAllowedItem,
    testMethodNotAllowedCollection
};
