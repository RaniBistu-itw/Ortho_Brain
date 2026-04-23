<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\Zipcode;
use Illuminate\Database\Seeder;

class LocationMasterSeeder extends Seeder
{
    public function run(): void
    {
        $data = $this->locationData();

        foreach ($data as $countryCode => $countryData) {
            $country = Country::firstOrCreate(
                ['country_code' => $countryCode],
                [
                    'name'       => $countryData['name'],
                    'phone_code' => $countryData['phone_code'],
                    'status'     => 'ACTIVE',
                ]
            );

            foreach ($countryData['states'] as $stateCode => $stateData) {
                $state = State::firstOrCreate(
                    ['country_id' => $country->id, 'state_code' => $stateCode],
                    [
                        'name'   => $stateData['name'],
                        'status' => 'ACTIVE',
                    ]
                );

                foreach ($stateData['cities'] as $cityName => $zipcodes) {
                    $city = City::firstOrCreate(
                        ['state_id' => $state->id, 'name' => $cityName],
                        ['status' => 'ACTIVE']
                    );

                    foreach ($zipcodes as $code) {
                        Zipcode::firstOrCreate(
                            ['city_id' => $city->id, 'code' => $code],
                            ['status' => 'ACTIVE']
                        );
                    }
                }
            }
        }
    }

