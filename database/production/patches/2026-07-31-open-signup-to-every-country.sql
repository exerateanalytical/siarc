-- ============================================================================
--  Open signup to every country for buyers, and all of Africa for sellers
--  Run once, in phpMyAdmin, against artisan_arthubdb.
--
--  Run this AFTER 2026-07-31-add-country-to-users.sql.
-- ============================================================================
--
--  Adds two columns to `countries` and the 209 countries the platform did not
--  have rows for. Purely additive: the currently-running code selects on
--  `is_active` and ignores both new columns, so this is safe to run before the
--  new code is uploaded, which is the required order.
--
--  `continent` is a fact about the country. `seller_enabled` is policy on top
--  of it — Africa only for now — kept separate so the rule can change for one
--  country without lying about its geography.
--
--  Cameroon, Cote d'Ivoire and Algeria are NOT re-inserted: they already exist
--  with curated names and currency symbols, and keep sort_order 1-3. They are
--  only given the two new columns.
--
--  Generated from the migration 2026_07_31_100000_open_signup_to_every_country,
--  not typed by hand.
--
--  MariaDB-safe: no CTEs, no procedures, no UPDATE ... JOIN.
-- ============================================================================

SET NAMES utf8mb4;

ALTER TABLE `countries` ADD COLUMN `continent` char(2) NULL AFTER `name_en`;
ALTER TABLE `countries` ADD COLUMN `seller_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_active`;

-- ── The three countries that already exist: new columns only ──
UPDATE `countries` SET `continent` = 'AF', `seller_enabled` = 1, `updated_at` = NOW() WHERE `code` = 'DZ';
UPDATE `countries` SET `continent` = 'AF', `seller_enabled` = 1, `updated_at` = NOW() WHERE `code` = 'CM';
UPDATE `countries` SET `continent` = 'AF', `seller_enabled` = 1, `updated_at` = NOW() WHERE `code` = 'CI';


-- ── The countries the platform did not have rows for ──
-- INSERT IGNORE so re-running the patch cannot duplicate a country.

INSERT IGNORE INTO `countries`
  (`code`,`code3`,`name_fr`,`name_en`,`continent`,`dial_code`,`currency_code`,`currency_symbol`,`flag_emoji`,`default_lang`,`is_active`,`seller_enabled`,`sort_order`,`created_at`,`updated_at`)
