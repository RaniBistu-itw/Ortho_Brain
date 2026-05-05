-- ============================================================
-- OrthoBrain — Sample Master Data
-- ============================================================
-- Loads sample data into: countries, states, cities, zipcodes,
-- products_category, products_subcategory, products, scanners.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE zipcodes;
TRUNCATE TABLE cities;
TRUNCATE TABLE states;
TRUNCATE TABLE countries;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- COUNTRIES
-- ============================================================
INSERT INTO countries (id, name, country_code, phone_code, status, created_at, updated_at) VALUES
(1, 'United States',  'US', '+1',   'ACTIVE', NOW(), NOW()),
(2, 'Canada',         'CA', '+1',   'ACTIVE', NOW(), NOW()),
(3, 'India',          'IN', '+91',  'ACTIVE', NOW(), NOW()),
(4, 'United Kingdom', 'GB', '+44',  'ACTIVE', NOW(), NOW()),
(5, 'Australia',      'AU', '+61',  'ACTIVE', NOW(), NOW());

-- ============================================================
-- STATES — United States (50)
-- ============================================================
INSERT INTO states (id, country_id, name, state_code, status, created_at, updated_at) VALUES
(1,  1, 'Alabama',        'AL', 'ACTIVE', NOW(), NOW()),
(2,  1, 'Alaska',         'AK', 'ACTIVE', NOW(), NOW()),
(3,  1, 'Arizona',        'AZ', 'ACTIVE', NOW(), NOW()),
(4,  1, 'Arkansas',       'AR', 'ACTIVE', NOW(), NOW()),
(5,  1, 'California',     'CA', 'ACTIVE', NOW(), NOW()),
(6,  1, 'Colorado',       'CO', 'ACTIVE', NOW(), NOW()),
(7,  1, 'Connecticut',    'CT', 'ACTIVE', NOW(), NOW()),
(8,  1, 'Delaware',       'DE', 'ACTIVE', NOW(), NOW()),
(9,  1, 'Florida',        'FL', 'ACTIVE', NOW(), NOW()),
(10, 1, 'Georgia',        'GA', 'ACTIVE', NOW(), NOW()),
(11, 1, 'Hawaii',         'HI', 'ACTIVE', NOW(), NOW()),
(12, 1, 'Idaho',          'ID', 'ACTIVE', NOW(), NOW()),
(13, 1, 'Illinois',       'IL', 'ACTIVE', NOW(), NOW()),
(14, 1, 'Indiana',        'IN', 'ACTIVE', NOW(), NOW()),
(15, 1, 'Iowa',           'IA', 'ACTIVE', NOW(), NOW()),
(16, 1, 'Kansas',         'KS', 'ACTIVE', NOW(), NOW()),
(17, 1, 'Kentucky',       'KY', 'ACTIVE', NOW(), NOW()),
(18, 1, 'Louisiana',      'LA', 'ACTIVE', NOW(), NOW()),
(19, 1, 'Maine',          'ME', 'ACTIVE', NOW(), NOW()),
(20, 1, 'Maryland',       'MD', 'ACTIVE', NOW(), NOW()),
(21, 1, 'Massachusetts',  'MA', 'ACTIVE', NOW(), NOW()),
(22, 1, 'Michigan',       'MI', 'ACTIVE', NOW(), NOW()),
(23, 1, 'Minnesota',      'MN', 'ACTIVE', NOW(), NOW()),
(24, 1, 'Mississippi',    'MS', 'ACTIVE', NOW(), NOW()),
(25, 1, 'Missouri',       'MO', 'ACTIVE', NOW(), NOW()),
(26, 1, 'Montana',        'MT', 'ACTIVE', NOW(), NOW()),
(27, 1, 'Nebraska',       'NE', 'ACTIVE', NOW(), NOW()),
(28, 1, 'Nevada',         'NV', 'ACTIVE', NOW(), NOW()),
(29, 1, 'New Hampshire',  'NH', 'ACTIVE', NOW(), NOW()),
(30, 1, 'New Jersey',     'NJ', 'ACTIVE', NOW(), NOW()),
(31, 1, 'New Mexico',     'NM', 'ACTIVE', NOW(), NOW()),
(32, 1, 'New York',       'NY', 'ACTIVE', NOW(), NOW()),
(33, 1, 'North Carolina', 'NC', 'ACTIVE', NOW(), NOW()),
(34, 1, 'North Dakota',   'ND', 'ACTIVE', NOW(), NOW()),
(35, 1, 'Ohio',           'OH', 'ACTIVE', NOW(), NOW()),
(36, 1, 'Oklahoma',       'OK', 'ACTIVE', NOW(), NOW()),
(37, 1, 'Oregon',         'OR', 'ACTIVE', NOW(), NOW()),
(38, 1, 'Pennsylvania',   'PA', 'ACTIVE', NOW(), NOW()),
(39, 1, 'Rhode Island',   'RI', 'ACTIVE', NOW(), NOW()),
(40, 1, 'South Carolina', 'SC', 'ACTIVE', NOW(), NOW()),
(41, 1, 'South Dakota',   'SD', 'ACTIVE', NOW(), NOW()),
(42, 1, 'Tennessee',      'TN', 'ACTIVE', NOW(), NOW()),
(43, 1, 'Texas',          'TX', 'ACTIVE', NOW(), NOW()),
(44, 1, 'Utah',           'UT', 'ACTIVE', NOW(), NOW()),
(45, 1, 'Vermont',        'VT', 'ACTIVE', NOW(), NOW()),
(46, 1, 'Virginia',       'VA', 'ACTIVE', NOW(), NOW()),
(47, 1, 'Washington',     'WA', 'ACTIVE', NOW(), NOW()),
(48, 1, 'West Virginia',  'WV', 'ACTIVE', NOW(), NOW()),
(49, 1, 'Wisconsin',      'WI', 'ACTIVE', NOW(), NOW()),
(50, 1, 'Wyoming',        'WY', 'ACTIVE', NOW(), NOW());

