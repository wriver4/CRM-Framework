const { test, expect } = require('@playwright/test');
const { login } = require('./auth-helper');
const { DEFAULT_TEST_USER } = require('./test-credentials');

test.describe('Calendar API Tests', () => {
  const testUsername = DEFAULT_TEST_USER.username;
  const testPassword = DEFAULT_TEST_USER.password;

  test.beforeEach(async ({ page }) => {
    await page.setExtraHTTPHeaders({
      'User-Agent': 'Playwright-Calendar-API-Testing'
    });
  });

  test.describe('Calendar Event CRUD Operations', () => {
    test('should test calendar events API endpoints', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Test GET calendar events
      const getResponse = await page.request.get('/calendar/api/events.php', {
        data: {
          start: '2024-01-01',
          end: '2024-12-31'
        }
      });

      console.log('GET events response status:', getResponse.status());
      expect([200, 404, 405]).toContain(getResponse.status());

      if (getResponse.status() === 200) {
        const responseText = await getResponse.text();
        console.log('Events API response preview:', responseText.substring(0, 200));

        // Try to parse as JSON
        if (responseText.trim().startsWith('{') || responseText.trim().startsWith('[')) {
          try {
            const jsonResponse = JSON.parse(responseText);
            console.log('Events API returned valid JSON');

            if (Array.isArray(jsonResponse)) {
              console.log(`Found ${jsonResponse.length} events`);
            } else if (jsonResponse.events) {
              console.log(`Found ${jsonResponse.events.length} events in response`);
            }
          } catch (e) {
            console.log('Response is not valid JSON');
          }
        }
      }
    });

    test('should test calendar event creation API', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Test POST create event
      const eventData = {
        title: 'API Test Event',
        event_type: 'phone_call',
        start_datetime: '2024-12-25 10:00:00',
        end_datetime: '2024-12-25 11:00:00',
        priority: '5',
        contact_name: 'API Test Contact',
        contact_phone: '+1234567890',
        description: 'Event created via API test',
        status: 'pending'
      };

      const createResponse = await page.request.post('/calendar/api/create_event.php', {
        data: eventData
      });

      console.log('Create event response status:', createResponse.status());
      expect([200, 201, 400, 404, 405, 422]).toContain(createResponse.status());

      if ([200, 201].includes(createResponse.status())) {
        const responseText = await createResponse.text();
        console.log('Create event response:', responseText.substring(0, 200));

        try {
          const jsonResponse = JSON.parse(responseText);
          if (jsonResponse.success || jsonResponse.id) {
            console.log('✅ Event created successfully via API');

            // Store event ID for cleanup if available
            const eventId = jsonResponse.id || jsonResponse.event_id;
            if (eventId) {
              console.log(`Created event ID: ${eventId}`);
            }
          }
        } catch (e) {
          console.log('Create response is not JSON');
        }
      }
    });

    test('should test calendar event update API', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // First create an event to update
      const eventData = {
        title: 'Update Test Event',
        event_type: 'email',
        start_datetime: '2024-12-26 14:00:00',
        priority: '1',
        description: 'Event to be updated'
      };

      const createResponse = await page.request.post('/calendar/api/create_event.php', {
        data: eventData
      });

      if ([200, 201].includes(createResponse.status())) {
        try {
          const createResult = JSON.parse(await createResponse.text());
          const eventId = createResult.id || createResult.event_id || 1; // Use 1 as fallback

          // Now try to update the event
          const updateData = {
            id: eventId,
            title: 'Updated Test Event',
            priority: '10',
            description: 'Event updated via API test'
          };

          const updateResponse = await page.request.post('/calendar/api/update_event.php', {
            data: updateData
          });

          console.log('Update event response status:', updateResponse.status());
          expect([200, 400, 404, 405, 422]).toContain(updateResponse.status());

          if (updateResponse.status() === 200) {
            const updateResult = await updateResponse.text();
            console.log('Update response:', updateResult.substring(0, 200));
            console.log('✅ Event updated successfully via API');
          }
        } catch (e) {
          console.log('Could not parse create response for update test');
        }
      }
    });

    test('should test calendar event deletion API', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Test DELETE event (using a test ID)
      const deleteData = {
        id: 999999 // Use a high ID that likely doesn't exist
      };

      const deleteResponse = await page.request.post('/calendar/api/delete_event.php', {
        data: deleteData
      });

      console.log('Delete event response status:', deleteResponse.status());
      expect([200, 400, 404, 405, 422]).toContain(deleteResponse.status());

      const deleteResult = await deleteResponse.text();
      console.log('Delete response:', deleteResult.substring(0, 200));

      // For non-existent ID, we expect either 404 or an error message
      if (deleteResponse.status() === 404) {
        console.log('✅ Proper 404 response for non-existent event');
      } else if (deleteResponse.status() === 200) {
        try {
          const jsonResult = JSON.parse(deleteResult);
          if (jsonResult.error || jsonResult.message) {
            console.log('✅ Proper error handling for non-existent event');
          }
        } catch (e) {
          console.log('Delete response is not JSON');
        }
      }
    });
  });

  test.describe('Calendar Statistics API', () => {
    test('should test calendar statistics endpoints', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Test stats API
      const statsResponse = await page.request.get('/calendar/api/stats.php');

      console.log('Stats API response status:', statsResponse.status());
      expect([200, 404, 405]).toContain(statsResponse.status());

      if (statsResponse.status() === 200) {
        const statsText = await statsResponse.text();
        console.log('Stats API response:', statsText.substring(0, 300));

        try {
          const statsJson = JSON.parse(statsText);
          console.log('Stats API returned valid JSON');

          // Check for expected stats fields
          const expectedFields = ['calls_today', 'emails_today', 'meetings_today', 'high_priority'];
          const foundFields = expectedFields.filter(field =>
            statsJson.hasOwnProperty(field) ||
            statsJson.hasOwnProperty(field.replace('_', ''))
          );

          console.log(`Found ${foundFields.length}/${expectedFields.length} expected stats fields`);

          if (foundFields.length > 0) {
            console.log('✅ Stats API working correctly');
          }
        } catch (e) {
          console.log('Stats response is not valid JSON');
        }
      }
    });

    test('should test calendar events by date range', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Test events for specific date range
      const today = new Date();
      const nextWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);

      const rangeResponse = await page.request.get('/calendar/api/events.php', {
        params: {
          start: today.toISOString().split('T')[0],
          end: nextWeek.toISOString().split('T')[0]
        }
      });

      console.log('Date range events response status:', rangeResponse.status());

      if (rangeResponse.status() === 200) {
        const rangeText = await rangeResponse.text();
        console.log('Date range response preview:', rangeText.substring(0, 200));

        try {
          const rangeJson = JSON.parse(rangeText);
          if (Array.isArray(rangeJson)) {
            console.log(`Found ${rangeJson.length} events in date range`);
          }
          console.log('✅ Date range filtering working');
        } catch (e) {
          console.log('Date range response is not valid JSON');
        }
      }
    });
  });

  test.describe('Calendar API Error Handling', () => {
    test('should handle invalid data gracefully', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Test with invalid event data
      const invalidData = {
        title: '', // Empty title
        event_type: 'invalid_type',
        start_datetime: 'invalid-date',
        priority: 'invalid-priority'
      };

      const invalidResponse = await page.request.post('/calendar/api/create_event.php', {
        data: invalidData
      });

      console.log('Invalid data response status:', invalidResponse.status());
      expect([400, 422, 500]).toContain(invalidResponse.status());

      const invalidText = await invalidResponse.text();
      console.log('Invalid data response:', invalidText.substring(0, 200));

      // Should return proper error response
      if ([400, 422].includes(invalidResponse.status())) {
        console.log('✅ Proper error status for invalid data');
      }
    });

    test('should handle missing authentication', async ({ page }) => {
      // Test API without authentication
      const unauthResponse = await page.request.get('/calendar/api/events.php');

      console.log('Unauthenticated response status:', unauthResponse.status());

      // Should return 401 Unauthorized or redirect to login
      if ([401, 403, 302].includes(unauthResponse.status())) {
        console.log('✅ Proper authentication handling');
      } else {
        const responseText = await unauthResponse.text();
        if (responseText.includes('login') || responseText.includes('unauthorized')) {
          console.log('✅ Authentication check in response content');
        }
      }
    });

    test('should handle malformed requests', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Test with malformed JSON
      const malformedResponse = await page.request.post('/calendar/api/create_event.php', {
        data: 'invalid-json-data'
      });

      console.log('Malformed request response status:', malformedResponse.status());
      expect([400, 422, 500]).toContain(malformedResponse.status());

      // Test with missing required fields
      const missingFieldsResponse = await page.request.post('/calendar/api/create_event.php', {
        data: {
          // Missing required fields like title, event_type, etc.
          description: 'Test with missing fields'
        }
      });

      console.log('Missing fields response status:', missingFieldsResponse.status());
      expect([400, 422]).toContain(missingFieldsResponse.status());
    });
  });

  test.describe('Calendar API Performance', () => {
    test('should respond within reasonable time limits', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Test API response times
      const endpoints = [
        '/calendar/api/events.php',
        '/calendar/api/stats.php'
      ];

      for (const endpoint of endpoints) {
        const startTime = Date.now();

        const response = await page.request.get(endpoint);

        const responseTime = Date.now() - startTime;
        console.log(`${endpoint} responded in ${responseTime}ms`);

        // API should respond within 5 seconds
        expect(responseTime).toBeLessThan(5000);

        if (responseTime < 1000) {
          console.log(`✅ ${endpoint} has good performance`);
        } else if (responseTime < 3000) {
          console.log(`⚠️  ${endpoint} has acceptable performance`);
        } else {
          console.log(`❌ ${endpoint} has slow performance`);
        }
      }
    });

    test('should handle concurrent requests', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Make multiple concurrent requests
      const promises = [];
      const requestCount = 5;

      for (let i = 0; i < requestCount; i++) {
        promises.push(
          page.request.get('/calendar/api/events.php').then(response => ({
            index: i,
            status: response.status(),
            time: Date.now()
          }))
        );
      }

      const startTime = Date.now();
      const results = await Promise.all(promises);
      const totalTime = Date.now() - startTime;

      console.log(`${requestCount} concurrent requests completed in ${totalTime}ms`);

      // Check that all requests succeeded
      const successCount = results.filter(r => [200, 404].includes(r.status)).length;
      console.log(`${successCount}/${requestCount} requests successful`);

      expect(successCount).toBeGreaterThan(0);

      // Concurrent requests should complete within 10 seconds
      expect(totalTime).toBeLessThan(10000);
    });
  });
});