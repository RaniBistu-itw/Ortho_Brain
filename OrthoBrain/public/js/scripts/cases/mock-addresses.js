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

// MOCK_ZIP_ENTRIES + COUNTRIES removed — driven by window.ZIPCODE_ENTRIES and
// window.COUNTRY_ENTRIES embedded in add-case.blade.php from the LocationMaster
// seeders. shipping-address.js falls back to MOCK_ZIP_ENTRIES if needed for
// any test scaffolding, but production has none.