VALUES
  ('AF','AFG','Afghanistan','Afghanistan','AS','93','AFN','AFN','🇦🇫','fr',1,0,100,NOW(),NOW()),
  ('ZA','ZAF','Afrique du Sud','South Africa','AF','27','ZAR','R','🇿🇦','en',1,1,101,NOW(),NOW()),
  ('AL','ALB','Albanie','Albania','EU','355','ALL','ALL','🇦🇱','fr',1,0,102,NOW(),NOW()),
  ('DE','DEU','Allemagne','Germany','EU','49','EUR','€','🇩🇪','fr',1,0,103,NOW(),NOW()),
  ('AD','AND','Andorre','Andorra','EU','376','EUR','€','🇦🇩','fr',1,0,104,NOW(),NOW()),
  ('AO','AGO','Angola','Angola','AF','244','AOA','AOA','🇦🇴','fr',1,1,105,NOW(),NOW()),
  ('AG','ATG','Antigua-et-Barbuda','Antigua & Barbuda','NA','1268','XCD','$','🇦🇬','fr',1,0,106,NOW(),NOW()),
  ('SA','SAU','Arabie saoudite','Saudi Arabia','AS','966','SAR','SAR','🇸🇦','fr',1,0,107,NOW(),NOW()),
  ('AR','ARG','Argentine','Argentina','SA','54','ARS','ARS','🇦🇷','fr',1,0,108,NOW(),NOW()),
  ('AM','ARM','Arménie','Armenia','AS','374','AMD','AMD','🇦🇲','fr',1,0,109,NOW(),NOW()),
  ('AW','ABW','Aruba','Aruba','NA','297','AWG','AWG','🇦🇼','fr',1,0,110,NOW(),NOW()),
  ('AU','AUS','Australie','Australia','OC','61','AUD','$','🇦🇺','en',1,0,111,NOW(),NOW()),
  ('AT','AUT','Autriche','Austria','EU','43','EUR','€','🇦🇹','fr',1,0,112,NOW(),NOW()),
  ('AZ','AZE','Azerbaïdjan','Azerbaijan','AS','994','AZN','AZN','🇦🇿','fr',1,0,113,NOW(),NOW()),
  ('BS','BHS','Bahamas','Bahamas','NA','1242','BSD','$','🇧🇸','fr',1,0,114,NOW(),NOW()),
  ('BH','BHR','Bahreïn','Bahrain','AS','973','BHD','BHD','🇧🇭','fr',1,0,115,NOW(),NOW()),
  ('BD','BGD','Bangladesh','Bangladesh','AS','880','BDT','BDT','🇧🇩','fr',1,0,116,NOW(),NOW()),
  ('BB','BRB','Barbade','Barbados','NA','1246','BBD','$','🇧🇧','fr',1,0,117,NOW(),NOW()),
  ('BE','BEL','Belgique','Belgium','EU','32','EUR','€','🇧🇪','fr',1,0,118,NOW(),NOW()),
  ('BZ','BLZ','Belize','Belize','NA','501','BZD','$','🇧🇿','fr',1,0,119,NOW(),NOW()),
  ('BM','BMU','Bermudes','Bermuda','NA','1441','BMD','$','🇧🇲','fr',1,0,120,NOW(),NOW()),
  ('BT','BTN','Bhoutan','Bhutan','AS','975','BTN','BTN','🇧🇹','fr',1,0,121,NOW(),NOW()),
  ('BY','BLR','Biélorussie','Belarus','EU','375','BYN','BYN','🇧🇾','fr',1,0,122,NOW(),NOW()),
  ('BO','BOL','Bolivie','Bolivia','SA','591','BOB','BOB','🇧🇴','fr',1,0,123,NOW(),NOW()),
  ('BA','BIH','Bosnie-Herzégovine','Bosnia & Herzegovina','EU','387','BAM','BAM','🇧🇦','fr',1,0,124,NOW(),NOW()),
  ('BW','BWA','Botswana','Botswana','AF','267','BWP','P','🇧🇼','en',1,1,125,NOW(),NOW()),
  ('BN','BRN','Brunei','Brunei','AS','673','BND','BND','🇧🇳','fr',1,0,126,NOW(),NOW()),
  ('BR','BRA','Brésil','Brazil','SA','55','BRL','R$','🇧🇷','fr',1,0,127,NOW(),NOW()),
  ('BG','BGR','Bulgarie','Bulgaria','EU','359','BGN','BGN','🇧🇬','fr',1,0,128,NOW(),NOW()),
  ('BF','BFA','Burkina Faso','Burkina Faso','AF','226','XOF','F CFA','🇧🇫','fr',1,1,129,NOW(),NOW()),
  ('BI','BDI','Burundi','Burundi','AF','257','BIF','FBu','🇧🇮','fr',1,1,130,NOW(),NOW()),
  ('BJ','BEN','Bénin','Benin','AF','229','XOF','F CFA','🇧🇯','fr',1,1,131,NOW(),NOW()),
  ('KH','KHM','Cambodge','Cambodia','AS','855','KHR','KHR','🇰🇭','fr',1,0,132,NOW(),NOW()),
  ('CA','CAN','Canada','Canada','NA','1','CAD','$','🇨🇦','en',1,0,133,NOW(),NOW()),
  ('CV','CPV','Cap-Vert','Cape Verde','AF','238','CVE','CVE','🇨🇻','fr',1,1,134,NOW(),NOW()),
  ('CL','CHL','Chili','Chile','SA','56','CLP','CLP','🇨🇱','fr',1,0,135,NOW(),NOW()),
  ('CN','CHN','Chine','China','AS','86','CNY','CN¥','🇨🇳','fr',1,0,136,NOW(),NOW()),
  ('CY','CYP','Chypre','Cyprus','EU','357','EUR','€','🇨🇾','fr',1,0,137,NOW(),NOW()),
  ('CO','COL','Colombie','Colombia','SA','57','COP','COP','🇨🇴','fr',1,0,138,NOW(),NOW()),
  ('KM','COM','Comores','Comoros','AF','269','KMF','KMF','🇰🇲','fr',1,1,139,NOW(),NOW());

INSERT IGNORE INTO `countries`
  (`code`,`code3`,`name_fr`,`name_en`,`continent`,`dial_code`,`currency_code`,`currency_symbol`,`flag_emoji`,`default_lang`,`is_active`,`seller_enabled`,`sort_order`,`created_at`,`updated_at`)