-- Canada
INSERT INTO states (id, country_id, name, state_code, status, created_at, updated_at) VALUES
(51, 2, 'Ontario',          'ON', 'ACTIVE', NOW(), NOW()),
(52, 2, 'Quebec',            'QC', 'ACTIVE', NOW(), NOW()),
(53, 2, 'British Columbia',  'BC', 'ACTIVE', NOW(), NOW()),
(54, 2, 'Alberta',           'AB', 'ACTIVE', NOW(), NOW());

-- India
INSERT INTO states (id, country_id, name, state_code, status, created_at, updated_at) VALUES
(55, 3, 'Maharashtra',   'MH', 'ACTIVE', NOW(), NOW()),
(56, 3, 'Karnataka',     'KA', 'ACTIVE', NOW(), NOW()),
(57, 3, 'Tamil Nadu',    'TN', 'ACTIVE', NOW(), NOW()),
(58, 3, 'Delhi',         'DL', 'ACTIVE', NOW(), NOW()),
(59, 3, 'Gujarat',       'GJ', 'ACTIVE', NOW(), NOW());

-- United Kingdom
INSERT INTO states (id, country_id, name, state_code, status, created_at, updated_at) VALUES
(60, 4, 'England',  'ENG', 'ACTIVE', NOW(), NOW()),
(61, 4, 'Scotland', 'SCT', 'ACTIVE', NOW(), NOW()),
(62, 4, 'Wales',    'WLS', 'ACTIVE', NOW(), NOW());

-- Australia
INSERT INTO states (id, country_id, name, state_code, status, created_at, updated_at) VALUES
(63, 5, 'New South Wales', 'NSW', 'ACTIVE', NOW(), NOW()),
(64, 5, 'Victoria',        'VIC', 'ACTIVE', NOW(), NOW()),
(65, 5, 'Queensland',      'QLD', 'ACTIVE', NOW(), NOW());

-- ============================================================
-- CITIES
-- ============================================================
INSERT INTO cities (id, state_id, name, status, created_at, updated_at) VALUES
(1,  35, 'Columbus',      'ACTIVE', NOW(), NOW()),
(2,  35, 'Cleveland',     'ACTIVE', NOW(), NOW()),
(3,  35, 'Cincinnati',    'ACTIVE', NOW(), NOW()),
(4,  5,  'Los Angeles',   'ACTIVE', NOW(), NOW()),
(5,  5,  'San Diego',     'ACTIVE', NOW(), NOW()),
(6,  5,  'San Francisco', 'ACTIVE', NOW(), NOW()),
(7,  32, 'New York',      'ACTIVE', NOW(), NOW()),
(8,  32, 'Buffalo',       'ACTIVE', NOW(), NOW()),
(9,  43, 'Austin',        'ACTIVE', NOW(), NOW()),
(10, 43, 'Dallas',        'ACTIVE', NOW(), NOW()),
(11, 43, 'Houston',       'ACTIVE', NOW(), NOW()),
(12, 9,  'Miami',         'ACTIVE', NOW(), NOW()),
(13, 9,  'Orlando',       'ACTIVE', NOW(), NOW()),
(14, 13, 'Chicago',       'ACTIVE', NOW(), NOW()),
(15, 51, 'Toronto',       'ACTIVE', NOW(), NOW()),
(16, 51, 'Ottawa',        'ACTIVE', NOW(), NOW()),
(17, 55, 'Mumbai',        'ACTIVE', NOW(), NOW()),
(18, 55, 'Pune',          'ACTIVE', NOW(), NOW()),
(19, 56, 'Bangalore',     'ACTIVE', NOW(), NOW()),
(20, 58, 'New Delhi',     'ACTIVE', NOW(), NOW()),
(21, 60, 'London',        'ACTIVE', NOW(), NOW()),
(22, 60, 'Manchester',    'ACTIVE', NOW(), NOW()),
(23, 63, 'Sydney',        'ACTIVE', NOW(), NOW()),
(24, 64, 'Melbourne',     'ACTIVE', NOW(), NOW()),
(25, 35, 'Dayton',        'INACTIVE', NOW(), NOW());

