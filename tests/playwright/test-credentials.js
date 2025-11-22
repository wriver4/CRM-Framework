// Test credentials for CRM Playwright tests
// These users correspond to the 32-role RBAC system
// Users should be created in the database with these credentials before running tests

const TEST_CREDENTIALS = {
  // System Administrator - Full system access (System Admin role, ID 2)
  // Note: System roles (1-2) are excluded from user assignment in the UI
  // but can be assigned directly in database for testing
  superAdmin: {
    username: 'system_admin',
    password: 'testpass123',
    email: 'systemadmin@democrm.local',
    role: 'System Admin',
    roleId: 2
  },

  // Executive VP - Broad access to all departments (Executive VP role, ID 11)
  executive: {
    username: 'executive',
    password: 'testpass123',
    email: 'executive@democrm.local',
    role: 'Executive VP',
    roleId: 11
  },

  // Sales Manager - Sales management access (Sales Manager role, ID 30)
  salesManager: {
    username: 'sales_manager',
    password: 'testpass123',
    email: 'sales.manager@democrm.local',
    role: 'Sales Manager',
    roleId: 30
  },

  // Sales Rep - Basic sales access (Sales Rep role, ID 35)
  salesRep: {
    username: 'sales_rep',
    password: 'testpass123',
    email: 'sales.rep@democrm.local',
    role: 'Sales Rep',
    roleId: 35
  },

  // Engineer - Engineering department access (Engineer role, ID 43)
  engineer: {
    username: 'engineer',
    password: 'testpass123',
    email: 'engineer@democrm.local',
    role: 'Engineer',
    roleId: 43
  },

  // Production Supervisor - Manufacturing access (Production Supervisor role, ID 51)
  productionSupervisor: {
    username: 'prod_supervisor',
    password: 'testpass123',
    email: 'supervisor@democrm.local',
    role: 'Production Supervisor',
    roleId: 51
  },

  // Partner Manager - Partner access (Partner Manager role, ID 110)
  partnerManager: {
    username: 'partner_manager',
    password: 'testpass123',
    email: 'partner.manager@democrm.local',
    role: 'Partner Manager',
    roleId: 110
  },

  // Client User - Limited client access (Client User role, ID 162)
  clientUser: {
    username: 'client_user',
    password: 'testpass123',
    email: 'client@democrm.local',
    role: 'Client User',
    roleId: 162
  }
};

// Default test user (Executive VP - has broad access but not system admin)
// Using Executive VP instead of System Admin for safer testing
const DEFAULT_TEST_USER = TEST_CREDENTIALS.executive;

// Get credentials by role name
function getCredentialsByRole (role) {
  const roleMap = {
    // System roles
    'system-admin': TEST_CREDENTIALS.superAdmin,
    'system_admin': TEST_CREDENTIALS.superAdmin,
    
    // Executive roles
    'executive': TEST_CREDENTIALS.executive,
    'executive-vp': TEST_CREDENTIALS.executive,
    'executive_vp': TEST_CREDENTIALS.executive,
    
    // Sales roles
    'sales-manager': TEST_CREDENTIALS.salesManager,
    'sales_manager': TEST_CREDENTIALS.salesManager,
    'sales-rep': TEST_CREDENTIALS.salesRep,
    'sales_rep': TEST_CREDENTIALS.salesRep,
    
    // Engineering roles
    'engineer': TEST_CREDENTIALS.engineer,
    
    // Manufacturing roles
    'production-supervisor': TEST_CREDENTIALS.productionSupervisor,
    'production_supervisor': TEST_CREDENTIALS.productionSupervisor,
    
    // Partner roles
    'partner-manager': TEST_CREDENTIALS.partnerManager,
    'partner_manager': TEST_CREDENTIALS.partnerManager,
    
    // Client roles
    'client-user': TEST_CREDENTIALS.clientUser,
    'client_user': TEST_CREDENTIALS.clientUser
  };

  return roleMap[role] || DEFAULT_TEST_USER;
}

// Get credentials by role ID (32-role system)
function getCredentialsByRoleId (roleId) {
  const roleIdMap = {
    2: TEST_CREDENTIALS.superAdmin,      // System Admin
    11: TEST_CREDENTIALS.executive,      // Executive VP
    30: TEST_CREDENTIALS.salesManager,   // Sales Manager
    35: TEST_CREDENTIALS.salesRep,       // Sales Rep
    43: TEST_CREDENTIALS.engineer,       // Engineer
    51: TEST_CREDENTIALS.productionSupervisor, // Production Supervisor
    110: TEST_CREDENTIALS.partnerManager, // Partner Manager
    162: TEST_CREDENTIALS.clientUser      // Client User
  };

  return roleIdMap[roleId] || DEFAULT_TEST_USER;
}

module.exports = {
  TEST_CREDENTIALS,
  DEFAULT_TEST_USER,
  getCredentialsByRole,
  getCredentialsByRoleId
};