VALUES
  ('CG','COG','Congo-Brazzaville','Congo - Brazzaville','AF','242','XAF','FCFA','🇨🇬','fr',1,1,140,NOW(),NOW()),
  ('CD','COD','Congo-Kinshasa','Congo - Kinshasa','AF','243','CDF','CDF','🇨🇩','fr',1,1,141,NOW(),NOW()),
  ('KP','PRK','Corée du Nord','North Korea','AS','850','KPW','KPW','🇰🇵','fr',1,0,142,NOW(),NOW()),
  ('KR','KOR','Corée du Sud','South Korea','AS','82','KRW','₩','🇰🇷','fr',1,0,143,NOW(),NOW()),
  ('CR','CRI','Costa Rica','Costa Rica','NA','506','CRC','CRC','🇨🇷','fr',1,0,144,NOW(),NOW()),
  ('HR','HRV','Croatie','Croatia','EU','385','EUR','€','🇭🇷','fr',1,0,145,NOW(),NOW()),
  ('CU','CUB','Cuba','Cuba','NA','53','CUP','CUP','🇨🇺','fr',1,0,146,NOW(),NOW()),
  ('CW','CUW','Curaçao','Curaçao','NA','599','ANG','ANG','🇨🇼','fr',1,0,147,NOW(),NOW()),
  ('DK','DNK','Danemark','Denmark','EU','45','DKK','kr.','🇩🇰','fr',1,0,148,NOW(),NOW()),
  ('DJ','DJI','Djibouti','Djibouti','AF','253','DJF','DJF','🇩🇯','fr',1,1,149,NOW(),NOW()),
  ('DM','DMA','Dominique','Dominica','NA','1767','XCD','$','🇩🇲','fr',1,0,150,NOW(),NOW()),
  ('ES','ESP','Espagne','Spain','EU','34','EUR','€','🇪🇸','fr',1,0,151,NOW(),NOW()),
  ('EE','EST','Estonie','Estonia','EU','372','EUR','€','🇪🇪','fr',1,0,152,NOW(),NOW()),
  ('SZ','SWZ','Eswatini','Eswatini','AF','268','SZL','E','🇸🇿','en',1,1,153,NOW(),NOW()),
  ('FJ','FJI','Fidji','Fiji','OC','679','FJD','$','🇫🇯','fr',1,0,154,NOW(),NOW()),
  ('FI','FIN','Finlande','Finland','EU','358','EUR','€','🇫🇮','fr',1,0,155,NOW(),NOW()),
  ('FR','FRA','France','France','EU','33','EUR','€','🇫🇷','fr',1,0,156,NOW(),NOW()),
  ('GA','GAB','Gabon','Gabon','AF','241','XAF','FCFA','🇬🇦','fr',1,1,157,NOW(),NOW()),
  ('GM','GMB','Gambie','Gambia','AF','220','GMD','D','🇬🇲','en',1,1,158,NOW(),NOW()),
  ('GH','GHA','Ghana','Ghana','AF','233','GHS','GH₵','🇬🇭','en',1,1,159,NOW(),NOW()),
  ('GI','GIB','Gibraltar','Gibraltar','EU','350','GIP','£','🇬🇮','fr',1,0,160,NOW(),NOW()),
  ('GD','GRD','Grenade','Grenada','NA','1473','XCD','$','🇬🇩','fr',1,0,161,NOW(),NOW()),
  ('GL','GRL','Groenland','Greenland','NA','299','DKK','DKK','🇬🇱','fr',1,0,162,NOW(),NOW()),
  ('GR','GRC','Grèce','Greece','EU','30','EUR','€','🇬🇷','fr',1,0,163,NOW(),NOW()),
  ('GU','GUM','Guam','Guam','OC','1671','USD','$','🇬🇺','fr',1,0,164,NOW(),NOW()),
  ('GT','GTM','Guatemala','Guatemala','NA','502','GTQ','GTQ','🇬🇹','fr',1,0,165,NOW(),NOW()),
  ('GN','GIN','Guinée','Guinea','AF','224','GNF','GNF','🇬🇳','fr',1,1,166,NOW(),NOW()),
  ('GQ','GNQ','Guinée équatoriale','Equatorial Guinea','AF','240','XAF','FCFA','🇬🇶','fr',1,1,167,NOW(),NOW()),
  ('GW','GNB','Guinée-Bissau','Guinea-Bissau','AF','245','XOF','F CFA','🇬🇼','fr',1,1,168,NOW(),NOW()),
  ('GY','GUY','Guyana','Guyana','SA','592','GYD','$','🇬🇾','fr',1,0,169,NOW(),NOW()),
  ('GE','GEO','Géorgie','Georgia','AS','995','GEL','GEL','🇬🇪','fr',1,0,170,NOW(),NOW()),
  ('HT','HTI','Haïti','Haiti','NA','509','HTG','HTG','🇭🇹','fr',1,0,171,NOW(),NOW()),
  ('HN','HND','Honduras','Honduras','NA','504','HNL','HNL','🇭🇳','fr',1,0,172,NOW(),NOW()),
  ('HU','HUN','Hongrie','Hungary','EU','36','HUF','HUF','🇭🇺','fr',1,0,173,NOW(),NOW()),
  ('IN','IND','Inde','India','AS','91','INR','₹','🇮🇳','en',1,0,174,NOW(),NOW()),
  ('ID','IDN','Indonésie','Indonesia','AS','62','IDR','IDR','🇮🇩','fr',1,0,175,NOW(),NOW()),
  ('IQ','IRQ','Irak','Iraq','AS','964','IQD','IQD','🇮🇶','fr',1,0,176,NOW(),NOW()),
  ('IR','IRN','Iran','Iran','AS','98','IRR','IRR','🇮🇷','fr',1,0,177,NOW(),NOW()),
  ('IE','IRL','Irlande','Ireland','EU','353','EUR','€','🇮🇪','en',1,0,178,NOW(),NOW()),
  ('IS','ISL','Islande','Iceland','EU','354','ISK','ISK','🇮🇸','fr',1,0,179,NOW(),NOW());