    private function locationData(): array
    {
        return [
            'US' => [
                'name'       => 'United States',
                'phone_code' => '+1',
                'states'     => [
                    'AL' => ['name' => 'Alabama', 'cities' => [
                        'Birmingham'  => ['35201', '35203', '35205'],
                        'Montgomery'  => ['36101', '36104', '36106'],
                        'Huntsville'  => ['35801', '35802', '35803'],
                        'Mobile'      => ['36601', '36602', '36604'],
                    ]],
                    'AK' => ['name' => 'Alaska', 'cities' => [
                        'Anchorage'   => ['99501', '99502', '99503'],
                        'Fairbanks'   => ['99701', '99709'],
                        'Juneau'      => ['99801', '99802'],
                    ]],
                    'AZ' => ['name' => 'Arizona', 'cities' => [
                        'Phoenix'     => ['85001', '85003', '85004'],
                        'Tucson'      => ['85701', '85705', '85711'],
                        'Scottsdale'  => ['85250', '85251', '85257'],
                        'Mesa'        => ['85201', '85202', '85203'],
                    ]],
                    'AR' => ['name' => 'Arkansas', 'cities' => [
                        'Little Rock' => ['72201', '72202', '72204'],
                        'Fort Smith'  => ['72901', '72903'],
                        'Fayetteville' => ['72701', '72703'],
                    ]],
                    'CA' => ['name' => 'California', 'cities' => [
                        'Los Angeles'   => ['90001', '90002', '90010'],
                        'San Francisco' => ['94101', '94102', '94103'],
                        'San Diego'     => ['92101', '92103', '92108'],
                        'San Jose'      => ['95101', '95110', '95112'],
                        'Sacramento'    => ['95814', '95816', '95818'],
                    ]],
                    'CO' => ['name' => 'Colorado', 'cities' => [
                        'Denver'           => ['80201', '80202', '80203'],
                        'Colorado Springs' => ['80901', '80903', '80905'],
                        'Aurora'           => ['80010', '80011', '80012'],
                        'Fort Collins'     => ['80521', '80522', '80524'],
                    ]],
                    'CT' => ['name' => 'Connecticut', 'cities' => [
                        'Hartford'    => ['06101', '06103', '06106'],
                        'Bridgeport'  => ['06601', '06604', '06607'],
                        'New Haven'   => ['06501', '06510', '06511'],
                        'Stamford'    => ['06901', '06902', '06905'],
                    ]],
                    'DE' => ['name' => 'Delaware', 'cities' => [
                        'Wilmington'  => ['19801', '19802', '19803'],
                        'Dover'       => ['19901', '19904'],
                        'Newark'      => ['19702', '19711'],
                    ]],
                    'FL' => ['name' => 'Florida', 'cities' => [
                        'Miami'        => ['33101', '33125', '33130'],
                        'Orlando'      => ['32801', '32803', '32806'],
                        'Tampa'        => ['33601', '33602', '33604'],
                        'Jacksonville' => ['32201', '32204', '32206'],
                        'Fort Lauderdale' => ['33301', '33304', '33309'],
                    ]],
                    'GA' => ['name' => 'Georgia', 'cities' => [
                        'Atlanta'   => ['30301', '30303', '30308'],
                        'Augusta'   => ['30901', '30904', '30909'],
                        'Savannah'  => ['31401', '31404', '31405'],
                        'Columbus'  => ['31901', '31902', '31904'],
                    ]],
                    'HI' => ['name' => 'Hawaii', 'cities' => [
                        'Honolulu'  => ['96801', '96813', '96817'],
                        'Hilo'      => ['96720', '96721'],
                        'Kailua'    => ['96734'],
                    ]],
                    'ID' => ['name' => 'Idaho', 'cities' => [
                        'Boise'     => ['83701', '83702', '83705'],
                        'Nampa'     => ['83651', '83686'],
                        'Meridian'  => ['83642', '83646'],
                    ]],
                    'IL' => ['name' => 'Illinois', 'cities' => [
                        'Chicago'    => ['60601', '60602', '60603'],
                        'Aurora'     => ['60505', '60506'],
                        'Rockford'   => ['61101', '61102', '61104'],
                        'Naperville'  => ['60540', '60563', '60565'],
                    ]],
                    'IN' => ['name' => 'Indiana', 'cities' => [
                        'Indianapolis' => ['46201', '46202', '46204'],
                        'Fort Wayne'   => ['46801', '46802', '46803'],
                        'Evansville'   => ['47701', '47708', '47710'],
                        'South Bend'   => ['46601', '46615', '46619'],
                    ]],
                    'IA' => ['name' => 'Iowa', 'cities' => [
                        'Des Moines'   => ['50301', '50309', '50311'],
                        'Cedar Rapids' => ['52401', '52402', '52403'],
                        'Davenport'    => ['52801', '52803', '52804'],
                    ]],
                    'KS' => ['name' => 'Kansas', 'cities' => [
                        'Wichita'        => ['67201', '67202', '67203'],
                        'Overland Park'  => ['66204', '66210', '66212'],
                        'Topeka'         => ['66601', '66603', '66604'],
                    ]],
                    'KY' => ['name' => 'Kentucky', 'cities' => [
                        'Louisville'  => ['40201', '40202', '40203'],
                        'Lexington'   => ['40501', '40502', '40503'],
                        'Bowling Green' => ['42101', '42103'],
                    ]],
                    'LA' => ['name' => 'Louisiana', 'cities' => [
                        'New Orleans'  => ['70112', '70113', '70115'],
                        'Baton Rouge'  => ['70801', '70802', '70806'],
                        'Shreveport'   => ['71101', '71103', '71104'],
                    ]],
                    'ME' => ['name' => 'Maine', 'cities' => [
                        'Portland'  => ['04101', '04102', '04103'],
                        'Lewiston'  => ['04240', '04241'],
                        'Bangor'    => ['04401', '04402'],
                    ]],
                    'MD' => ['name' => 'Maryland', 'cities' => [
                        'Baltimore'  => ['21201', '21202', '21205'],
                        'Frederick'  => ['21701', '21702', '21703'],
                        'Rockville'  => ['20850', '20852', '20853'],
                        'Annapolis'  => ['21401', '21403'],
                    ]],
                    'MA' => ['name' => 'Massachusetts', 'cities' => [
                        'Boston'      => ['02101', '02102', '02110'],
                        'Worcester'   => ['01601', '01602', '01603'],
                        'Springfield' => ['01101', '01103', '01104'],
                        'Cambridge'   => ['02138', '02139', '02141'],
                    ]],
                    'MI' => ['name' => 'Michigan', 'cities' => [
                        'Detroit'       => ['48201', '48202', '48203'],
                        'Grand Rapids'  => ['49501', '49503', '49504'],
                        'Ann Arbor'     => ['48103', '48104', '48105'],
                        'Lansing'       => ['48901', '48906', '48910'],
                    ]],
                    'MN' => ['name' => 'Minnesota', 'cities' => [
                        'Minneapolis'  => ['55401', '55402', '55403'],
                        'Saint Paul'   => ['55101', '55102', '55103'],
                        'Rochester'    => ['55901', '55902', '55904'],
                        'Duluth'       => ['55801', '55802', '55803'],
                    ]],
                    'MS' => ['name' => 'Mississippi', 'cities' => [
                        'Jackson'      => ['39201', '39202', '39204'],
                        'Gulfport'     => ['39501', '39503', '39507'],
                        'Hattiesburg'  => ['39401', '39402'],
                    ]],
                    'MO' => ['name' => 'Missouri', 'cities' => [
                        'Kansas City'  => ['64101', '64105', '64108'],
                        'St. Louis'    => ['63101', '63103', '63104'],
                        'Springfield'  => ['65801', '65802', '65803'],
                        'Columbia'     => ['65201', '65202', '65203'],
                    ]],
                    'MT' => ['name' => 'Montana', 'cities' => [
                        'Billings'     => ['59101', '59102', '59105'],
                        'Missoula'     => ['59801', '59802', '59803'],
                        'Great Falls'  => ['59401', '59403', '59404'],
                    ]],
                    'NE' => ['name' => 'Nebraska', 'cities' => [
                        'Omaha'    => ['68101', '68102', '68105'],
                        'Lincoln'  => ['68501', '68502', '68503'],
                        'Bellevue' => ['68005', '68123'],
                    ]],
                    'NV' => ['name' => 'Nevada', 'cities' => [
                        'Las Vegas'   => ['89101', '89102', '89104'],
                        'Henderson'   => ['89002', '89014', '89015'],
                        'Reno'        => ['89501', '89502', '89503'],
                        'North Las Vegas' => ['89030', '89031'],
                    ]],
                    'NH' => ['name' => 'New Hampshire', 'cities' => [
                        'Manchester'  => ['03101', '03102', '03103'],
                        'Nashua'      => ['03060', '03063', '03064'],
                        'Concord'     => ['03301', '03302', '03303'],
                    ]],
                    'NJ' => ['name' => 'New Jersey', 'cities' => [
                        'Newark'      => ['07101', '07102', '07103'],
                        'Jersey City' => ['07302', '07304', '07305'],
                        'Trenton'     => ['08601', '08608', '08609'],
                        'Camden'      => ['08101', '08102', '08103'],
                    ]],
                    'NM' => ['name' => 'New Mexico', 'cities' => [
                        'Albuquerque'  => ['87101', '87102', '87104'],
                        'Santa Fe'     => ['87501', '87502', '87505'],
                        'Las Cruces'   => ['88001', '88004', '88005'],
                    ]],
                    'NY' => ['name' => 'New York', 'cities' => [
                        'New York City' => ['10001', '10002', '10007'],
                        'Buffalo'       => ['14201', '14202', '14204'],
                        'Rochester'     => ['14601', '14602', '14604'],
                        'Albany'        => ['12201', '12202', '12203'],
                        'Syracuse'      => ['13201', '13202', '13203'],
                    ]],
                    'NC' => ['name' => 'North Carolina', 'cities' => [
                        'Charlotte'    => ['28201', '28202', '28203'],
                        'Raleigh'      => ['27601', '27603', '27604'],
                        'Greensboro'   => ['27401', '27403', '27405'],
                        'Durham'       => ['27701', '27702', '27703'],
                    ]],
                    'ND' => ['name' => 'North Dakota', 'cities' => [
                        'Fargo'        => ['58101', '58102', '58103'],
                        'Bismarck'     => ['58501', '58503', '58504'],
                        'Grand Forks'  => ['58201', '58202', '58203'],
                    ]],
                    'OH' => ['name' => 'Ohio', 'cities' => [
                        'Columbus'      => ['43201', '43202', '43203'],
                        'Cleveland'     => ['44101', '44102', '44103'],
                        'Cincinnati'    => ['45201', '45202', '45203'],
                        'Toledo'        => ['43601', '43602', '43604'],
                        'Akron'         => ['44301', '44302', '44303'],
                    ]],
                    'OK' => ['name' => 'Oklahoma', 'cities' => [
                        'Oklahoma City' => ['73101', '73102', '73104'],
                        'Tulsa'         => ['74101', '74103', '74104'],
                        'Norman'        => ['73069', '73071', '73072'],
                    ]],
                    'OR' => ['name' => 'Oregon', 'cities' => [
                        'Portland'  => ['97201', '97202', '97203'],
                        'Eugene'    => ['97401', '97402', '97403'],
                        'Salem'     => ['97301', '97302', '97303'],
                        'Bend'      => ['97701', '97702', '97703'],
                    ]],
                    'PA' => ['name' => 'Pennsylvania', 'cities' => [
                        'Philadelphia' => ['19101', '19102', '19103'],
                        'Pittsburgh'   => ['15201', '15212', '15213'],
                        'Allentown'    => ['18101', '18102', '18103'],
                        'Erie'         => ['16501', '16502', '16503'],
                    ]],
                    'RI' => ['name' => 'Rhode Island', 'cities' => [
                        'Providence'  => ['02901', '02903', '02904'],
                        'Warwick'     => ['02886', '02888', '02889'],
                        'Cranston'    => ['02910', '02920'],
                    ]],
                    'SC' => ['name' => 'South Carolina', 'cities' => [
                        'Columbia'    => ['29201', '29203', '29204'],
                        'Charleston'  => ['29401', '29403', '29405'],
                        'Greenville'  => ['29601', '29607', '29609'],
                    ]],
                    'SD' => ['name' => 'South Dakota', 'cities' => [
                        'Sioux Falls' => ['57101', '57103', '57104'],
                        'Rapid City'  => ['57701', '57702', '57703'],
                        'Aberdeen'    => ['57401', '57402'],
                    ]],
                    'TN' => ['name' => 'Tennessee', 'cities' => [
                        'Nashville'   => ['37201', '37203', '37206'],
                        'Memphis'     => ['38101', '38103', '38104'],
                        'Knoxville'   => ['37901', '37902', '37916'],
                        'Chattanooga' => ['37401', '37402', '37403'],
                    ]],
                    'TX' => ['name' => 'Texas', 'cities' => [
                        'Houston'      => ['77001', '77002', '77004'],
                        'Dallas'       => ['75201', '75202', '75204'],
                        'San Antonio'  => ['78201', '78202', '78203'],
                        'Austin'       => ['78701', '78702', '78703'],
                        'Fort Worth'   => ['76101', '76102', '76103'],
                        'El Paso'      => ['79901', '79902', '79903'],
                    ]],
                    'UT' => ['name' => 'Utah', 'cities' => [
                        'Salt Lake City'  => ['84101', '84102', '84103'],
                        'West Valley City' => ['84119', '84120'],
                        'Provo'           => ['84601', '84604', '84606'],
                        'Ogden'           => ['84401', '84403'],
                    ]],
                    'VT' => ['name' => 'Vermont', 'cities' => [
                        'Burlington'   => ['05401', '05402', '05403'],
                        'Montpelier'   => ['05601', '05602'],
                        'Rutland'      => ['05701', '05702'],
                    ]],
                    'VA' => ['name' => 'Virginia', 'cities' => [
                        'Virginia Beach' => ['23451', '23452', '23453'],
                        'Norfolk'        => ['23501', '23502', '23503'],
                        'Richmond'       => ['23218', '23219', '23220'],
                        'Arlington'      => ['22201', '22202', '22203'],
                    ]],
                    'WA' => ['name' => 'Washington', 'cities' => [
                        'Seattle'   => ['98101', '98102', '98103'],
                        'Spokane'   => ['99201', '99202', '99203'],
                        'Tacoma'    => ['98401', '98402', '98403'],
                        'Bellevue'  => ['98004', '98005', '98006'],
                    ]],
                    'WV' => ['name' => 'West Virginia', 'cities' => [
                        'Charleston'  => ['25301', '25302', '25303'],
                        'Huntington'  => ['25701', '25702', '25703'],
                        'Morgantown'  => ['26501', '26502', '26505'],
                    ]],
                    'WI' => ['name' => 'Wisconsin', 'cities' => [
                        'Milwaukee'  => ['53201', '53202', '53203'],
                        'Madison'    => ['53701', '53703', '53704'],
                        'Green Bay'  => ['54301', '54302', '54303'],
                        'Kenosha'    => ['53140', '53142', '53143'],
                    ]],
                    'WY' => ['name' => 'Wyoming', 'cities' => [
                        'Cheyenne'  => ['82001', '82003', '82007'],
                        'Casper'    => ['82601', '82602', '82604'],
                        'Laramie'   => ['82070', '82072'],
                    ]],
                    'DC' => ['name' => 'District of Columbia', 'cities' => [
                        'Washington'  => ['20001', '20002', '20003'],
                    ]],
                ],
            ],
            'CA' => [
                'name'       => 'Canada',
                'phone_code' => '+1',
                'states'     => [
                    'AB' => ['name' => 'Alberta', 'cities' => [
                        'Calgary'    => ['T2G 0A1', 'T2H 0A1', 'T2P 0A1'],
                        'Edmonton'   => ['T5H 0A1', 'T5J 0A1', 'T5K 0A1'],
                        'Red Deer'   => ['T4N 0A1', 'T4P 0A1'],
                    ]],
                    'BC' => ['name' => 'British Columbia', 'cities' => [
                        'Vancouver'  => ['V5K 0A1', 'V5L 0A1', 'V5M 0A1'],
                        'Victoria'   => ['V8V 0A1', 'V8W 0A1'],
                        'Surrey'     => ['V3R 0A1', 'V3S 0A1', 'V3T 0A1'],
                        'Burnaby'    => ['V5A 0A1', 'V5B 0A1', 'V5C 0A1'],
                    ]],
                    'MB' => ['name' => 'Manitoba', 'cities' => [
                        'Winnipeg'   => ['R2C 0A1', 'R2H 0A1', 'R2J 0A1'],
                        'Brandon'    => ['R7A 0A1', 'R7B 0A1'],
                    ]],
                    'NB' => ['name' => 'New Brunswick', 'cities' => [
                        'Moncton'       => ['E1C 0A1', 'E1E 0A1'],
                        'Saint John'    => ['E2K 0A1', 'E2L 0A1'],
                        'Fredericton'   => ['E3A 0A1', 'E3B 0A1'],
                    ]],
                    'NL' => ['name' => 'Newfoundland and Labrador', 'cities' => [
                        'St. John\'s'   => ['A1A 0A1', 'A1B 0A1', 'A1C 0A1'],
                        'Corner Brook'  => ['A2H 0A1', 'A2H 5T3'],
                    ]],
                    'NS' => ['name' => 'Nova Scotia', 'cities' => [
                        'Halifax'    => ['B3H 0A1', 'B3J 0A1', 'B3K 0A1'],
                        'Dartmouth'  => ['B2W 0A1', 'B2X 0A1'],
                        'Sydney'     => ['B1P 0A1', 'B1S 0A1'],
                    ]],
                    'ON' => ['name' => 'Ontario', 'cities' => [
                        'Toronto'       => ['M4B 0A1', 'M4C 0A1', 'M5A 0A1'],
                        'Ottawa'        => ['K1A 0A1', 'K1B 0A1', 'K1P 0A1'],
                        'Mississauga'   => ['L4T 0A1', 'L4W 0A1', 'L5A 0A1'],
                        'Hamilton'      => ['L8E 0A1', 'L8H 0A1', 'L8N 0A1'],
                        'London'        => ['N5W 0A1', 'N5X 0A1', 'N5Y 0A1'],
                    ]],
                    'PE' => ['name' => 'Prince Edward Island', 'cities' => [
                        'Charlottetown'  => ['C1A 0A1', 'C1B 0A1'],
                        'Summerside'     => ['C1N 0A1', 'C1N 3K6'],
                    ]],
                    'QC' => ['name' => 'Quebec', 'cities' => [
                        'Montreal'      => ['H2G 0A1', 'H2H 0A1', 'H2Y 0A1'],
                        'Quebec City'   => ['G1K 0A1', 'G1R 0A1', 'G1S 0A1'],
                        'Laval'         => ['H7A 0A1', 'H7B 0A1', 'H7C 0A1'],
                        'Gatineau'      => ['J8P 0A1', 'J8T 0A1', 'J8X 0A1'],
                    ]],
                    'SK' => ['name' => 'Saskatchewan', 'cities' => [
                        'Regina'     => ['S4P 0A1', 'S4R 0A1', 'S4S 0A1'],
                        'Saskatoon'  => ['S7H 0A1', 'S7J 0A1', 'S7K 0A1'],
                        'Prince Albert' => ['S6V 0A1', 'S6W 0A1'],
                    ]],
                ],
            ],
        ];
    }
}
