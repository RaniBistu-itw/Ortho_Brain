// TODO: replace with API call to GET /api/doctors/{id}/preferences
// These are the hardcoded default preferences for the prototype.
// In production, these load from the doctor-preferences settings page.
window.MOCK_DOCTOR_PREFERENCES = {
  iprProtocol:  { enabled: true, value: 'no-ipr' },
  attachments:  { enabled: true, value: 'step-1', specificStepNumber: null },
  elastics:     { enabled: true, value: 'yes' },
  extractions:  { enabled: true, value: 'no' },
};