INSERT IGNORE INTO `countries`
  (`code`,`code3`,`name_fr`,`name_en`,`continent`,`dial_code`,`currency_code`,`currency_symbol`,`flag_emoji`,`default_lang`,`is_active`,`seller_enabled`,`sort_order`,`created_at`,`updated_at`)
VALUES
  ('IL','ISR','Israël','Israel','AS','972','ILS','₪','🇮🇱','fr',1,0,180,NOW(),NOW()),
  ('IT','ITA','Italie','Italy','EU','39','EUR','€','🇮🇹','fr',1,0,181,NOW(),NOW()),
  ('JM','JAM','Jamaïque','Jamaica','NA','1876','JMD','$','🇯🇲','fr',1,0,182,NOW(),NOW()),
  ('JP','JPN','Japon','Japan','AS','81','JPY','¥','🇯🇵','fr',1,0,183,NOW(),NOW()),
  ('JO','JOR','Jordanie','Jordan','AS','962','JOD','JOD','🇯🇴','fr',1,0,184,NOW(),NOW()),
  ('KZ','KAZ','Kazakhstan','Kazakhstan','AS','7','KZT','KZT','🇰🇿','fr',1,0,185,NOW(),NOW()),
  ('KE','KEN','Kenya','Kenya','AF','254','KES','Ksh','🇰🇪','en',1,1,186,NOW(),NOW()),
  ('KG','KGZ','Kirghizstan','Kyrgyzstan','AS','996','KGS','KGS','🇰🇬','fr',1,0,187,NOW(),NOW()),
  ('KI','KIR','Kiribati','Kiribati','OC','686','AUD','$','🇰🇮','fr',1,0,188,NOW(),NOW()),
  ('KW','KWT','Koweït','Kuwait','AS','965','KWD','KWD','🇰🇼','fr',1,0,189,NOW(),NOW()),
  ('LA','LAO','Laos','Laos','AS','856','LAK','LAK','🇱🇦','fr',1,0,190,NOW(),NOW()),
  ('LS','LSO','Lesotho','Lesotho','AF','266','ZAR','R','🇱🇸','en',1,1,191,NOW(),NOW()),
  ('LV','LVA','Lettonie','Latvia','EU','371','EUR','€','🇱🇻','fr',1,0,192,NOW(),NOW()),
  ('LB','LBN','Liban','Lebanon','AS','961','LBP','LBP','🇱🇧','fr',1,0,193,NOW(),NOW()),
  ('LR','LBR','Liberia','Liberia','AF','231','LRD','$','🇱🇷','en',1,1,194,NOW(),NOW()),
  ('LY','LBY','Libye','Libya','AF','218','LYD','LYD','🇱🇾','fr',1,1,195,NOW(),NOW()),
  ('LI','LIE','Liechtenstein','Liechtenstein','EU','423','CHF','CHF','🇱🇮','fr',1,0,196,NOW(),NOW()),
  ('LT','LTU','Lituanie','Lithuania','EU','370','EUR','€','🇱🇹','fr',1,0,197,NOW(),NOW()),
  ('LU','LUX','Luxembourg','Luxembourg','EU','352','EUR','€','🇱🇺','fr',1,0,198,NOW(),NOW()),
  ('MK','MKD','Macédoine du Nord','North Macedonia','EU','389','MKD','MKD','🇲🇰','fr',1,0,199,NOW(),NOW()),
  ('MG','MDG','Madagascar','Madagascar','AF','261','MGA','Ar','🇲🇬','fr',1,1,200,NOW(),NOW()),
  ('MY','MYS','Malaisie','Malaysia','AS','60','MYR','RM','🇲🇾','en',1,0,201,NOW(),NOW()),
  ('MW','MWI','Malawi','Malawi','AF','265','MWK','MK','🇲🇼','en',1,1,202,NOW(),NOW()),
  ('MV','MDV','Maldives','Maldives','AS','960','MVR','Rf','🇲🇻','fr',1,0,203,NOW(),NOW()),
  ('ML','MLI','Mali','Mali','AF','223','XOF','F CFA','🇲🇱','fr',1,1,204,NOW(),NOW()),
  ('MT','MLT','Malte','Malta','EU','356','EUR','€','🇲🇹','fr',1,0,205,NOW(),NOW()),
  ('MA','MAR','Maroc','Morocco','AF','212','MAD','MAD','🇲🇦','fr',1,1,206,NOW(),NOW()),
  ('MU','MUS','Maurice','Mauritius','AF','230','MUR','Rs','🇲🇺','en',1,1,207,NOW(),NOW()),
  ('MR','MRT','Mauritanie','Mauritania','AF','222','MRU','MRU','🇲🇷','fr',1,1,208,NOW(),NOW()),
  ('MX','MEX','Mexique','Mexico','NA','52','MXN','MX$','🇲🇽','fr',1,0,209,NOW(),NOW()),
  ('FM','FSM','Micronésie','Micronesia','OC','691','USD','US$','🇫🇲','fr',1,0,210,NOW(),NOW()),
  ('MD','MDA','Moldavie','Moldova','EU','373','MDL','MDL','🇲🇩','fr',1,0,211,NOW(),NOW()),
  ('MC','MCO','Monaco','Monaco','EU','377','EUR','€','🇲🇨','fr',1,0,212,NOW(),NOW()),
  ('MN','MNG','Mongolie','Mongolia','AS','976','MNT','MNT','🇲🇳','fr',1,0,213,NOW(),NOW()),
  ('ME','MNE','Monténégro','Montenegro','EU','382','EUR','€','🇲🇪','fr',1,0,214,NOW(),NOW()),
  ('MZ','MOZ','Mozambique','Mozambique','AF','258','MZN','MZN','🇲🇿','fr',1,1,215,NOW(),NOW()),
  ('MM','MMR','Myanmar (Birmanie)','Myanmar (Burma)','AS','95','MMK','MMK','🇲🇲','fr',1,0,216,NOW(),NOW()),
  ('NA','NAM','Namibie','Namibia','AF','264','NAD','$','🇳🇦','en',1,1,217,NOW(),NOW()),
  ('NR','NRU','Nauru','Nauru','OC','674','AUD','$','🇳🇷','fr',1,0,218,NOW(),NOW()),
  ('NI','NIC','Nicaragua','Nicaragua','NA','505','NIO','NIO','🇳🇮','fr',1,0,219,NOW(),NOW());

