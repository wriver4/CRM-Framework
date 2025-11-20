// Test credentials for CRM Playwright tests
// These users were created specifically for testing

const TEST_CREDENTIALS = {
  // Super Administrator - Full access
  superAdmin: {
    username: 'superadmin',
    password: 'testpass123',
    email: 'superadmin@democrm.local',
    role: 'Super Administrator',
    roleId: 1
  },

  // Administrator - High level access
  admin: {
    username: 'admin',
    password: 'testpass123',
    email: 'admin@democrm.local',
    role: 'Administrator',
    roleId: 2
  },

  // Sales Manager - Sales management access
  salesManager: {
    username: 'salesman',
    password: 'testpass123',
    email: 'salesman@democrm.local',
    role: 'Sales Manager',
    roleId: 3
  },

  // Sales Assistant - Sales support access
  salesAssistant: {
    username: 'salesasst',
    password: 'testpass123',
    email: 'salesasst@democrm.local',
    role: 'Sales Assistant',
    roleId: 4
  },

  // Sales Person - Basic sales access
  salesPerson: {
    username: 'salesperson',
    password: 'testpass123',
    email: 'salesperson@democrm.local',
    role: 'Sales Person',
    roleId: 5
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