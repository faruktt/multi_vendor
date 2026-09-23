<?php

namespace App\Support;

class BangladeshLocations
{
    /**
     * All 64 districts of Bangladesh and their corresponding Upazilas / Thanas.
     */
    public static function all(): array
    {
        return [
            // ── Dhaka Division ──────────────────────────────────────────────
            'Dhaka' => [
                'Adabor', 'Badda', 'Bangshal', 'Biman Bandar', 'Cantonment', 'Chowkbazar', 'Dakshinkhan', 
                'Darus Salam', 'Demra', 'Dhamrai', 'Dhanmondi', 'Dohar', 'Gendaria', 'Gulshan', 'Hazaribagh', 
                'Jatrabari', 'Kadamtali', 'Kafrul', 'Kalabagan', 'Kamrangirchar', 'Keraniganj', 'Khilgaon', 
                'Khilkhet', 'Kotwali', 'Lalbagh', 'Mirpur', 'Mohammadpur', 'Motijheel', 'Mughda', 'Nawabganj', 
                'New Market', 'Pallabi', 'Paltan', 'Ramna', 'Rampura', 'Sabujbagh', 'Savar', 'Shah Ali', 
                'Shahbagh', 'Sher-e-Bangla Nagar', 'Shyampur', 'Sutrapur', 'Tejgaon', 'Tejgaon Industrial Area', 
                'Turag', 'Uttar Khan', 'Uttara East', 'Uttara West', 'Vatara', 'Wari'
            ],
            'Gazipur' => [
                'Gazipur Sadar', 'Tongi', 'Kaliakair', 'Kapasia', 'Sreepur', 'Kaliganj'
            ],
            'Narayanganj' => [
                'Narayanganj Sadar', 'Bandar', 'Fatullah', 'Siddhirganj', 'Rupganj', 'Araihazar', 'Sonargaon'
            ],
            'Munshiganj' => [
                'Munshiganj Sadar', 'Sreenagar', 'Sirajdikhan', 'Tongibari', 'Louhajang', 'Gazaria'
            ],
            'Narsingdi' => [
                'Narsingdi Sadar', 'Belabo', 'Monohardi', 'Palash', 'Raipura', 'Shibpur'
            ],
            'Manikganj' => [
                'Manikganj Sadar', 'Singair', 'Shibalaya', 'Saturia', 'Harirampur', 'Ghior', 'Daulatpur'
            ],
            'Tangail' => [
                'Tangail Sadar', 'Basail', 'Bhuapur', 'Delduar', 'Ghatail', 'Gopalpur', 'Kalihati', 'Madhupur', 'Mirzapur', 'Nagarpur', 'Sakhipur', 'Dhanbari'
            ],
            'Faridpur' => [
                'Faridpur Sadar', 'Boalmari', 'Alfadanga', 'Madhukhali', 'Bhanga', 'Nagarkanda', 'Charbhadrasan', 'Sadarpur', 'Saltha'
            ],
            'Gopalganj' => [
                'Gopalganj Sadar', 'Kashiani', 'Kotalipara', 'Muksudpur', 'Tungipara'
            ],
            'Madaripur' => [
                'Madaripur Sadar', 'Kalkini', 'Rajoir', 'Shibchar', 'Dasar'
            ],
            'Rajbari' => [
                'Rajbari Sadar', 'Goalanda', 'Pangsha', 'Baliakandi', 'Kalukhali'
            ],
            'Shariatpur' => [
                'Shariatpur Sadar', 'Damudya', 'Naria', 'Janjira', 'Bhedarganj', 'Gosairhat'
            ],
            'Kishoreganj' => [
                'Kishoreganj Sadar', 'Bhairab', 'Bajitpur', 'Hossainpur', 'Itna', 'Karimganj', 'Katiadi', 'Kuliarchar', 'Mithamain', 'Nikli', 'Pakundia', 'Tarail', 'Ashtagram'
            ],

            // ── Chittagong Division ──────────────────────────────────────────
            'Chattogram' => [
                'Agrabad', 'Bakalia', 'Bayezid', 'Chandgaon', 'Chittagong Port', 'Double Mooring', 'Halishahar', 'Khulshi', 'Kotwali', 'Pahartali', 'Panchlaish', 'Patenga', 'Anwara', 'Banshkhali', 'Boalkhali', 'Chandanaish', 'Fatikchhari', 'Hathazari', 'Lohagara', 'Mirsharai', 'Patiya', 'Rangunia', 'Raozan', 'Sandwip', 'Satkania', 'Sitakunda', 'Karnafuli'
            ],
            'Cox\'s Bazar' => [
                'Cox\'s Bazar Sadar', 'Chakaria', 'Maheshkhali', 'Ramu', 'Teknaf', 'Ukhia', 'Pekua', 'Kutubdia', 'Eidgaon'
            ],
            'Cumilla' => [
                'Cumilla Adarsha Sadar', 'Cumilla Sadar Dakshin', 'Barura', 'Brahmanpara', 'Burichang', 'Chandina', 'Chauddagram', 'Daudkandi', 'Debidwar', 'Homna', 'Laksam', 'Muradnagar', 'Nangalkot', 'Meghna', 'Titas', 'Monohargonj', 'Lalmai'
            ],
            'Feni' => [
                'Feni Sadar', 'Chhagalnaiya', 'Daganbhuiyan', 'Parshuram', 'Fulgazi', 'Sonagazi'
            ],
            'Brahmanbaria' => [
                'Brahmanbaria Sadar', 'Ashuganj', 'Nasirnagar', 'Nabinagar', 'Sarail', 'Kasba', 'Akhaura', 'Bancharampur', 'Bijoynagar'
            ],
            'Noakhali' => [
                'Noakhali Sadar', 'Begumganj', 'Chatkhil', 'Companiganj', 'Hatiya', 'Senbagh', 'Subarnachar', 'Kabirhat', 'Sonaimuri'
            ],
            'Chandpur' => [
                'Chandpur Sadar', 'Faridganj', 'Haimchar', 'Haziganj', 'Kachua', 'Matlab Dakshin', 'Matlab Uttar', 'Shahrasti'
            ],
            'Lakshmipur' => [
                'Lakshmipur Sadar', 'Raipur', 'Ramganj', 'Ramgati', 'Kamalnagar'
            ],
            'Rangamati' => [
                'Rangamati Sadar', 'Belaichhari', 'Bagaichhari', 'Barkal', 'Juraichhari', 'Kaptai', 'Kawkhali', 'Langadu', 'Naniarchar', 'Rajasthali'
            ],
            'Khagrachhari' => [
                'Khagrachhari Sadar', 'Dighinala', 'Lakshmichhari', 'Mahalchhari', 'Manikchhari', 'Matiranga', 'Panchhari', 'Ramgarh', 'Guimara'
            ],
            'Bandarban' => [
                'Bandarban Sadar', 'Ali Kadam', 'Lama', 'Naikhongchhari', 'Rowangchhari', 'Ruma', 'Thanchi'
            ],

            // ── Sylhet Division ──────────────────────────────────────────────
            'Sylhet' => [
                'Sylhet Sadar', 'Beanibazar', 'Bishwanath', 'Dakshin Surma', 'Fenchuganj', 'Golapganj', 'Gowainghat', 'Jaintiapur', 'Kanaighat', 'Companiganj', 'Zakiganj', 'Osmani Nagar'
            ],
            'Moulvibazar' => [
                'Moulvibazar Sadar', 'Barlekha', 'Juri', 'Kamalganj', 'Kulaura', 'Rajnagar', 'Sreemangal'
            ],
            'Habiganj' => [
                'Habiganj Sadar', 'Ajmiriganj', 'Bahubal', 'Baniachong', 'Chunarughat', 'Lakhai', 'Madhabpur', 'Nabiganj', 'Sayestaganj'
            ],
            'Sunamganj' => [
                'Sunamganj Sadar', 'Bishwamvarpur', 'Chhatak', 'Derai', 'Dharampasha', 'Dowarabazar', 'Jagannathpur', 'Jamalganj', 'Shantiganj', 'Tahirpur', 'Madhyanagar'
            ],

            // ── Rajshahi Division ────────────────────────────────────────────
            'Rajshahi' => [
                'Boalia', 'Motihar', 'Rajpara', 'Shah Makhdum', 'Bagha', 'Bagmara', 'Charghat', 'Durgapur', 'Godagari', 'Mohanpur', 'Paba', 'Puthia', 'Tanore'
            ],
            'Bogura' => [
                'Bogura Sadar', 'Adamdighi', 'Dhunat', 'Dhupchanchia', 'Gabtali', 'Kahaloo', 'Nandigram', 'Sariakandi', 'Shajahanpur', 'Sherpur', 'Shibganj', 'Sonatola'
            ],
            'Pabna' => [
                'Pabna Sadar', 'Atgharia', 'Bera', 'Bhangura', 'Chatmohar', 'Faridpur', 'Ishwardi', 'Santhia', 'Sujanagar'
            ],
            'Sirajganj' => [
                'Sirajganj Sadar', 'Belkuchi', 'Chauhali', 'Kamarkhanda', 'Kazipur', 'Raiganj', 'Shahjadpur', 'Tarash', 'Ullapara'
            ],
            'Naogaon' => [
                'Naogaon Sadar', 'Atrai', 'Badalgachhi', 'Dhamoirhat', 'Manda', 'Mohadevpur', 'Niamatpur', 'Patnitala', 'Porsha', 'Raninagar', 'Sapahar'
            ],
            'Natore' => [
                'Natore Sadar', 'Bagatipara', 'Baraigram', 'Gurudaspur', 'Lalpur', 'Singra', 'Naldanga'
            ],
            'Chapainawabganj' => [
                'Chapainawabganj Sadar', 'Bholahat', 'Gomastapur', 'Nachole', 'Shibganj'
            ],
            'Joypurhat' => [
                'Joypurhat Sadar', 'Akkelpur', 'Kalai', 'Khetlal', 'Panchbibi'
            ],

            // ── Khulna Division ──────────────────────────────────────────────
            'Khulna' => [
                'Khulna Sadar', 'Daulatpur', 'Khalishpur', 'Khan Jahan Ali', 'Sonadanga', 'Batiaghata', 'Dacope', 'Dumuria', 'Dighalia', 'Koyra', 'Paikgachha', 'Phultala', 'Rupsha', 'Terokhada'
            ],
            'Jashore' => [
                'Jashore Sadar', 'Abhaynagar', 'Bagherpara', 'Chaugachha', 'Jhikargachha', 'Keshabpur', 'Manirampur', 'Sharsha'
            ],
            'Kushtia' => [
                'Kushtia Sadar', 'Bheramara', 'Daulatpur', 'Khoksa', 'Kumarkhali', 'Mirpur'
            ],
            'Satkhira' => [
                'Satkhira Sadar', 'Assasuni', 'Debhata', 'Kalaroa', 'Kaliganj', 'Shyamnagar', 'Tala'
            ],
            'Bagerhat' => [
                'Bagerhat Sadar', 'Chitalmari', 'Fakirhat', 'Kachua', 'Mollahat', 'Mongla', 'Morrelganj', 'Rampal', 'Sarankhola'
            ],
            'Jhenaidah' => [
                'Jhenaidah Sadar', 'Harinakunda', 'Kaliganj', 'Kotchandpur', 'Maheshpur', 'Shailkupa'
            ],
            'Chuadanga' => [
                'Chuadanga Sadar', 'Alamdanga', 'Damurhuda', 'Jibannagar'
            ],
            'Magura' => [
                'Magura Sadar', 'Mohammadpur', 'Shalikha', 'Sreepur'
            ],
            'Meherpur' => [
                'Meherpur Sadar', 'Gangni', 'Mujibnagar'
            ],
            'Narail' => [
                'Narail Sadar', 'Kalia', 'Lohagara'
            ],

            // ── Barishal Division ────────────────────────────────────────────
            'Barishal' => [
                'Barishal Sadar', 'Agailjhara', 'Babuganj', 'Bakerganj', 'Banaripara', 'Gaurnadi', 'Hizla', 'Mehendiganj', 'Muladi', 'Wazirpur'
            ],
            'Patuakhali' => [
                'Patuakhali Sadar', 'Bauphal', 'Dashmina', 'Galachipa', 'Kalapara', 'Mirzaganj', 'Rangabali', 'Dumki'
            ],
            'Bhola' => [
                'Bhola Sadar', 'Burhanuddin', 'Char Fasson', 'Daulatkhan', 'Lalmohan', 'Manpura', 'Tazumuddin'
            ],
            'Pirojpur' => [
                'Pirojpur Sadar', 'Bhandaria', 'Kawkhali', 'Mathbaria', 'Nazirpur', 'Nesarabad (Swarupkati)', 'Indurkani'
            ],
            'Barguna' => [
                'Barguna Sadar', 'Amtali', 'Bamna', 'Betagi', 'Patharghata', 'Taltali'
            ],
            'Jhalokathi' => [
                'Jhalokathi Sadar', 'Kathalia', 'Nalchity', 'Rajapur'
            ],

            // ── Rangpur Division ────────────────────────────────────────────
            'Rangpur' => [
                'Rangpur Sadar', 'Badarganj', 'Gangachhara', 'Kaunia', 'Mithapukur', 'Pirgachha', 'Pirganj', 'Taraganj'
            ],
            'Dinajpur' => [
                'Dinajpur Sadar', 'Birampur', 'Birganj', 'Biral', 'Bochaganj', 'Chirirbandar', 'Fulbari', 'Ghoraghat', 'Hakimpur', 'Kaharole', 'Khansama', 'Nawabganj', 'Parbatipur'
            ],
            'Gaibandha' => [
                'Gaibandha Sadar', 'Fulchhari', 'Gobindaganj', 'Palashbari', 'Sadullapur', 'Saghata', 'Sundarganj'
            ],
            'Kurigram' => [
                'Kurigram Sadar', 'Bhurungamari', 'Char Rajibpur', 'Chilmari', 'Phulbari', 'Nageshwari', 'Rajarhat', 'Raomari', 'Ulipur'
            ],
            'Lalmonirhat' => [
                'Lalmonirhat Sadar', 'Aditmari', 'Hatibandha', 'Kaliganj', 'Patgram'
            ],
            'Nilphamari' => [
                'Nilphamari Sadar', 'Dimla', 'Domar', 'Jaldhaka', 'Kishoreganj', 'Syedpur'
            ],
            'Panchagarh' => [
                'Panchagarh Sadar', 'Atwari', 'Boda', 'Debiganj', 'Tetulia'
            ],
            'Thakurgaon' => [
                'Thakurgaon Sadar', 'Baliadangi', 'Haripur', 'Pirganj', 'Ranisankail'
            ],

            // ── Mymensingh Division ──────────────────────────────────────────
            'Mymensingh' => [
                'Mymensingh Sadar', 'Bhaluka', 'Dhobaura', 'Fulbaria', 'Gaffargaon', 'Gauripur', 'Haluaghat', 'Ishwarganj', 'Muktagachha', 'Nandail', 'Phulpur', 'Trishal', 'Tara Khanda'
            ],
            'Jamalpur' => [
                'Jamalpur Sadar', 'Bakshiganj', 'Dewanganj', 'Islampur', 'Madarganj', 'Melandaha', 'Sarishabari'
            ],
            'Netrokona' => [
                'Netrokona Sadar', 'Atpara', 'Barhatta', 'Durgapur', 'Khaliajuri', 'Kalmakanda', 'Kendua', 'Madan', 'Mohanganj', 'Purbadhala'
            ],
            'Sherpur' => [
                'Sherpur Sadar', 'Jhenaigati', 'Nakla', 'Nalitabari', 'Sreebardi'
            ],
        ];
    }