INSERT IGNORE INTO `countries`
  (`code`,`code3`,`name_fr`,`name_en`,`continent`,`dial_code`,`currency_code`,`currency_symbol`,`flag_emoji`,`default_lang`,`is_active`,`seller_enabled`,`sort_order`,`created_at`,`updated_at`)
VALUES
  ('NE','NER','Niger','Niger','AF','227','XOF','F CFA','🇳🇪','fr',1,1,220,NOW(),NOW()),
  ('NG','NGA','Nigeria','Nigeria','AF','234','NGN','₦','🇳🇬','en',1,1,221,NOW(),NOW()),
  ('NO','NOR','Norvège','Norway','EU','47','NOK','NOK','🇳🇴','fr',1,0,222,NOW(),NOW()),
  ('NC','NCL','Nouvelle-Calédonie','New Caledonia','OC','687','XPF','CFPF','🇳🇨','fr',1,0,223,NOW(),NOW()),
  ('NZ','NZL','Nouvelle-Zélande','New Zealand','OC','64','NZD','$','🇳🇿','en',1,0,224,NOW(),NOW()),
  ('NP','NPL','Népal','Nepal','AS','977','NPR','NPR','🇳🇵','fr',1,0,225,NOW(),NOW()),
  ('OM','OMN','Oman','Oman','AS','968','OMR','OMR','🇴🇲','fr',1,0,226,NOW(),NOW()),
  ('UG','UGA','Ouganda','Uganda','AF','256','UGX','USh','🇺🇬','en',1,1,227,NOW(),NOW()),
  ('UZ','UZB','Ouzbékistan','Uzbekistan','AS','998','UZS','UZS','🇺🇿','fr',1,0,228,NOW(),NOW()),
  ('PK','PAK','Pakistan','Pakistan','AS','92','PKR','Rs','🇵🇰','en',1,0,229,NOW(),NOW()),
  ('PW','PLW','Palaos','Palau','OC','680','USD','US$','🇵🇼','fr',1,0,230,NOW(),NOW()),
  ('PA','PAN','Panama','Panama','NA','507','PAB','PAB','🇵🇦','fr',1,0,231,NOW(),NOW()),
  ('PG','PNG','Papouasie-Nouvelle-Guinée','Papua New Guinea','OC','675','PGK','K','🇵🇬','fr',1,0,232,NOW(),NOW()),
  ('PY','PRY','Paraguay','Paraguay','SA','595','PYG','PYG','🇵🇾','fr',1,0,233,NOW(),NOW()),
  ('NL','NLD','Pays-Bas','Netherlands','EU','31','EUR','€','🇳🇱','fr',1,0,234,NOW(),NOW()),
  ('PH','PHL','Philippines','Philippines','AS','63','PHP','₱','🇵🇭','en',1,0,235,NOW(),NOW()),
  ('PL','POL','Pologne','Poland','EU','48','PLN','PLN','🇵🇱','fr',1,0,236,NOW(),NOW()),
  ('PF','PYF','Polynésie française','French Polynesia','OC','689','XPF','CFPF','🇵🇫','fr',1,0,237,NOW(),NOW()),
  ('PR','PRI','Porto Rico','Puerto Rico','NA','1787','USD','$','🇵🇷','fr',1,0,238,NOW(),NOW()),
  ('PT','PRT','Portugal','Portugal','EU','351','EUR','€','🇵🇹','fr',1,0,239,NOW(),NOW()),
  ('PE','PER','Pérou','Peru','SA','51','PEN','PEN','🇵🇪','fr',1,0,240,NOW(),NOW()),
  ('QA','QAT','Qatar','Qatar','AS','974','QAR','QAR','🇶🇦','fr',1,0,241,NOW(),NOW()),
  ('HK','HKG','R.A.S. chinoise de Hong Kong','Hong Kong SAR China','AS','852','HKD','HK$','🇭🇰','fr',1,0,242,NOW(),NOW()),
  ('MO','MAC','R.A.S. chinoise de Macao','Macao SAR China','AS','853','MOP','MOP$','🇲🇴','fr',1,0,243,NOW(),NOW()),
  ('RO','ROU','Roumanie','Romania','EU','40','RON','RON','🇷🇴','fr',1,0,244,NOW(),NOW()),
  ('GB','GBR','Royaume-Uni','United Kingdom','EU','44','GBP','£','🇬🇧','en',1,0,245,NOW(),NOW()),
  ('RU','RUS','Russie','Russia','EU','7','RUB','RUB','🇷🇺','fr',1,0,246,NOW(),NOW()),
  ('RW','RWA','Rwanda','Rwanda','AF','250','RWF','RF','🇷🇼','en',1,1,247,NOW(),NOW()),
  ('CF','CAF','République centrafricaine','Central African Republic','AF','236','XAF','FCFA','🇨🇫','fr',1,1,248,NOW(),NOW()),
  ('DO','DOM','République dominicaine','Dominican Republic','NA','1809','DOP','DOP','🇩🇴','fr',1,0,249,NOW(),NOW()),
  ('KN','KNA','Saint-Christophe-et-Niévès','St. Kitts & Nevis','NA','1869','XCD','$','🇰🇳','fr',1,0,250,NOW(),NOW()),
  ('SM','SMR','Saint-Marin','San Marino','EU','378','EUR','€','🇸🇲','fr',1,0,251,NOW(),NOW()),
  ('VC','VCT','Saint-Vincent-et-les Grenadines','St. Vincent & Grenadines','NA','1784','XCD','$','🇻🇨','fr',1,0,252,NOW(),NOW()),
  ('LC','LCA','Sainte-Lucie','St. Lucia','NA','1758','XCD','$','🇱🇨','fr',1,0,253,NOW(),NOW()),
  ('SV','SLV','Salvador','El Salvador','NA','503','USD','$','🇸🇻','fr',1,0,254,NOW(),NOW()),
  ('WS','WSM','Samoa','Samoa','OC','685','WST','WS$','🇼🇸','fr',1,0,255,NOW(),NOW()),
  ('ST','STP','Sao Tomé-et-Principe','São Tomé & Príncipe','AF','239','STN','STN','🇸🇹','fr',1,1,256,NOW(),NOW()),
  ('RS','SRB','Serbie','Serbia','EU','381','RSD','RSD','🇷🇸','fr',1,0,257,NOW(),NOW()),
  ('SC','SYC','Seychelles','Seychelles','AF','248','SCR','SR','🇸🇨','en',1,1,258,NOW(),NOW()),
  ('SL','SLE','Sierra Leone','Sierra Leone','AF','232','SLE','Le','🇸🇱','en',1,1,259,NOW(),NOW());

