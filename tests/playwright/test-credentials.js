// Test credentials for CRM Playwright tests
// These users were created specifically for testing

const TEST_CREDENTIALS = {
  // Super Administrator - Full access
  superAdmin: {
    username: 'testadmin',
    password: 'TestsuperadminHQ4!!@@1',
    email: 'mark@waveguardco.com',
    role: 'Super Administrator',
    roleId: 1
  },

  // Administrator - High level access
  admin: {
    username: 'testadmin2',
    password: 'TestadminHQ4!!@@1',
    email: 'mark@waveguardco.com',
    role: 'Administrator',
    roleId: 2
  },

  // Sales Manager - Sales management access
  salesManager: {
    username: 'testsalesmgr',
    password: 'TestsalesmanHQ4!!@@1',
    email: 'mark@waveguardco.com',
    role: 'Sales Manager',
    roleId: 13
  },

  // Sales Assistant - Sales support access
  salesAssistant: {
    username: 'testsalesasst',
    password: 'TestsalesassHQ4!!@@1',
    email: 'mark@waveguardco.com',
    role: 'Sales Assistant',
    roleId: 14
  },

  // Sales Person - Basic sales access
  salesPerson: {
    username: 'testsalesperson',
    password: 'TestsalespersonHQ4!!@@1',
    email: 'mark@waveguardco.com',
    role: 'Sales Person',
    roleId: 15
  }
};

// Default test user (Super Admin)
const DEFAULT_TEST_USER = TEST_CREDENTIALS.superAdmin;

// Get credentials by role
function getCredentialsByRole (role) {
  const roleMap = {
    'super-admin': TEST_CREDENTIALS.superAdmin,
    'admin': TEST_CREDENTIALS.admin,
    'sales-manager': TEST_CREDENTIALS.salesManager,
    'sales-assistant': TEST_CREDENTIALS.salesAssistant,
    'sales-person': TEST_CREDENTIALS.salesPerson
  };

  return roleMap[role] || DEFAULT_TEST_USER;
}

// Get credentials by role ID
function getCredentialsByRoleId (roleId) {
  const credentials = Object.values(TEST_CREDENTIALS);
  return credentials.find(cred => cred.roleId === roleId) || DEFAULT_TEST_USER;
}

module.exports = {
  TEST_CREDENTIALS,
  DEFAULT_TEST_USER,
  getCredentialsByRole,
  getCredentialsByRoleId
};