    /**
     * Get list of all 64 district names sorted alphabetically.
     */
    public static function districts(): array
    {
        $districts = array_keys(static::all());
        sort($districts);
        return $districts;
    }

    /**
     * Get all thanas for a specific district.
     */
    public static function thanasFor(?string $district): array
    {
        if (empty($district)) {
            return [];
        }

        $all = static::all();

        // Direct match
        if (isset($all[$district])) {
            return $all[$district];
        }

        // Case-insensitive match
        foreach ($all as $d => $thanas) {
            if (strcasecmp($d, $district) === 0) {
                return $thanas;
            }
        }

        return [];
    }

    /**
     * Common Sub-Dhaka districts default list.
     */
    public static function defaultSubDhakaDistricts(): array
    {
        return ['Gazipur', 'Narayanganj', 'Munshiganj', 'Narsingdi', 'Manikganj'];
    }

    /**
     * Default Sub-Dhaka thanas grouped by district.
     * In Dhaka district: outer/suburb thanas (Savar, Dhamrai, Keraniganj, Nawabganj, Dohar).
     * Adjacent districts: all their respective thanas.
     */
    public static function defaultSubDhakaThanas(): array
    {
        $all = static::all();
        return [
            'Dhaka'       => ['Savar', 'Dhamrai', 'Keraniganj', 'Nawabganj', 'Dohar'],
            'Gazipur'     => $all['Gazipur'] ?? [],
            'Narayanganj' => $all['Narayanganj'] ?? [],
            'Munshiganj'  => $all['Munshiganj'] ?? [],
            'Narsingdi'   => $all['Narsingdi'] ?? [],
            'Manikganj'   => $all['Manikganj'] ?? [],
        ];
    }