INSERT IGNORE INTO `countries`
  (`code`,`code3`,`name_fr`,`name_en`,`continent`,`dial_code`,`currency_code`,`currency_symbol`,`flag_emoji`,`default_lang`,`is_active`,`seller_enabled`,`sort_order`,`created_at`,`updated_at`)
VALUES
  ('SG','SGP','Singapour','Singapore','AS','65','SGD','$','🇸🇬','en',1,0,260,NOW(),NOW()),
  ('SK','SVK','Slovaquie','Slovakia','EU','421','EUR','€','🇸🇰','fr',1,0,261,NOW(),NOW()),
  ('SI','SVN','Slovénie','Slovenia','EU','386','EUR','€','🇸🇮','fr',1,0,262,NOW(),NOW()),
  ('SO','SOM','Somalie','Somalia','AF','252','SOS','SOS','🇸🇴','en',1,1,263,NOW(),NOW()),
  ('SD','SDN','Soudan','Sudan','AF','249','SDG','SDG','🇸🇩','fr',1,1,264,NOW(),NOW()),
  ('SS','SSD','Soudan du Sud','South Sudan','AF','211','SSP','£','🇸🇸','en',1,1,265,NOW(),NOW()),
  ('LK','LKA','Sri Lanka','Sri Lanka','AS','94','LKR','LKR','🇱🇰','fr',1,0,266,NOW(),NOW()),
  ('CH','CHE','Suisse','Switzerland','EU','41','CHF','CHF','🇨🇭','fr',1,0,267,NOW(),NOW()),
  ('SR','SUR','Suriname','Suriname','SA','597','SRD','SRD','🇸🇷','fr',1,0,268,NOW(),NOW()),
  ('SE','SWE','Suède','Sweden','EU','46','SEK','kr','🇸🇪','fr',1,0,269,NOW(),NOW()),
  ('SY','SYR','Syrie','Syria','AS','963','SYP','SYP','🇸🇾','fr',1,0,270,NOW(),NOW()),
  ('SN','SEN','Sénégal','Senegal','AF','221','XOF','F CFA','🇸🇳','fr',1,1,271,NOW(),NOW()),
  ('TJ','TJK','Tadjikistan','Tajikistan','AS','992','TJS','TJS','🇹🇯','fr',1,0,272,NOW(),NOW()),
  ('TZ','TZA','Tanzanie','Tanzania','AF','255','TZS','TSh','🇹🇿','en',1,1,273,NOW(),NOW()),
  ('TW','TWN','Taïwan','Taiwan','AS','886','TWD','NT$','🇹🇼','fr',1,0,274,NOW(),NOW()),
  ('TD','TCD','Tchad','Chad','AF','235','XAF','FCFA','🇹🇩','fr',1,1,275,NOW(),NOW()),
  ('CZ','CZE','Tchéquie','Czechia','EU','420','CZK','CZK','🇨🇿','fr',1,0,276,NOW(),NOW()),
  ('PS','PSE','Territoires palestiniens','Palestinian Territories','AS','970','ILS','₪','🇵🇸','fr',1,0,277,NOW(),NOW()),
  ('TH','THA','Thaïlande','Thailand','AS','66','THB','THB','🇹🇭','fr',1,0,278,NOW(),NOW()),
  ('TL','TLS','Timor oriental','Timor-Leste','AS','670','USD','$','🇹🇱','fr',1,0,279,NOW(),NOW()),
  ('TG','TGO','Togo','Togo','AF','228','XOF','F CFA','🇹🇬','fr',1,1,280,NOW(),NOW()),
  ('TO','TON','Tonga','Tonga','OC','676','TOP','T$','🇹🇴','fr',1,0,281,NOW(),NOW()),
  ('TT','TTO','Trinité-et-Tobago','Trinidad & Tobago','NA','1868','TTD','$','🇹🇹','fr',1,0,282,NOW(),NOW()),
  ('TN','TUN','Tunisie','Tunisia','AF','216','TND','TND','🇹🇳','fr',1,1,283,NOW(),NOW()),
  ('TM','TKM','Turkménistan','Turkmenistan','AS','993','TMT','TMT','🇹🇲','fr',1,0,284,NOW(),NOW()),
  ('TR','TUR','Turquie','Turkey','AS','90','TRY','TRY','🇹🇷','fr',1,0,285,NOW(),NOW()),
  ('TV','TUV','Tuvalu','Tuvalu','OC','688','AUD','$','🇹🇻','fr',1,0,286,NOW(),NOW()),
  ('UA','UKR','Ukraine','Ukraine','EU','380','UAH','UAH','🇺🇦','fr',1,0,287,NOW(),NOW()),
  ('UY','URY','Uruguay','Uruguay','SA','598','UYU','UYU','🇺🇾','fr',1,0,288,NOW(),NOW()),
  ('VU','VUT','Vanuatu','Vanuatu','OC','678','VUV','VT','🇻🇺','fr',1,0,289,NOW(),NOW()),
  ('VE','VEN','Venezuela','Venezuela','SA','58','VES','VES','🇻🇪','fr',1,0,290,NOW(),NOW()),
  ('VN','VNM','Viêt Nam','Vietnam','AS','84','VND','₫','🇻🇳','fr',1,0,291,NOW(),NOW()),
  ('YE','YEM','Yémen','Yemen','AS','967','YER','YER','🇾🇪','fr',1,0,292,NOW(),NOW()),
  ('ZM','ZMB','Zambie','Zambia','AF','260','ZMW','K','🇿🇲','en',1,1,293,NOW(),NOW()),
  ('ZW','ZWE','Zimbabwe','Zimbabwe','AF','263','USD','US$','🇿🇼','en',1,1,294,NOW(),NOW()),
  ('EG','EGY','Égypte','Egypt','AF','20','EGP','EGP','🇪🇬','fr',1,1,295,NOW(),NOW()),
  ('AE','ARE','Émirats arabes unis','United Arab Emirates','AS','971','AED','AED','🇦🇪','fr',1,0,296,NOW(),NOW()),
  ('EC','ECU','Équateur','Ecuador','SA','593','USD','$','🇪🇨','fr',1,0,297,NOW(),NOW()),
  ('ER','ERI','Érythrée','Eritrea','AF','291','ERN','Nfk','🇪🇷','en',1,1,298,NOW(),NOW()),
  ('VA','VAT','État de la Cité du Vatican','Vatican City','EU','379','EUR','€','🇻🇦','fr',1,0,299,NOW(),NOW());

