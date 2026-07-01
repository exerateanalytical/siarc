<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssociationsSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('associations')->count() > 0) return;

        $douala   = DB::table('regions')->where('name_en','like','%Littoral%')->value('id');
        $yaounde  = DB::table('regions')->where('name_en','like','%Centre%')->value('id');
        $northwest= DB::table('regions')->where('name_en','like','%North West%')->value('id');
        $west     = DB::table('regions')->where('name_en','like','%West%')->where('name_en','not like','%North%')->where('name_en','not like','%South%')->value('id');

        $associations = [
            [
                'slug'=>'gicam','name_en'=>'Groupement Inter-patronal du Cameroun','name_fr'=>'Groupement Inter-patronal du Cameroun',
                'acronym'=>'GICAM','sector'=>'commerce',
                'description_en'=>"Cameroon's leading employers' federation, GICAM has represented the interests of private businesses since 1957. With over 500 member companies across all sectors, it is the primary voice of Cameroon's private sector in dialogue with government, trade unions, and international partners. GICAM advocates for a competitive business environment, promotes investment, and provides services including legal advice, training, and networking opportunities to its members.",
                'description_fr'=>"La principale fédération patronale du Cameroun, le GICAM représente les intérêts des entreprises privées depuis 1957.",
                'website'=>'https://www.gicam.org','email'=>'contact@gicam.org','phone'=>'+237 233 425 522',
                'city'=>'Douala','region_id'=>$douala,'is_featured'=>true,'member_count'=>500,'founded_year'=>1957,
            ],
            [
                'slug'=>'ccima','name_en'=>'Cameroon Chamber of Commerce, Industry, Mines and Crafts','name_fr'=>'Chambre de Commerce, d\'Industrie, des Mines et de l\'Artisanat',
                'acronym'=>'CCIMA','sector'=>'commerce',
                'description_en'=>'The CCIMA is a public institution under the Ministry of Commerce that promotes trade and industry in Cameroon. It provides services to businesses including registration, certification, arbitration, training and trade information. It also manages the commercial registry and issues certificates of origin for exported goods.',
                'description_fr'=>'La CCIMA est un établissement public sous la tutelle du Ministère du Commerce qui promeut le commerce et l\'industrie au Cameroun.',
                'website'=>'http://www.ccima.cm','email'=>'ccima@ccima.cm','phone'=>'+237 233 422 816',
                'city'=>'Douala','region_id'=>$douala,'is_featured'=>true,'member_count'=>null,'founded_year'=>1921,
            ],
            [
                'slug'=>'syndustricam','name_en'=>'Syndicate of Industrialists of Cameroon','name_fr'=>'Syndicat des Industriels du Cameroun',
                'acronym'=>'SYNDUSTRICAM','sector'=>'industry',
                'description_en'=>'SYNDUSTRICAM brings together the major industrial enterprises of Cameroon. It represents manufacturers across agri-food, chemicals, plastics, metals, construction materials, and other industrial sectors. The syndicate advocates for an enabling environment for industrial development, including access to electricity, infrastructure, and skilled labour.',
                'description_fr'=>'Le SYNDUSTRICAM regroupe les principales entreprises industrielles du Cameroun.',
                'city'=>'Douala','region_id'=>$douala,'member_count'=>120,'founded_year'=>1985,
            ],
            [
                'slug'=>'apeccam','name_en'=>'Cameroonian Professional Exporters Association','name_fr'=>'Association Professionnelle des Exportateurs Camerounais',
                'acronym'=>'APECCAM','sector'=>'commerce',
                'description_en'=>'APECCAM represents Cameroonian exporters and promotes the development of export activities. It provides members with market intelligence, export readiness training, trade mission support, and advocacy for export-friendly policies. The association works closely with customs, MINCOMMERCE, and international trade bodies.',
                'city'=>'Douala','region_id'=>$douala,'member_count'=>80,'founded_year'=>1994,
            ],
            [
                'slug'=>'gfac','name_en'=>'Groupement des Femmes d\'Affaires du Cameroun','name_fr'=>'Groupement des Femmes d\'Affaires du Cameroun',
                'acronym'=>'GFAC','sector'=>'commerce',
                'description_en'=>'The GFAC supports women entrepreneurs in Cameroon through networking, capacity building, and advocacy. It promotes access to finance, markets, and technology for women-owned businesses. The groupement organises trade fairs, mentorship programmes, and partnerships with development finance institutions to empower women in business.',
                'city'=>'Yaoundé','region_id'=>$yaounde,'member_count'=>350,'founded_year'=>1989,
            ],
            [
                'slug'=>'mecam','name_en'=>'Mouvement des Entreprises du Cameroun','name_fr'=>'Mouvement des Entreprises du Cameroun',
                'acronym'=>'MECAM','sector'=>'commerce',
                'description_en'=>'MECAM is a federation of Cameroonian enterprises focused on promoting local entrepreneurship and SME development. It advocates for simplified business regulations, access to credit, and market linkages for small and medium enterprises across all regions of Cameroon.',
                'city'=>'Yaoundé','region_id'=>$yaounde,'member_count'=>200,'founded_year'=>2002,
            ],
            [
                'slug'=>'aprocam','name_en'=>'Association of Cocoa Producers of Cameroon','name_fr'=>'Association des Producteurs de Cacao du Cameroun',
                'acronym'=>'APROCAM','sector'=>'agriculture',
                'description_en'=>'APROCAM represents cocoa farmers and producers across Cameroon, particularly in the South West, Littoral, and Centre regions. It advocates for fair prices, access to inputs and finance, and capacity building for farmers. The association coordinates with government and buyers to improve quality standards and traceability in the Cameroonian cocoa supply chain.',
                'city'=>'Douala','region_id'=>$douala,'member_count'=>15000,'founded_year'=>1999,
            ],
            [
                'slug'=>'ubac','name_en'=>'Union of Bankers of Cameroon','name_fr'=>'Union des Banques du Cameroun',
                'acronym'=>'UBAC','sector'=>'finance',
                'description_en'=>'UBAC brings together the commercial banks operating in Cameroon. It serves as the industry body for banking, coordinates positions on monetary policy, financial regulation, and fintech development. It works with the COBAC (CEMAC banking regulator) and the BEAC central bank on sector issues.',
                'city'=>'Yaoundé','region_id'=>$yaounde,'member_count'=>18,'founded_year'=>1990,
            ],
            [
                'slug'=>'aptica','name_en'=>'Cameroonian ICT & Digital Professionals Association','name_fr'=>'Association des Professionnels des TIC et de l\'Audiovisuel du Cameroun',
                'acronym'=>'APTICA','sector'=>'ict',
                'description_en'=>'APTICA represents ICT companies, software developers, telecoms providers, and digital entrepreneurs in Cameroon. It promotes digital transformation, cybersecurity, and the growth of the tech ecosystem. The association organises hackathons, training bootcamps, and advocates for a favourable regulatory environment for digital businesses.',
                'city'=>'Yaoundé','region_id'=>$yaounde,'member_count'=>180,'founded_year'=>2010,
            ],
            [
                'slug'=>'citca','name_en'=>'Cameroon International Trade and Commerce Association','name_fr'=>'Association du Commerce International et des Affaires du Cameroun',
                'acronym'=>'CITCA','sector'=>'commerce',
                'description_en'=>'CITCA promotes international trade relationships for Cameroonian businesses. It facilitates market access to Europe, Asia, and the Americas, organises trade delegations, and provides expertise in import/export regulations, trade finance, and cross-border compliance.',
                'city'=>'Douala','region_id'=>$douala,'member_count'=>95,'founded_year'=>2005,
            ],
            [
                'slug'=>'camconstruct','name_en'=>'Cameroon Construction Industry Federation','name_fr'=>'Fédération des Industriels de la Construction du Cameroun',
                'acronym'=>'FEDERCAM','sector'=>'construction',
                'description_en'=>'The Cameroon Construction Industry Federation represents contractors, civil engineers, architects, real estate developers, and building materials manufacturers. It advocates for transparent public procurement, quality standards in construction, and access to infrastructure contracts for local companies.',
                'city'=>'Yaoundé','region_id'=>$yaounde,'member_count'=>240,'founded_year'=>1975,
            ],
            [
                'slug'=>'camagroservices','name_en'=>'Cameroon Agribusiness Services Federation','name_fr'=>'Fédération des Services Agro-industriels du Cameroun',
                'acronym'=>'FASCA','sector'=>'agri-food',
                'description_en'=>'FASCA brings together agribusiness companies across the cocoa, coffee, palm oil, rubber, and banana value chains. It promotes agro-industrial investments, value-added processing, and export competitiveness. The federation connects farmers, processors, exporters, and equipment suppliers across all agricultural sectors.',
                'city'=>'Douala','region_id'=>$douala,'member_count'=>310,'founded_year'=>1996,
            ],
            [
                'slug'=>'cameroon-transport-employers','name_en'=>'Cameroon Transport Employers Federation','name_fr'=>'Fédération des Employeurs du Transport du Cameroun',
                'acronym'=>'FETCA','sector'=>'transport',
                'description_en'=>'FETCA represents road freight, passenger transport, logistics, and port handling companies. It advocates for road infrastructure investment, transport regulation reform, and professional standards in the transport sector. The federation also coordinates with CAMRAIL and port authorities on multimodal logistics issues.',
                'city'=>'Douala','region_id'=>$douala,'member_count'=>420,'founded_year'=>1968,
            ],
            [
                'slug'=>'cameroon-pharma-association','name_en'=>'Cameroon Pharmaceutical Companies Association','name_fr'=>'Association des Sociétés Pharmaceutiques du Cameroun',
                'acronym'=>'ASPHACAM','sector'=>'health',
                'description_en'=>'ASPHACAM represents pharmaceutical importers, distributors, and manufacturers in Cameroon. It works to ensure quality, safety, and affordability of medicines, and advocates for local pharmaceutical manufacturing. The association coordinates with LANACOME (national medicines laboratory) and the Ministry of Health on drug regulation.',
                'city'=>'Yaoundé','region_id'=>$yaounde,'member_count'=>65,'founded_year'=>2001,
            ],
            [
                'slug'=>'cameroon-hotel-tourism','name_en'=>'Cameroon Hotel and Tourism Industry Association','name_fr'=>'Association de l\'Industrie Hôtelière et Touristique du Cameroun',
                'acronym'=>'CAHTIA','sector'=>'tourism',
                'description_en'=>'CAHTIA represents hotels, lodges, tour operators, event companies, and travel agencies in Cameroon. It promotes Cameroon as a tourism destination, advocates for tourism infrastructure investment, and provides training for hospitality professionals. The association works with the Ministry of Tourism and the MINTOUL on sector development.',
                'city'=>'Yaoundé','region_id'=>$yaounde,'member_count'=>130,'founded_year'=>1988,
            ],
            [
                'slug'=>'cameroon-energy-association','name_en'=>'Cameroon Energy and Mining Professionals Association','name_fr'=>'Association des Professionnels de l\'Énergie et des Mines du Cameroun',
                'acronym'=>'CEMAP','sector'=>'energy',
                'description_en'=>'CEMAP brings together energy companies, mining operators, oil and gas service providers, and renewable energy developers. It advocates for an enabling regulatory environment, local content requirements, and skills development in the energy and extractive industries sector.',
                'city'=>'Yaoundé','region_id'=>$yaounde,'member_count'=>75,'founded_year'=>2008,
            ],
            [
                'slug'=>'northwest-business-forum','name_en'=>'North West Business Forum','name_fr'=>'Forum des Affaires du Nord-Ouest',
                'acronym'=>'NWBF','sector'=>'commerce',
                'description_en'=>'The North West Business Forum represents businesses in the North West Region of Cameroon. It promotes trade, investment, and economic development in the region, with a particular focus on agriculture, crafts, and small business development in Bamenda and surrounding areas.',
                'city'=>'Bamenda','region_id'=>$northwest,'member_count'=>180,'founded_year'=>2003,
            ],
            [
                'slug'=>'cameroon-insurance-association','name_en'=>'Cameroon Insurance Companies Association','name_fr'=>'Association des Sociétés d\'Assurances du Cameroun',
                'acronym'=>'ASAC','sector'=>'finance',
                'description_en'=>'ASAC represents life, non-life, and reinsurance companies operating in Cameroon. It coordinates with the CIMA regulator, advocates for market development, and promotes insurance awareness. The association provides a framework for industry self-regulation and professional standards.',
                'city'=>'Yaoundé','region_id'=>$yaounde,'member_count'=>22,'founded_year'=>1994,
            ],
            [
                'slug'=>'cameroon-lawyers-bar','name_en'=>'Cameroon Bar Association','name_fr'=>'Barreau du Cameroun',
                'acronym'=>'BdC','sector'=>'legal',
                'description_en'=>'The Cameroon Bar Association regulates the legal profession and represents lawyers across the country. For businesses, the Bar provides access to qualified legal practitioners for corporate law, contract drafting, dispute resolution, and regulatory compliance. It operates in both Common Law (Anglophone) and Civil Law (Francophone) traditions.',
                'city'=>'Yaoundé','region_id'=>$yaounde,'member_count'=>2800,'founded_year'=>1959,
            ],
            [
                'slug'=>'young-entrepreneurs-cameroon','name_en'=>'Young Entrepreneurs of Cameroon Network','name_fr'=>'Réseau des Jeunes Entrepreneurs du Cameroun',
                'acronym'=>'RJEC','sector'=>'commerce',
                'description_en'=>'RJEC supports young business owners aged 18–40 across Cameroon with mentorship, training, access to finance, and networking. The network runs incubation programmes, pitch competitions, and accelerators to help young entrepreneurs scale their businesses and access markets.',
                'city'=>'Douala','region_id'=>$douala,'member_count'=>600,'founded_year'=>2012,
            ],
        ];

        foreach ($associations as $a) {
            DB::table('associations')->insertOrIgnore(array_merge($a, [
                'is_active' => 1,
                'is_featured' => $a['is_featured'] ?? false,
                'view_count' => rand(50,500),
                'created_at' => now()->subDays(rand(30,365)),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('20 Cameroonian associations seeded.');
    }
}