    /**
     * Determine delivery zone ('inside', 'sub_dhaka', 'outside') based on district and thana.
     */
    public static function determineZone(?string $district, ?string $thana, ?array $subDhakaThanas = null): string
    {
        $district = trim($district ?? '');
        $thana = trim($thana ?? '');

        if (empty($district)) {
            return 'outside';
        }

        if ($subDhakaThanas === null) {
            $subDhakaThanas = static::defaultSubDhakaThanas();
        }

        // Check if matching thana exists under the district in subDhakaThanas
        if (!empty($thana)) {
            foreach ($subDhakaThanas as $distKey => $thanas) {
                if (strcasecmp($distKey, $district) === 0 && is_array($thanas)) {
                    foreach ($thanas as $t) {
                        if (strcasecmp($t, $thana) === 0) {
                            return 'sub_dhaka';
                        }
                    }
                }
            }
        }

        // If district is Dhaka and thana is not in sub_dhaka, it is inside Dhaka
        if (strcasecmp($district, 'Dhaka') === 0) {
            return 'inside';
        }

        return 'outside';
    }

    /**
     * Check if a specific district + thana belongs to Sub-Dhaka.
     */
    public static function isSubDhakaThana(?string $district, ?string $thana, ?array $subDhakaThanas = null): bool
    {
        return static::determineZone($district, $thana, $subDhakaThanas) === 'sub_dhaka';
    }
}
