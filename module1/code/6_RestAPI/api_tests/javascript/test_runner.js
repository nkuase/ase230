#!/usr/bin/env node
/**
 * Simple REST API Tests for Student Management System
 * 
 * This script demonstrates how to test REST APIs using Node.js
 * Run with: node test_runner.js
 * 
 * Make sure your PHP API server is running on http://localhost:8000
 */

// Simple HTTP client using Node.js built-in modules
const http = require('http');
const https = require('https');

// Configuration
const API_BASE_URL = 'http://localhost:8000';
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
function makeRequest(url, method = 'GET', data = null) {
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

        if (data) {
            const postData = JSON.stringify(data);
            options.headers['Content-Length'] = Buffer.byteLength(postData);
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
                        ok: res.statusCode >= 200 && res.statusCode < 300
                    });
                } catch (error) {
                    resolve({
                        status: res.statusCode,
                        data: responseData,
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

        if (data) {
            req.write(JSON.stringify(data));
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

async function testGetSingleStudent() {
    console.log(`${COLORS.BLUE}Testing GET /students/1...${COLORS.RESET}`);
    
    try {
        const result = await makeRequest(`${API_BASE_URL}/students/1`);
        
        if (result.ok && result.data.success && result.data.data) {
            const student = result.data.data;
            const hasRequiredFields = student.id === 1 && 
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
        major: 'Data Science'
    };
    
    try {
        const result = await makeRequest(`${API_BASE_URL}/students/${studentId}`, 'PUT', updateData);
        
        if (result.ok && result.data.success && result.data.data) {
            const updated = result.data.data;
            const isValid = updated.name === updateData.name && 
                updated.major === updateData.major;
            
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
    
    // Test 3: Get single student
    totalTests++;
    if (await testGetSingleStudent()) {
        passedTests++;
    }
    console.log('');
    
    // Test 4: Create student
    totalTests++;
    const createdStudentId = await testCreateStudent();
    if (createdStudentId) {
        passedTests++;
    }
    console.log('');
    
    // Test 5: Update student (only if creation succeeded)
    if (createdStudentId) {
        totalTests++;
        if (await testUpdateStudent(createdStudentId)) {
            passedTests++;
        }
        console.log('');
        
        // Test 6: Delete student
        totalTests++;
        if (await testDeleteStudent(createdStudentId)) {
            passedTests++;
        }
        console.log('');
    }
    
    // Summary
    console.log(`${COLORS.MAGENTA}📊 Test Summary${COLORS.RESET}`);
    console.log(`${COLORS.CYAN}Total Tests: ${totalTests}${COLORS.RESET}`);
    console.log(`${COLORS.GREEN}Passed: ${passedTests}${COLORS.RESET}`);
    console.log(`${COLORS.RED}Failed: ${totalTests - passedTests}${COLORS.RESET}`);
    
    if (passedTests === totalTests) {
        console.log(`${COLORS.GREEN}🎉 All tests passed!${COLORS.RESET}`);
    } else {
        console.log(`${COLORS.YELLOW}⚠️  Some tests failed. Check the output above.${COLORS.RESET}`);
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
    testDeleteStudent
};