INSERT IGNORE INTO `countries`
  (`code`,`code3`,`name_fr`,`name_en`,`continent`,`dial_code`,`currency_code`,`currency_symbol`,`flag_emoji`,`default_lang`,`is_active`,`seller_enabled`,`sort_order`,`created_at`,`updated_at`)
VALUES
  ('US','USA','États-Unis','United States','NA','1','USD','$','🇺🇸','en',1,0,300,NOW(),NOW()),
  ('ET','ETH','Éthiopie','Ethiopia','AF','251','ETB','ETB','🇪🇹','en',1,1,301,NOW(),NOW()),
  ('KY','CYM','Îles Caïmans','Cayman Islands','NA','1345','KYD','$','🇰🇾','fr',1,0,302,NOW(),NOW()),
  ('FO','FRO','Îles Féroé','Faroe Islands','EU','298','DKK','DKK','🇫🇴','fr',1,0,303,NOW(),NOW()),
  ('MH','MHL','Îles Marshall','Marshall Islands','OC','692','USD','$','🇲🇭','fr',1,0,304,NOW(),NOW()),
  ('SB','SLB','Îles Salomon','Solomon Islands','OC','677','SBD','$','🇸🇧','fr',1,0,305,NOW(),NOW()),
  ('TC','TCA','Îles Turques-et-Caïques','Turks & Caicos Islands','NA','1649','USD','US$','🇹🇨','fr',1,0,306,NOW(),NOW()),
  ('VG','VGB','Îles Vierges britanniques','British Virgin Islands','NA','1284','USD','US$','🇻🇬','fr',1,0,307,NOW(),NOW()),
  ('VI','VIR','Îles Vierges des États-Unis','U.S. Virgin Islands','NA','1340','USD','$','🇻🇮','fr',1,0,308,NOW(),NOW());


INSERT IGNORE INTO `migrations` (`migration`, `batch`)
VALUES ('2026_07_31_100000_open_signup_to_every_country',
        (SELECT * FROM (SELECT COALESCE(MAX(`batch`),0)+1 FROM `migrations`) AS b));

-- Expected: 212, 54, and 0.
SELECT
  (SELECT COUNT(*) FROM `countries`) AS `countries_MUST_BE_212`,
  (SELECT COUNT(*) FROM `countries` WHERE `seller_enabled` = 1) AS `seller_countries_MUST_BE_54`,
  (SELECT COUNT(*) FROM `countries` WHERE `seller_enabled` = 1 AND `continent` <> 'AF')
    AS `non_african_sellers_MUST_BE_0`;
