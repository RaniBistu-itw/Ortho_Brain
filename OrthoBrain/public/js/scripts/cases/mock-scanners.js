// TODO: replace with API call to GET /api/impression-methods
// The real data will be a master table with brand + model hierarchy,
// seeded and admin-managed (Phase 2 admin scope).

window.MOCK_SCANNERS = [
  {
    brand: 'iTero',
    models: [
      { id: 'itero-element-5d',      label: 'Element 5D' },
      { id: 'itero-element-5d-plus', label: 'Element 5D Plus' },
      { id: 'itero-element-flex',    label: 'Element Flex' },
    ],
  },
  {
    brand: '3Shape',
    models: [
      { id: '3shape-trios-3', label: 'Trios 3' },
      { id: '3shape-trios-4', label: 'Trios 4' },
      { id: '3shape-trios-5', label: 'Trios 5' },
    ],
  },
  {
    brand: 'Medit',
    models: [
      { id: 'medit-i500', label: 'i500' },
      { id: 'medit-i700', label: 'i700' },
      { id: 'medit-i900', label: 'i900' },
    ],
  },
  {
    brand: 'Planmeca',
    models: [
      { id: 'planmeca-emerald',   label: 'Emerald' },
      { id: 'planmeca-emerald-s', label: 'Emerald S' },
    ],
  },
  {
    brand: 'Carestream',
    models: [
      { id: 'carestream-cs-3600', label: 'CS 3600' },
      { id: 'carestream-cs-3700', label: 'CS 3700' },
      { id: 'carestream-cs-3800', label: 'CS 3800' },
    ],
  },
  {
    brand: 'Other / Non-digital',
    models: [
      { id: 'pvs', label: 'PVS (Polyvinyl Siloxane)' },
    ],
  },
];
