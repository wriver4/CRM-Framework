// Minimal Playwright config for testing
module.exports = {
  testDir: './tests/playwright',
  use: {
    baseURL: 'https://democrm.waveguardco.net',
    ignoreHTTPSErrors: true,
    actionTimeout: 30000,
    navigationTimeout: 30000,
  },
  projects: [
    {
      name: 'chromium',
      use: {
        browserName: 'chromium',
        viewport: { width: 1280, height: 720 }
      },
    },
  ],
  reporter: 'list',
};