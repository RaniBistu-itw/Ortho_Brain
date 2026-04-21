// TODO: replace with API call to GET /api/practices/{id}/clinic-address
window.MOCK_CLINIC_ADDRESS = {
  practice:       'Promo Indp Practice Admin 3',
  doctorName:     'Promo Indp Practice Admin 3 Dr 1',
  streetAddress:  '1234 Dental Way',
  streetAddress2: 'Suite 200',
  zipId:          'us-43015',
  city:           'Delaware',
  state:          'OH',
  country:        'US',
};

// TODO: replace with API call to GET /api/doctors/{id}/saved-addresses
window.MOCK_SAVED_ADDRESSES = [
  {
    id:             'saved-1',
    label:          'Main Office',
    streetAddress:  '1234 Dental Way',
    streetAddress2: 'Suite 200',
    zipId:          'us-43015',
    city:           'Delaware',
    state:          'OH',
    country:        'US',
  },
  {
    id:             'saved-2',
    label:          'Satellite Clinic',
    streetAddress:  '5678 Smile Blvd',
    streetAddress2: '',
    zipId:          'us-10001',
    city:           'New York',
    state:          'NY',
    country:        'US',
  },
];

// TODO: replace with API/seeded DB lookup
window.MOCK_ZIP_ENTRIES = [
  { id: 'us-43015',  zip: '43015',   displayLabel: '43015 - Delaware (OH)',        city: 'Delaware',      state: 'OH',  country: 'US' },
  { id: 'us-10001',  zip: '10001',   displayLabel: '10001 - New York (NY)',        city: 'New York',      state: 'NY',  country: 'US' },
  { id: 'us-90210',  zip: '90210',   displayLabel: '90210 - Beverly Hills (CA)',   city: 'Beverly Hills', state: 'CA',  country: 'US' },
  { id: 'us-60601',  zip: '60601',   displayLabel: '60601 - Chicago (IL)',         city: 'Chicago',       state: 'IL',  country: 'US' },
  { id: 'ca-k1a0b1', zip: 'K1A 0B1', displayLabel: 'K1A 0B1 - Ottawa (ON)',        city: 'Ottawa',        state: 'ON',  country: 'CA' },
  { id: 'ca-m5v3l9', zip: 'M5V 3L9', displayLabel: 'M5V 3L9 - Toronto (ON)',       city: 'Toronto',       state: 'ON',  country: 'CA' },
  { id: 'au-2000',   zip: '2000',    displayLabel: '2000 - Sydney (NSW)',          city: 'Sydney',        state: 'NSW', country: 'AU' },
  { id: 'au-3000',   zip: '3000',    displayLabel: '3000 - Melbourne (VIC)',       city: 'Melbourne',     state: 'VIC', country: 'AU' },
];

window.COUNTRIES = [
  { code: 'US', label: 'United States' },
  { code: 'CA', label: 'Canada' },
  { code: 'AU', label: 'Australia' },
];
