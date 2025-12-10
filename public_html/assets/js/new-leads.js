document.addEventListener('DOMContentLoaded', function() {
  function getTimezoneFromLocation(state, country) {
    state = state.replace(/^(US-|CA-)/, '').toUpperCase();
    country = country.toUpperCase();
    
    const usTimezones = {
      'CA': 'America/Los_Angeles', 'WA': 'America/Los_Angeles', 'OR': 'America/Los_Angeles', 'NV': 'America/Los_Angeles',
      'AZ': 'America/Phoenix', 'UT': 'America/Denver', 'CO': 'America/Denver', 'WY': 'America/Denver', 
      'MT': 'America/Denver', 'NM': 'America/Denver', 'ND': 'America/Denver', 'SD': 'America/Denver', 'ID': 'America/Denver',
      'TX': 'America/Chicago', 'OK': 'America/Chicago', 'KS': 'America/Chicago', 'NE': 'America/Chicago',
      'MN': 'America/Chicago', 'IA': 'America/Chicago', 'MO': 'America/Chicago', 'AR': 'America/Chicago',
      'LA': 'America/Chicago', 'MS': 'America/Chicago', 'AL': 'America/Chicago', 'TN': 'America/Chicago',
      'KY': 'America/Chicago', 'IN': 'America/Chicago', 'IL': 'America/Chicago', 'WI': 'America/Chicago',
      'MI': 'America/Detroit', 'OH': 'America/New_York', 'WV': 'America/New_York', 'VA': 'America/New_York',
      'PA': 'America/New_York', 'NY': 'America/New_York', 'VT': 'America/New_York', 'NH': 'America/New_York',
      'ME': 'America/New_York', 'MA': 'America/New_York', 'RI': 'America/New_York', 'CT': 'America/New_York',
      'NJ': 'America/New_York', 'DE': 'America/New_York', 'MD': 'America/New_York', 'DC': 'America/New_York',
      'NC': 'America/New_York', 'SC': 'America/New_York', 'GA': 'America/New_York', 'FL': 'America/New_York',
      'AK': 'America/Anchorage', 'HI': 'Pacific/Honolulu'
    };
    
    if (country === 'US' && usTimezones[state]) {
      return usTimezones[state];
    }
    
    const countryTimezones = {
      'US': 'America/New_York',
      'CA': 'America/Toronto',
      'MX': 'America/Mexico_City',
      'UK': 'Europe/London',
      'AU': 'Australia/Sydney',
      'NZ': 'Pacific/Auckland',
      'BR': 'America/Sao_Paulo',
    };
    
    return countryTimezones[country] || 'UTC';
  }
  
  function updateTimezone() {
    const stateField = document.getElementById('form_state');
    const countryField = document.getElementById('form_country');
    const timezoneField = document.getElementById('timezone');
    
    if (stateField && countryField && timezoneField) {
      const state = stateField.value;
      const country = countryField.value;
      const timezone = getTimezoneFromLocation(state, country);
      timezoneField.value = timezone;
    }
  }
  
  const stateField = document.getElementById('form_state');
  const countryField = document.getElementById('form_country');
  
  if (stateField) {
    stateField.addEventListener('change', updateTimezone);
  }
  
  if (countryField) {
    countryField.addEventListener('change', updateTimezone);
  }
  
  updateTimezone();
});
