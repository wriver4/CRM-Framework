const { spawnSync } = require('child_process');
const path = require('path');

async function globalSetup(config) {
  console.log('\n╔════════════════════════════════════════════════════════════════╗');
  console.log('║                   PLAYWRIGHT TEST SETUP                        ║');
  console.log('╚════════════════════════════════════════════════════════════════╝\n');
  
  console.log('ℹ️  Tests will send X-Playwright-Test header to remote CRM');
  console.log('   This tells the CRM to use the test database (democrm_test)\n');

  const projectRoot = path.resolve(__dirname, '../../');
  const setupScript = path.join(projectRoot, 'tests', 'setup-test-database.php');

  const dbConfig = {
    host: process.env.TEST_DB_HOST || 'localhost',
    database: process.env.TEST_DB_NAME || 'democrm_test',
    user: process.env.TEST_DB_USER || 'democrm_test',
    password: process.env.TEST_DB_PASS || 'TestDB_2025_Secure!',
  };

  console.log('🔧 Setting up test database...');
  console.log(`   Host:     ${dbConfig.host}`);
  console.log(`   Database: ${dbConfig.database}`);
  console.log(`   User:     ${dbConfig.user}`);
  console.log(`   Script:   ${setupScript}`);

  const env = {
    ...process.env,
    APP_ENV: 'testing',
    TESTING_MODE: 'true',
    TEST_DB_HOST: dbConfig.host,
    TEST_DB_NAME: dbConfig.database,
    TEST_DB_USER: dbConfig.user,
    TEST_DB_PASS: dbConfig.password,
  };

  console.log('\n📝 Running setup script via SSH...\n');
  
  const result = spawnSync('ssh', ['-p', '222', 'root@159.203.116.150', `cd /home/democrm && php tests/setup-test-database.php --mode=persistent --seed=standard --reset`], {
    cwd: projectRoot,
    env,
    encoding: 'utf-8',
    stdio: 'inherit'
  });

  if (result.error) {
    console.log(`\n⚠️  Setup script not available: ${result.error.message}`);
    console.log('    Tests will proceed, but may fail if database is not properly seeded.\n');
    console.log('    To manually setup the database via SSH, run:');
    console.log(`    ssh -p 222 root@159.203.116.150 "cd /home/democrm && php tests/setup-test-database.php --mode=persistent --seed=standard --reset"\n`);
    return;
  }

  if (result.status !== 0 && result.status !== null) {
    console.log(`\n⚠️  Setup script exited with status ${result.status}`);
    console.log('    Tests will proceed but may fail if database was not properly initialized.\n');
    return;
  }

  console.log('\n✅ Test database setup complete\n');
  console.log('📝 Expected test users:');
  console.log('   • superadmin / testpass123 (role: Super Administrator)');
  console.log('   • admin / testpass123 (role: Administrator)');
  console.log('   • salesman / testpass123 (role: Sales Manager)');
  console.log('   • salesasst / testpass123 (role: Sales Assistant)');
  console.log('   • salesperson / testpass123 (role: Sales Person)');
  console.log('\n');
}

module.exports = globalSetup;
