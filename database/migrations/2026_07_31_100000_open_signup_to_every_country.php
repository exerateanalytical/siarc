<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Opens signup to the whole world for buyers, and to all of Africa for sellers.
 *
 * The platform shipped with three countries because those were the three it had
 * regions for. That conflated two different questions: where a member may be
 * from, and where the platform has mapped administrative divisions. A buyer in
 * Tokyo needs neither a region nor a shop — only a country to put on their
 * account — so there was never a reason to refuse them.
 *
 * `continent` is a fact about the country. `seller_enabled` is a policy on top
 * of it, kept as its own column so the rule can be changed for one country
 * without lying about its geography.
 *
 * The three launch countries are NOT overwritten: their names and currency
 * symbols were curated by hand ('FCFA' rather than ICU's 'F CFA', 'Ivory Coast'
 * rather than ICU's 'Côte d’Ivoire' for English) and they keep sort_order 1-3
 * so they stay at the top of every select.
 *
 * Country names, currency codes and symbols in the list below were generated
 * from ICU 72.1 rather than typed by hand; ISO alpha-3 and E.164 dial codes
 * were hand-entered and validated for shape and uniqueness.
 */
return new class extends Migration
{
    /** code, code3, name_fr, name_en, dial, currency, symbol, flag, continent, lang, seller_enabled, sort */
    private const COUNTRIES = [
            ['AF', 'AFG', 'Afghanistan', 'Afghanistan', '93', 'AFN', 'AFN', '🇦🇫', 'AS', 'fr', false, 100],
            ['ZA', 'ZAF', 'Afrique du Sud', 'South Africa', '27', 'ZAR', 'R', '🇿🇦', 'AF', 'en', true, 101],
            ['AL', 'ALB', 'Albanie', 'Albania', '355', 'ALL', 'ALL', '🇦🇱', 'EU', 'fr', false, 102],
            ['DZ', 'DZA', 'Algérie', 'Algeria', '213', 'DZD', 'DZD', '🇩🇿', 'AF', 'fr', true, 3],
            ['DE', 'DEU', 'Allemagne', 'Germany', '49', 'EUR', '€', '🇩🇪', 'EU', 'fr', false, 103],
            ['AD', 'AND', 'Andorre', 'Andorra', '376', 'EUR', '€', '🇦🇩', 'EU', 'fr', false, 104],
            ['AO', 'AGO', 'Angola', 'Angola', '244', 'AOA', 'AOA', '🇦🇴', 'AF', 'fr', true, 105],
            ['AG', 'ATG', 'Antigua-et-Barbuda', 'Antigua & Barbuda', '1268', 'XCD', '$', '🇦🇬', 'NA', 'fr', false, 106],
            ['SA', 'SAU', 'Arabie saoudite', 'Saudi Arabia', '966', 'SAR', 'SAR', '🇸🇦', 'AS', 'fr', false, 107],
            ['AR', 'ARG', 'Argentine', 'Argentina', '54', 'ARS', 'ARS', '🇦🇷', 'SA', 'fr', false, 108],
            ['AM', 'ARM', 'Arménie', 'Armenia', '374', 'AMD', 'AMD', '🇦🇲', 'AS', 'fr', false, 109],
            ['AW', 'ABW', 'Aruba', 'Aruba', '297', 'AWG', 'AWG', '🇦🇼', 'NA', 'fr', false, 110],
            ['AU', 'AUS', 'Australie', 'Australia', '61', 'AUD', '$', '🇦🇺', 'OC', 'en', false, 111],
            ['AT', 'AUT', 'Autriche', 'Austria', '43', 'EUR', '€', '🇦🇹', 'EU', 'fr', false, 112],
            ['AZ', 'AZE', 'Azerbaïdjan', 'Azerbaijan', '994', 'AZN', 'AZN', '🇦🇿', 'AS', 'fr', false, 113],
            ['BS', 'BHS', 'Bahamas', 'Bahamas', '1242', 'BSD', '$', '🇧🇸', 'NA', 'fr', false, 114],
            ['BH', 'BHR', 'Bahreïn', 'Bahrain', '973', 'BHD', 'BHD', '🇧🇭', 'AS', 'fr', false, 115],
            ['BD', 'BGD', 'Bangladesh', 'Bangladesh', '880', 'BDT', 'BDT', '🇧🇩', 'AS', 'fr', false, 116],
            ['BB', 'BRB', 'Barbade', 'Barbados', '1246', 'BBD', '$', '🇧🇧', 'NA', 'fr', false, 117],
            ['BE', 'BEL', 'Belgique', 'Belgium', '32', 'EUR', '€', '🇧🇪', 'EU', 'fr', false, 118],
            ['BZ', 'BLZ', 'Belize', 'Belize', '501', 'BZD', '$', '🇧🇿', 'NA', 'fr', false, 119],
            ['BM', 'BMU', 'Bermudes', 'Bermuda', '1441', 'BMD', '$', '🇧🇲', 'NA', 'fr', false, 120],
            ['BT', 'BTN', 'Bhoutan', 'Bhutan', '975', 'BTN', 'BTN', '🇧🇹', 'AS', 'fr', false, 121],
            ['BY', 'BLR', 'Biélorussie', 'Belarus', '375', 'BYN', 'BYN', '🇧🇾', 'EU', 'fr', false, 122],
            ['BO', 'BOL', 'Bolivie', 'Bolivia', '591', 'BOB', 'BOB', '🇧🇴', 'SA', 'fr', false, 123],
            ['BA', 'BIH', 'Bosnie-Herzégovine', 'Bosnia & Herzegovina', '387', 'BAM', 'BAM', '🇧🇦', 'EU', 'fr', false, 124],
            ['BW', 'BWA', 'Botswana', 'Botswana', '267', 'BWP', 'P', '🇧🇼', 'AF', 'en', true, 125],
            ['BN', 'BRN', 'Brunei', 'Brunei', '673', 'BND', 'BND', '🇧🇳', 'AS', 'fr', false, 126],
            ['BR', 'BRA', 'Brésil', 'Brazil', '55', 'BRL', 'R$', '🇧🇷', 'SA', 'fr', false, 127],
            ['BG', 'BGR', 'Bulgarie', 'Bulgaria', '359', 'BGN', 'BGN', '🇧🇬', 'EU', 'fr', false, 128],
            ['BF', 'BFA', 'Burkina Faso', 'Burkina Faso', '226', 'XOF', 'F CFA', '🇧🇫', 'AF', 'fr', true, 129],
            ['BI', 'BDI', 'Burundi', 'Burundi', '257', 'BIF', 'FBu', '🇧🇮', 'AF', 'fr', true, 130],
            ['BJ', 'BEN', 'Bénin', 'Benin', '229', 'XOF', 'F CFA', '🇧🇯', 'AF', 'fr', true, 131],
            ['KH', 'KHM', 'Cambodge', 'Cambodia', '855', 'KHR', 'KHR', '🇰🇭', 'AS', 'fr', false, 132],
            ['CM', 'CMR', 'Cameroun', 'Cameroon', '237', 'XAF', 'FCFA', '🇨🇲', 'AF', 'fr', true, 1],
            ['CA', 'CAN', 'Canada', 'Canada', '1', 'CAD', '$', '🇨🇦', 'NA', 'en', false, 133],
            ['CV', 'CPV', 'Cap-Vert', 'Cape Verde', '238', 'CVE', 'CVE', '🇨🇻', 'AF', 'fr', true, 134],
            ['CL', 'CHL', 'Chili', 'Chile', '56', 'CLP', 'CLP', '🇨🇱', 'SA', 'fr', false, 135],
            ['CN', 'CHN', 'Chine', 'China', '86', 'CNY', 'CN¥', '🇨🇳', 'AS', 'fr', false, 136],
            ['CY', 'CYP', 'Chypre', 'Cyprus', '357', 'EUR', '€', '🇨🇾', 'EU', 'fr', false, 137],
            ['CO', 'COL', 'Colombie', 'Colombia', '57', 'COP', 'COP', '🇨🇴', 'SA', 'fr', false, 138],
            ['KM', 'COM', 'Comores', 'Comoros', '269', 'KMF', 'KMF', '🇰🇲', 'AF', 'fr', true, 139],
            ['CG', 'COG', 'Congo-Brazzaville', 'Congo - Brazzaville', '242', 'XAF', 'FCFA', '🇨🇬', 'AF', 'fr', true, 140],
            ['CD', 'COD', 'Congo-Kinshasa', 'Congo - Kinshasa', '243', 'CDF', 'CDF', '🇨🇩', 'AF', 'fr', true, 141],
            ['KP', 'PRK', 'Corée du Nord', 'North Korea', '850', 'KPW', 'KPW', '🇰🇵', 'AS', 'fr', false, 142],
            ['KR', 'KOR', 'Corée du Sud', 'South Korea', '82', 'KRW', '₩', '🇰🇷', 'AS', 'fr', false, 143],
            ['CR', 'CRI', 'Costa Rica', 'Costa Rica', '506', 'CRC', 'CRC', '🇨🇷', 'NA', 'fr', false, 144],
            ['HR', 'HRV', 'Croatie', 'Croatia', '385', 'EUR', '€', '🇭🇷', 'EU', 'fr', false, 145],
            ['CU', 'CUB', 'Cuba', 'Cuba', '53', 'CUP', 'CUP', '🇨🇺', 'NA', 'fr', false, 146],
            ['CW', 'CUW', 'Curaçao', 'Curaçao', '599', 'ANG', 'ANG', '🇨🇼', 'NA', 'fr', false, 147],
            ['CI', 'CIV', 'Côte d’Ivoire', 'Côte d’Ivoire', '225', 'XOF', 'F CFA', '🇨🇮', 'AF', 'fr', true, 2],
            ['DK', 'DNK', 'Danemark', 'Denmark', '45', 'DKK', 'kr.', '🇩🇰', 'EU', 'fr', false, 148],
            ['DJ', 'DJI', 'Djibouti', 'Djibouti', '253', 'DJF', 'DJF', '🇩🇯', 'AF', 'fr', true, 149],
            ['DM', 'DMA', 'Dominique', 'Dominica', '1767', 'XCD', '$', '🇩🇲', 'NA', 'fr', false, 150],
            ['ES', 'ESP', 'Espagne', 'Spain', '34', 'EUR', '€', '🇪🇸', 'EU', 'fr', false, 151],
            ['EE', 'EST', 'Estonie', 'Estonia', '372', 'EUR', '€', '🇪🇪', 'EU', 'fr', false, 152],
            ['SZ', 'SWZ', 'Eswatini', 'Eswatini', '268', 'SZL', 'E', '🇸🇿', 'AF', 'en', true, 153],
            ['FJ', 'FJI', 'Fidji', 'Fiji', '679', 'FJD', '$', '🇫🇯', 'OC', 'fr', false, 154],
            ['FI', 'FIN', 'Finlande', 'Finland', '358', 'EUR', '€', '🇫🇮', 'EU', 'fr', false, 155],
            ['FR', 'FRA', 'France', 'France', '33', 'EUR', '€', '🇫🇷', 'EU', 'fr', false, 156],
            ['GA', 'GAB', 'Gabon', 'Gabon', '241', 'XAF', 'FCFA', '🇬🇦', 'AF', 'fr', true, 157],
            ['GM', 'GMB', 'Gambie', 'Gambia', '220', 'GMD', 'D', '🇬🇲', 'AF', 'en', true, 158],
            ['GH', 'GHA', 'Ghana', 'Ghana', '233', 'GHS', 'GH₵', '🇬🇭', 'AF', 'en', true, 159],
            ['GI', 'GIB', 'Gibraltar', 'Gibraltar', '350', 'GIP', '£', '🇬🇮', 'EU', 'fr', false, 160],
            ['GD', 'GRD', 'Grenade', 'Grenada', '1473', 'XCD', '$', '🇬🇩', 'NA', 'fr', false, 161],
            ['GL', 'GRL', 'Groenland', 'Greenland', '299', 'DKK', 'DKK', '🇬🇱', 'NA', 'fr', false, 162],
            ['GR', 'GRC', 'Grèce', 'Greece', '30', 'EUR', '€', '🇬🇷', 'EU', 'fr', false, 163],
            ['GU', 'GUM', 'Guam', 'Guam', '1671', 'USD', '$', '🇬🇺', 'OC', 'fr', false, 164],
            ['GT', 'GTM', 'Guatemala', 'Guatemala', '502', 'GTQ', 'GTQ', '🇬🇹', 'NA', 'fr', false, 165],
            ['GN', 'GIN', 'Guinée', 'Guinea', '224', 'GNF', 'GNF', '🇬🇳', 'AF', 'fr', true, 166],
            ['GQ', 'GNQ', 'Guinée équatoriale', 'Equatorial Guinea', '240', 'XAF', 'FCFA', '🇬🇶', 'AF', 'fr', true, 167],
            ['GW', 'GNB', 'Guinée-Bissau', 'Guinea-Bissau', '245', 'XOF', 'F CFA', '🇬🇼', 'AF', 'fr', true, 168],
            ['GY', 'GUY', 'Guyana', 'Guyana', '592', 'GYD', '$', '🇬🇾', 'SA', 'fr', false, 169],
            ['GE', 'GEO', 'Géorgie', 'Georgia', '995', 'GEL', 'GEL', '🇬🇪', 'AS', 'fr', false, 170],
            ['HT', 'HTI', 'Haïti', 'Haiti', '509', 'HTG', 'HTG', '🇭🇹', 'NA', 'fr', false, 171],
            ['HN', 'HND', 'Honduras', 'Honduras', '504', 'HNL', 'HNL', '🇭🇳', 'NA', 'fr', false, 172],
            ['HU', 'HUN', 'Hongrie', 'Hungary', '36', 'HUF', 'HUF', '🇭🇺', 'EU', 'fr', false, 173],
            ['IN', 'IND', 'Inde', 'India', '91', 'INR', '₹', '🇮🇳', 'AS', 'en', false, 174],
            ['ID', 'IDN', 'Indonésie', 'Indonesia', '62', 'IDR', 'IDR', '🇮🇩', 'AS', 'fr', false, 175],
            ['IQ', 'IRQ', 'Irak', 'Iraq', '964', 'IQD', 'IQD', '🇮🇶', 'AS', 'fr', false, 176],
            ['IR', 'IRN', 'Iran', 'Iran', '98', 'IRR', 'IRR', '🇮🇷', 'AS', 'fr', false, 177],
            ['IE', 'IRL', 'Irlande', 'Ireland', '353', 'EUR', '€', '🇮🇪', 'EU', 'en', false, 178],
            ['IS', 'ISL', 'Islande', 'Iceland', '354', 'ISK', 'ISK', '🇮🇸', 'EU', 'fr', false, 179],
            ['IL', 'ISR', 'Israël', 'Israel', '972', 'ILS', '₪', '🇮🇱', 'AS', 'fr', false, 180],
            ['IT', 'ITA', 'Italie', 'Italy', '39', 'EUR', '€', '🇮🇹', 'EU', 'fr', false, 181],
            ['JM', 'JAM', 'Jamaïque', 'Jamaica', '1876', 'JMD', '$', '🇯🇲', 'NA', 'fr', false, 182],
            ['JP', 'JPN', 'Japon', 'Japan', '81', 'JPY', '¥', '🇯🇵', 'AS', 'fr', false, 183],
            ['JO', 'JOR', 'Jordanie', 'Jordan', '962', 'JOD', 'JOD', '🇯🇴', 'AS', 'fr', false, 184],
            ['KZ', 'KAZ', 'Kazakhstan', 'Kazakhstan', '7', 'KZT', 'KZT', '🇰🇿', 'AS', 'fr', false, 185],
            ['KE', 'KEN', 'Kenya', 'Kenya', '254', 'KES', 'Ksh', '🇰🇪', 'AF', 'en', true, 186],
            ['KG', 'KGZ', 'Kirghizstan', 'Kyrgyzstan', '996', 'KGS', 'KGS', '🇰🇬', 'AS', 'fr', false, 187],
            ['KI', 'KIR', 'Kiribati', 'Kiribati', '686', 'AUD', '$', '🇰🇮', 'OC', 'fr', false, 188],
            ['KW', 'KWT', 'Koweït', 'Kuwait', '965', 'KWD', 'KWD', '🇰🇼', 'AS', 'fr', false, 189],
            ['LA', 'LAO', 'Laos', 'Laos', '856', 'LAK', 'LAK', '🇱🇦', 'AS', 'fr', false, 190],
            ['LS', 'LSO', 'Lesotho', 'Lesotho', '266', 'ZAR', 'R', '🇱🇸', 'AF', 'en', true, 191],
            ['LV', 'LVA', 'Lettonie', 'Latvia', '371', 'EUR', '€', '🇱🇻', 'EU', 'fr', false, 192],
            ['LB', 'LBN', 'Liban', 'Lebanon', '961', 'LBP', 'LBP', '🇱🇧', 'AS', 'fr', false, 193],
            ['LR', 'LBR', 'Liberia', 'Liberia', '231', 'LRD', '$', '🇱🇷', 'AF', 'en', true, 194],
            ['LY', 'LBY', 'Libye', 'Libya', '218', 'LYD', 'LYD', '🇱🇾', 'AF', 'fr', true, 195],
            ['LI', 'LIE', 'Liechtenstein', 'Liechtenstein', '423', 'CHF', 'CHF', '🇱🇮', 'EU', 'fr', false, 196],
            ['LT', 'LTU', 'Lituanie', 'Lithuania', '370', 'EUR', '€', '🇱🇹', 'EU', 'fr', false, 197],
            ['LU', 'LUX', 'Luxembourg', 'Luxembourg', '352', 'EUR', '€', '🇱🇺', 'EU', 'fr', false, 198],
            ['MK', 'MKD', 'Macédoine du Nord', 'North Macedonia', '389', 'MKD', 'MKD', '🇲🇰', 'EU', 'fr', false, 199],
            ['MG', 'MDG', 'Madagascar', 'Madagascar', '261', 'MGA', 'Ar', '🇲🇬', 'AF', 'fr', true, 200],
            ['MY', 'MYS', 'Malaisie', 'Malaysia', '60', 'MYR', 'RM', '🇲🇾', 'AS', 'en', false, 201],
            ['MW', 'MWI', 'Malawi', 'Malawi', '265', 'MWK', 'MK', '🇲🇼', 'AF', 'en', true, 202],
            ['MV', 'MDV', 'Maldives', 'Maldives', '960', 'MVR', 'Rf', '🇲🇻', 'AS', 'fr', false, 203],
            ['ML', 'MLI', 'Mali', 'Mali', '223', 'XOF', 'F CFA', '🇲🇱', 'AF', 'fr', true, 204],
            ['MT', 'MLT', 'Malte', 'Malta', '356', 'EUR', '€', '🇲🇹', 'EU', 'fr', false, 205],
            ['MA', 'MAR', 'Maroc', 'Morocco', '212', 'MAD', 'MAD', '🇲🇦', 'AF', 'fr', true, 206],
            ['MU', 'MUS', 'Maurice', 'Mauritius', '230', 'MUR', 'Rs', '🇲🇺', 'AF', 'en', true, 207],
            ['MR', 'MRT', 'Mauritanie', 'Mauritania', '222', 'MRU', 'MRU', '🇲🇷', 'AF', 'fr', true, 208],
            ['MX', 'MEX', 'Mexique', 'Mexico', '52', 'MXN', 'MX$', '🇲🇽', 'NA', 'fr', false, 209],
            ['FM', 'FSM', 'Micronésie', 'Micronesia', '691', 'USD', 'US$', '🇫🇲', 'OC', 'fr', false, 210],
            ['MD', 'MDA', 'Moldavie', 'Moldova', '373', 'MDL', 'MDL', '🇲🇩', 'EU', 'fr', false, 211],
            ['MC', 'MCO', 'Monaco', 'Monaco', '377', 'EUR', '€', '🇲🇨', 'EU', 'fr', false, 212],
            ['MN', 'MNG', 'Mongolie', 'Mongolia', '976', 'MNT', 'MNT', '🇲🇳', 'AS', 'fr', false, 213],
            ['ME', 'MNE', 'Monténégro', 'Montenegro', '382', 'EUR', '€', '🇲🇪', 'EU', 'fr', false, 214],
            ['MZ', 'MOZ', 'Mozambique', 'Mozambique', '258', 'MZN', 'MZN', '🇲🇿', 'AF', 'fr', true, 215],
            ['MM', 'MMR', 'Myanmar (Birmanie)', 'Myanmar (Burma)', '95', 'MMK', 'MMK', '🇲🇲', 'AS', 'fr', false, 216],
            ['NA', 'NAM', 'Namibie', 'Namibia', '264', 'NAD', '$', '🇳🇦', 'AF', 'en', true, 217],
            ['NR', 'NRU', 'Nauru', 'Nauru', '674', 'AUD', '$', '🇳🇷', 'OC', 'fr', false, 218],
            ['NI', 'NIC', 'Nicaragua', 'Nicaragua', '505', 'NIO', 'NIO', '🇳🇮', 'NA', 'fr', false, 219],
            ['NE', 'NER', 'Niger', 'Niger', '227', 'XOF', 'F CFA', '🇳🇪', 'AF', 'fr', true, 220],
            ['NG', 'NGA', 'Nigeria', 'Nigeria', '234', 'NGN', '₦', '🇳🇬', 'AF', 'en', true, 221],
            ['NO', 'NOR', 'Norvège', 'Norway', '47', 'NOK', 'NOK', '🇳🇴', 'EU', 'fr', false, 222],
            ['NC', 'NCL', 'Nouvelle-Calédonie', 'New Caledonia', '687', 'XPF', 'CFPF', '🇳🇨', 'OC', 'fr', false, 223],
            ['NZ', 'NZL', 'Nouvelle-Zélande', 'New Zealand', '64', 'NZD', '$', '🇳🇿', 'OC', 'en', false, 224],
            ['NP', 'NPL', 'Népal', 'Nepal', '977', 'NPR', 'NPR', '🇳🇵', 'AS', 'fr', false, 225],
            ['OM', 'OMN', 'Oman', 'Oman', '968', 'OMR', 'OMR', '🇴🇲', 'AS', 'fr', false, 226],
            ['UG', 'UGA', 'Ouganda', 'Uganda', '256', 'UGX', 'USh', '🇺🇬', 'AF', 'en', true, 227],
            ['UZ', 'UZB', 'Ouzbékistan', 'Uzbekistan', '998', 'UZS', 'UZS', '🇺🇿', 'AS', 'fr', false, 228],
            ['PK', 'PAK', 'Pakistan', 'Pakistan', '92', 'PKR', 'Rs', '🇵🇰', 'AS', 'en', false, 229],
            ['PW', 'PLW', 'Palaos', 'Palau', '680', 'USD', 'US$', '🇵🇼', 'OC', 'fr', false, 230],
            ['PA', 'PAN', 'Panama', 'Panama', '507', 'PAB', 'PAB', '🇵🇦', 'NA', 'fr', false, 231],
            ['PG', 'PNG', 'Papouasie-Nouvelle-Guinée', 'Papua New Guinea', '675', 'PGK', 'K', '🇵🇬', 'OC', 'fr', false, 232],
            ['PY', 'PRY', 'Paraguay', 'Paraguay', '595', 'PYG', 'PYG', '🇵🇾', 'SA', 'fr', false, 233],
            ['NL', 'NLD', 'Pays-Bas', 'Netherlands', '31', 'EUR', '€', '🇳🇱', 'EU', 'fr', false, 234],
            ['PH', 'PHL', 'Philippines', 'Philippines', '63', 'PHP', '₱', '🇵🇭', 'AS', 'en', false, 235],
            ['PL', 'POL', 'Pologne', 'Poland', '48', 'PLN', 'PLN', '🇵🇱', 'EU', 'fr', false, 236],
            ['PF', 'PYF', 'Polynésie française', 'French Polynesia', '689', 'XPF', 'CFPF', '🇵🇫', 'OC', 'fr', false, 237],
            ['PR', 'PRI', 'Porto Rico', 'Puerto Rico', '1787', 'USD', '$', '🇵🇷', 'NA', 'fr', false, 238],
            ['PT', 'PRT', 'Portugal', 'Portugal', '351', 'EUR', '€', '🇵🇹', 'EU', 'fr', false, 239],
            ['PE', 'PER', 'Pérou', 'Peru', '51', 'PEN', 'PEN', '🇵🇪', 'SA', 'fr', false, 240],
            ['QA', 'QAT', 'Qatar', 'Qatar', '974', 'QAR', 'QAR', '🇶🇦', 'AS', 'fr', false, 241],
            ['HK', 'HKG', 'R.A.S. chinoise de Hong Kong', 'Hong Kong SAR China', '852', 'HKD', 'HK$', '🇭🇰', 'AS', 'fr', false, 242],
            ['MO', 'MAC', 'R.A.S. chinoise de Macao', 'Macao SAR China', '853', 'MOP', 'MOP$', '🇲🇴', 'AS', 'fr', false, 243],
            ['RO', 'ROU', 'Roumanie', 'Romania', '40', 'RON', 'RON', '🇷🇴', 'EU', 'fr', false, 244],
            ['GB', 'GBR', 'Royaume-Uni', 'United Kingdom', '44', 'GBP', '£', '🇬🇧', 'EU', 'en', false, 245],
            ['RU', 'RUS', 'Russie', 'Russia', '7', 'RUB', 'RUB', '🇷🇺', 'EU', 'fr', false, 246],
            ['RW', 'RWA', 'Rwanda', 'Rwanda', '250', 'RWF', 'RF', '🇷🇼', 'AF', 'en', true, 247],
            ['CF', 'CAF', 'République centrafricaine', 'Central African Republic', '236', 'XAF', 'FCFA', '🇨🇫', 'AF', 'fr', true, 248],
            ['DO', 'DOM', 'République dominicaine', 'Dominican Republic', '1809', 'DOP', 'DOP', '🇩🇴', 'NA', 'fr', false, 249],
            ['KN', 'KNA', 'Saint-Christophe-et-Niévès', 'St. Kitts & Nevis', '1869', 'XCD', '$', '🇰🇳', 'NA', 'fr', false, 250],
            ['SM', 'SMR', 'Saint-Marin', 'San Marino', '378', 'EUR', '€', '🇸🇲', 'EU', 'fr', false, 251],
            ['VC', 'VCT', 'Saint-Vincent-et-les Grenadines', 'St. Vincent & Grenadines', '1784', 'XCD', '$', '🇻🇨', 'NA', 'fr', false, 252],
            ['LC', 'LCA', 'Sainte-Lucie', 'St. Lucia', '1758', 'XCD', '$', '🇱🇨', 'NA', 'fr', false, 253],
            ['SV', 'SLV', 'Salvador', 'El Salvador', '503', 'USD', '$', '🇸🇻', 'NA', 'fr', false, 254],
            ['WS', 'WSM', 'Samoa', 'Samoa', '685', 'WST', 'WS$', '🇼🇸', 'OC', 'fr', false, 255],
            ['ST', 'STP', 'Sao Tomé-et-Principe', 'São Tomé & Príncipe', '239', 'STN', 'STN', '🇸🇹', 'AF', 'fr', true, 256],
            ['RS', 'SRB', 'Serbie', 'Serbia', '381', 'RSD', 'RSD', '🇷🇸', 'EU', 'fr', false, 257],
            ['SC', 'SYC', 'Seychelles', 'Seychelles', '248', 'SCR', 'SR', '🇸🇨', 'AF', 'en', true, 258],
            ['SL', 'SLE', 'Sierra Leone', 'Sierra Leone', '232', 'SLE', 'Le', '🇸🇱', 'AF', 'en', true, 259],
            ['SG', 'SGP', 'Singapour', 'Singapore', '65', 'SGD', '$', '🇸🇬', 'AS', 'en', false, 260],
            ['SK', 'SVK', 'Slovaquie', 'Slovakia', '421', 'EUR', '€', '🇸🇰', 'EU', 'fr', false, 261],
            ['SI', 'SVN', 'Slovénie', 'Slovenia', '386', 'EUR', '€', '🇸🇮', 'EU', 'fr', false, 262],
            ['SO', 'SOM', 'Somalie', 'Somalia', '252', 'SOS', 'SOS', '🇸🇴', 'AF', 'en', true, 263],
            ['SD', 'SDN', 'Soudan', 'Sudan', '249', 'SDG', 'SDG', '🇸🇩', 'AF', 'fr', true, 264],
            ['SS', 'SSD', 'Soudan du Sud', 'South Sudan', '211', 'SSP', '£', '🇸🇸', 'AF', 'en', true, 265],
            ['LK', 'LKA', 'Sri Lanka', 'Sri Lanka', '94', 'LKR', 'LKR', '🇱🇰', 'AS', 'fr', false, 266],
            ['CH', 'CHE', 'Suisse', 'Switzerland', '41', 'CHF', 'CHF', '🇨🇭', 'EU', 'fr', false, 267],
            ['SR', 'SUR', 'Suriname', 'Suriname', '597', 'SRD', 'SRD', '🇸🇷', 'SA', 'fr', false, 268],
            ['SE', 'SWE', 'Suède', 'Sweden', '46', 'SEK', 'kr', '🇸🇪', 'EU', 'fr', false, 269],
            ['SY', 'SYR', 'Syrie', 'Syria', '963', 'SYP', 'SYP', '🇸🇾', 'AS', 'fr', false, 270],
            ['SN', 'SEN', 'Sénégal', 'Senegal', '221', 'XOF', 'F CFA', '🇸🇳', 'AF', 'fr', true, 271],
            ['TJ', 'TJK', 'Tadjikistan', 'Tajikistan', '992', 'TJS', 'TJS', '🇹🇯', 'AS', 'fr', false, 272],
            ['TZ', 'TZA', 'Tanzanie', 'Tanzania', '255', 'TZS', 'TSh', '🇹🇿', 'AF', 'en', true, 273],
            ['TW', 'TWN', 'Taïwan', 'Taiwan', '886', 'TWD', 'NT$', '🇹🇼', 'AS', 'fr', false, 274],
            ['TD', 'TCD', 'Tchad', 'Chad', '235', 'XAF', 'FCFA', '🇹🇩', 'AF', 'fr', true, 275],
            ['CZ', 'CZE', 'Tchéquie', 'Czechia', '420', 'CZK', 'CZK', '🇨🇿', 'EU', 'fr', false, 276],
            ['PS', 'PSE', 'Territoires palestiniens', 'Palestinian Territories', '970', 'ILS', '₪', '🇵🇸', 'AS', 'fr', false, 277],
            ['TH', 'THA', 'Thaïlande', 'Thailand', '66', 'THB', 'THB', '🇹🇭', 'AS', 'fr', false, 278],
            ['TL', 'TLS', 'Timor oriental', 'Timor-Leste', '670', 'USD', '$', '🇹🇱', 'AS', 'fr', false, 279],
            ['TG', 'TGO', 'Togo', 'Togo', '228', 'XOF', 'F CFA', '🇹🇬', 'AF', 'fr', true, 280],
            ['TO', 'TON', 'Tonga', 'Tonga', '676', 'TOP', 'T$', '🇹🇴', 'OC', 'fr', false, 281],
            ['TT', 'TTO', 'Trinité-et-Tobago', 'Trinidad & Tobago', '1868', 'TTD', '$', '🇹🇹', 'NA', 'fr', false, 282],
            ['TN', 'TUN', 'Tunisie', 'Tunisia', '216', 'TND', 'TND', '🇹🇳', 'AF', 'fr', true, 283],
            ['TM', 'TKM', 'Turkménistan', 'Turkmenistan', '993', 'TMT', 'TMT', '🇹🇲', 'AS', 'fr', false, 284],
            ['TR', 'TUR', 'Turquie', 'Turkey', '90', 'TRY', 'TRY', '🇹🇷', 'AS', 'fr', false, 285],
            ['TV', 'TUV', 'Tuvalu', 'Tuvalu', '688', 'AUD', '$', '🇹🇻', 'OC', 'fr', false, 286],
            ['UA', 'UKR', 'Ukraine', 'Ukraine', '380', 'UAH', 'UAH', '🇺🇦', 'EU', 'fr', false, 287],
            ['UY', 'URY', 'Uruguay', 'Uruguay', '598', 'UYU', 'UYU', '🇺🇾', 'SA', 'fr', false, 288],
            ['VU', 'VUT', 'Vanuatu', 'Vanuatu', '678', 'VUV', 'VT', '🇻🇺', 'OC', 'fr', false, 289],
            ['VE', 'VEN', 'Venezuela', 'Venezuela', '58', 'VES', 'VES', '🇻🇪', 'SA', 'fr', false, 290],
            ['VN', 'VNM', 'Viêt Nam', 'Vietnam', '84', 'VND', '₫', '🇻🇳', 'AS', 'fr', false, 291],
            ['YE', 'YEM', 'Yémen', 'Yemen', '967', 'YER', 'YER', '🇾🇪', 'AS', 'fr', false, 292],
            ['ZM', 'ZMB', 'Zambie', 'Zambia', '260', 'ZMW', 'K', '🇿🇲', 'AF', 'en', true, 293],
            ['ZW', 'ZWE', 'Zimbabwe', 'Zimbabwe', '263', 'USD', 'US$', '🇿🇼', 'AF', 'en', true, 294],
            ['EG', 'EGY', 'Égypte', 'Egypt', '20', 'EGP', 'EGP', '🇪🇬', 'AF', 'fr', true, 295],
            ['AE', 'ARE', 'Émirats arabes unis', 'United Arab Emirates', '971', 'AED', 'AED', '🇦🇪', 'AS', 'fr', false, 296],
            ['EC', 'ECU', 'Équateur', 'Ecuador', '593', 'USD', '$', '🇪🇨', 'SA', 'fr', false, 297],
            ['ER', 'ERI', 'Érythrée', 'Eritrea', '291', 'ERN', 'Nfk', '🇪🇷', 'AF', 'en', true, 298],
            ['VA', 'VAT', 'État de la Cité du Vatican', 'Vatican City', '379', 'EUR', '€', '🇻🇦', 'EU', 'fr', false, 299],
            ['US', 'USA', 'États-Unis', 'United States', '1', 'USD', '$', '🇺🇸', 'NA', 'en', false, 300],
            ['ET', 'ETH', 'Éthiopie', 'Ethiopia', '251', 'ETB', 'ETB', '🇪🇹', 'AF', 'en', true, 301],
            ['KY', 'CYM', 'Îles Caïmans', 'Cayman Islands', '1345', 'KYD', '$', '🇰🇾', 'NA', 'fr', false, 302],
            ['FO', 'FRO', 'Îles Féroé', 'Faroe Islands', '298', 'DKK', 'DKK', '🇫🇴', 'EU', 'fr', false, 303],
            ['MH', 'MHL', 'Îles Marshall', 'Marshall Islands', '692', 'USD', '$', '🇲🇭', 'OC', 'fr', false, 304],
            ['SB', 'SLB', 'Îles Salomon', 'Solomon Islands', '677', 'SBD', '$', '🇸🇧', 'OC', 'fr', false, 305],
            ['TC', 'TCA', 'Îles Turques-et-Caïques', 'Turks & Caicos Islands', '1649', 'USD', 'US$', '🇹🇨', 'NA', 'fr', false, 306],
            ['VG', 'VGB', 'Îles Vierges britanniques', 'British Virgin Islands', '1284', 'USD', 'US$', '🇻🇬', 'NA', 'fr', false, 307],
            ['VI', 'VIR', 'Îles Vierges des États-Unis', 'U.S. Virgin Islands', '1340', 'USD', '$', '🇻🇮', 'NA', 'fr', false, 308],
    ];

    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            // AF, EU, AS, NA, SA, OC. A fact about the country.
            $table->char('continent', 2)->nullable()->after('name_en');
            // Policy: may someone selling from here open a shop? Africa only for
            // now. Separate from is_active, which answers whether the country is
            // offered at all.
            $table->boolean('seller_enabled')->default(false)->after('is_active');
        });

        $now      = now();
        $existing = DB::table('countries')->pluck('code')->all();
        $insert   = [];

        foreach (self::COUNTRIES as [$code, $code3, $nameFr, $nameEn, $dial, $currency, $symbol, $flag, $continent, $lang, $seller, $sort]) {
            if (in_array($code, $existing, true)) {
                // Already curated by hand — only the two new columns are set.
                DB::table('countries')->where('code', $code)->update([
                    'continent'      => $continent,
                    'seller_enabled' => $seller,
                    'updated_at'     => $now,
                ]);

                continue;
            }

            $insert[] = [
                'code' => $code, 'code3' => $code3,
                'name_fr' => $nameFr, 'name_en' => $nameEn,
                'continent' => $continent,
                'dial_code' => $dial,
                'currency_code' => $currency, 'currency_symbol' => $symbol,
                'flag_emoji' => $flag, 'default_lang' => $lang,
                'is_active' => true, 'seller_enabled' => $seller,
                'sort_order' => $sort,
                'created_at' => $now, 'updated_at' => $now,
            ];
        }

        // Chunked: some shared hosts cap the size of a single INSERT.
        foreach (array_chunk($insert, 50) as $chunk) {
            DB::table('countries')->insert($chunk);
        }
    }

    public function down(): void
    {
        // Only the countries this migration added: the original three predate it.
        DB::table('countries')
            ->whereNotIn('code', ['CM', 'CI', 'DZ'])
            ->whereNotIn('id', fn ($q) => $q->select('country_id')->from('users')->whereNotNull('country_id'))
            ->whereNotIn('id', fn ($q) => $q->select('country_id')->from('businesses')->whereNotNull('country_id'))
            ->delete();

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['continent', 'seller_enabled']);
        });
    }
};