-- ============================================================
-- ZIPCODES
-- ============================================================
INSERT INTO zipcodes (id, code, city_id, status, details, created_at, updated_at) VALUES
(1,  '43215', 1,  'ACTIVE',   'Downtown Columbus',             NOW(), NOW()),
(2,  '43220', 1,  'ACTIVE',   'Upper Arlington area',          NOW(), NOW()),
(3,  '44101', 2,  'ACTIVE',   'Downtown Cleveland',            NOW(), NOW()),
(4,  '44114', 2,  'ACTIVE',   'Cleveland Central Business',    NOW(), NOW()),
(5,  '45202', 3,  'ACTIVE',   'Cincinnati Downtown',           NOW(), NOW()),
(6,  '90001', 4,  'ACTIVE',   'South Los Angeles',             NOW(), NOW()),
(7,  '90028', 4,  'ACTIVE',   'Hollywood',                     NOW(), NOW()),
(8,  '92101', 5,  'ACTIVE',   'Downtown San Diego',            NOW(), NOW()),
(9,  '94102', 6,  'ACTIVE',   'San Francisco Civic Center',    NOW(), NOW()),
(10, '94107', 6,  'ACTIVE',   'SoMa / Potrero',                NOW(), NOW()),
(11, '10001', 7,  'ACTIVE',   'Midtown Manhattan',             NOW(), NOW()),
(12, '10013', 7,  'ACTIVE',   'Tribeca',                       NOW(), NOW()),
(13, '14202', 8,  'ACTIVE',   'Downtown Buffalo',              NOW(), NOW()),
(14, '73301', 9,  'ACTIVE',   'Downtown Austin',               NOW(), NOW()),
(15, '78701', 9,  'ACTIVE',   'Central Austin',                NOW(), NOW()),
(16, '75201', 10, 'ACTIVE',   'Dallas Downtown',               NOW(), NOW()),
(17, '77002', 11, 'ACTIVE',   'Houston Downtown',              NOW(), NOW()),
(18, '33101', 12, 'ACTIVE',   'Downtown Miami',                NOW(), NOW()),
(19, '33109', 12, 'ACTIVE',   'Fisher Island',                 NOW(), NOW()),
(20, '32801', 13, 'ACTIVE',   'Downtown Orlando',              NOW(), NOW()),
(21, '60601', 14, 'ACTIVE',   'The Loop',                      NOW(), NOW()),
(22, 'M5V 2T6', 15, 'ACTIVE', 'Toronto Entertainment District', NOW(), NOW()),
(23, 'K1A 0A6', 16, 'ACTIVE', 'Ottawa — Parliament area',      NOW(), NOW()),
(24, '400001', 17, 'ACTIVE',  'Fort, South Mumbai',            NOW(), NOW()),
(25, '400076', 17, 'ACTIVE',  'Powai',                         NOW(), NOW()),
(26, '411001', 18, 'ACTIVE',  'Pune Camp',                     NOW(), NOW()),
(27, '411045', 18, 'ACTIVE',  'Pimpri-Chinchwad',              NOW(), NOW()),
(28, '560001', 19, 'ACTIVE',  'Bangalore GPO',                 NOW(), NOW()),
(29, '110001', 20, 'ACTIVE',  'Connaught Place',               NOW(), NOW()),
(30, 'SW1A 1AA', 21, 'ACTIVE', 'Westminster',                  NOW(), NOW()),
(31, 'EC1A 1BB', 21, 'ACTIVE', 'City of London',               NOW(), NOW()),
(32, 'M1 1AE', 22, 'ACTIVE',  'Manchester City Centre',        NOW(), NOW()),
(33, '2000',   23, 'ACTIVE',  'Sydney CBD',                    NOW(), NOW()),
(34, '3000',   24, 'ACTIVE',  'Melbourne CBD',                 NOW(), NOW()),
(35, '45402',  25, 'INACTIVE','Legacy zip — Dayton',           NOW(), NOW());
