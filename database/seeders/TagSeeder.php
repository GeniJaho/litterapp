<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\TagType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $brand = TagType::query()->create(['name' => 'Brand', 'slug' => 'brand']);
        $material = TagType::query()->create(['name' => 'Material', 'slug' => 'material']);
        $event = TagType::query()->create(['name' => 'Event', 'slug' => 'event']);

        Tag::query()->insert($this->format($this->getEvents(), $event));
        Tag::query()->insert($this->format($this->getBrands(), $brand));
        Tag::query()->insert($this->format($this->getMaterials(), $material));
    }

    private function format(array $tags, Model $type): array
    {
        return array_map(fn (string $tag) => [
            'name' => $tag,
            'slug' => Str::slug($tag),
            'tag_type_id' => $type->id,
            'created_at' => now(),
            'updated_at' => now(),
        ], $tags);
    }

    private function getEvents(): array
    {
        return [
            'BeforeAndAfter',
            'BeforeAndAfter_After',
            'BeforeAndAfter_Before',
            'BigPlasticCount-2024',
            'BlueNoseMarathon-2024',
            'Canuary-2024',
            'Cleanup4Sarah-2024',
            'CornishSplicedTreasureHunt-SpecialFind',
            'CornishSplicedTreasureHunt-2024-01',
            'CornishSplicedTreasureHunt-2024-02',
            'CornishSplicedTreasureHunt-2024-03',
            'CornishSplicedTreasureHunt-2024-04',
            'CornishSplicedTreasureHunt-2024-05',
            'CornishSplicedTreasureHunt-2024-06',
            'NoordzeeKanaal-IJ-2024',
            'Ospar 100m',
            'Ospar 1000m',
            'OTHER (For an Event that is not in the picklist)',
            'PeukMeuk-2024',
            'PlasticAvengers-Bakzeil',
            'RedBullLitter',
            'ShowYourImpact',
            'ShowYourImpact_DeAfvalraperBladel',
            'ShowYourImpact_DeGrachtWacht',
            'ShowYourImpact_DeZwerfafvalRuimers',
            'ShowYourImpact_ZaanseZwerfvuilEstafette',
            'ShowYourImpact_ZwerfieRotterdam',
            'StichtingDeNoordzee-MeetMee-2024',
            'WorldCleanupDay-2024',
            'ZaanDeWandel-2024',
        ];
    }

    private function getMaterials(): array
    {
        return [
            'Aluminium',
            'Bronze',
            'Cardboard',
            'Cellulose Acetate Fiber'
            'Ceramic',
            'Copper',
            'Fiberglass',
            'Glass',
            'Iron or Steel',
            'Latex',
            'Leather',
            'Lithium',
            'Metal',
            'Nylon',
            'OTHER (For a Material that is not in the picklist)',
            'Paper',
            'Plastic',
            'Plastic (ABS)',
            'Plastic (HDPE)',
            'Plastic (LDPE)',
            'Plastic (PC)',
            'Plastic (PE)',
            'Plastic (PEN)',
            'Plastic (PET)',
            'Plastic (PP)',
            'Plastic (PS)',
            'Plastic (PVC)',
            'Plastic (PVDC)',
            'Plexyglass',
            'Rubber',
            'Styrofoam',
            'Textile',
            'Unknown',
            'Wood',
        ];
    }

    private function getBrands(): array
    {
        return [
            '100% Smoothie',
            '555',
            '71 Brewing',
            '7UP',
            '86',
            '9',
            'A&W (AW)',
            'AaDrink',
            'Abant',
            'Absolut',
            'Acadia',
            'Ace',
            'Actimel',
            'Action',
            'Actiph',
            'Acti Shake',
            'Activia',
            'Active O2',
            'Addipak Medical Supplies',
            'Adidas',
            'Adnams',
            'Aero',
            'Affligem',
            'Aga',
            'AH Vitamin Drink',
            'Airbar',
            'Airfilled',
            'Airheads',
            'Akhtamar',
            'Alaska',
            'Alaskan',
            'Albert Heijn',
            'Aldi',
            'Alesto',
            'Alex Meijer',
            'Alfa',
            'Alfakhr',
            'Almhof',
            'Alpen',
            'Alpro',
            'Amanie',
            'Amazon',
            'Amber Leaf',
            'Ambrosia',
            'American Vintage',
            'Amicelli',
            'Amigo',
            'Amstel',
            'Amsterdam Brewboys',
            'Anadin',
            'Anchor',
            'Andalooz',
            'Angelos',
            'Anta Flu',
            'Appelsientje',
            'Apple Bandit',
            'Applegreen',
            'Appletiser',
            'Aqua Carpatica',
            'Aqua Twist',
            'Aqua Vale',
            'Aquafina',
            'Aqualube',
            'Aquarius',
            'Arctic Coffee',
            'Arctic Glacier',
            'Argus',
            'Arizona',
            'Arla',
            'Aro',
            'Aromaking',
            'Asado',
            'Asahi',
            'Asda',
            'Astro',
            'Asvina',
            'Atlas',
            'Autodrop',
            'Avoca',
            'Awake',
            'Axe',
            'Ayran',
            'Babybel',
            'Bacardi',
            'Backwerk',
            'Badoit',
            'Baha',
            'Bahlsen',
            'Bakey',
            'Bakkers',
            'Balconi',
            'Balisto',
            'Ballygowan',
            'Bang',
            'Barbican',
            'Bar-le-duc',
            'Barnies Foods',
            'Barr',
            'Barratt',
            'BASF',
            'Basic-fit',
            'Basil Seed',
            'Bastogne',
            'Bavaria',
            'Bazooka',
            'BCLC',
            'BD Medical Supplies',
            'Bear',
            'Bearpaws',
            'Beatrice',
            'Bebeto',
            'Becks',
            'Beckys',
            'Beemster',
            'Belin',
            'Belkin',
            'Bella & Duke (BellaDuke)',
            'Belvedere',
            "Ben & Jerry's (BenJerrys)",
            'Ben Shaws',
            'Benecol',
            'Benson & Hedges (BensonHedges)',
            'Bentasil',
            'Berentzen',
            'Best',
            'Better',
            'Bewleys',
            'Beypazari',
            'Bic',
            'Bifi',
            'Big Turk',
            'Bigga',
            'Biggym',
            'Bio',
            'Biotiful',
            'Bird Brewery',
            'Birra Moretti',
            'Black',
            'Blaes',
            'Blankenheym',
            'blck',
            'Bloonies',
            'Blu',
            'Blue Ribbon',
            'Blueband',
            'BlueBastard',
            'Boba Poke',
            'Bobbys',
            'Bocian',
            'Boels',
            'Boembombali',
            'Bokma',
            'Bolletje',
            'Bols',
            'Bomba',
            'Bombay',
            'Bongelati',
            'Boost',
            'Booster Juice',
            'Boots',
            'Border',
            'Boss',
            'Boston',
            'Bounty',
            'Boursin',
            'Bouwmaat',
            'Bozu',
            'Bp',
            'Bramble Foods',
            'Brand',
            'Bratee',
            'Bravo',
            'Break',
            'Breaker',
            'Breezer',
            'Brekies',
            'Breton',
            'Brewdog',
            'Brinky',
            'Brisk',
            'Britvic',
            'Bros',
            'Brouwerij t IJ',
            'Brouwers',
            'Btween',
            'Bubbelfrisss',
            'Bubblicious',
            'Budweiser',
            'Bugles',
            'Bullit',
            'BumBum',
            'Buonciorno',
            'Burger King',
            'Busch',
            'Busta',
            'Butterkist',
            'Buxton',
            'Buys',
            'Buzz Bar',
            'C&A (CA)',
            'Cable Car',
            'Cadbury',
            'Cafe Bonjour',
            'Cafe Nero',
            'Cailler',
            'Calippo',
            'Calve',
            'Calypso',
            'Camaro',
            'Camden Town Brewery',
            'Camel',
            'Campina',
            'Canada Dry',
            'Canadian Classics',
            'Canadian Springs',
            'Canadian Tire',
            'Canderel',
            'Candy Can',
            'Candy King',
            'Candyman',
            'Canei',
            'Cannabis',
            'Capri-Sun',
            'Captainmorgan',
            'Carabao',
            'Caramilk',
            'Cariboo',
            'Carling',
            'Carls Jr.',
            'Carlsberg',
            'Carmex',
            'Carrick Glen',
            'Casal de Ventozela',
            'Casino',
            'Castel Boglione',
            'Celebrations',
            'Celtic Spring',
            'Chalwa',
            'Chapmans',
            'Chardonnay',
            'Charleston Chew',
            'Charlies',
            'Chaudfontaine',
            'Cheetos',
            'Cheezies',
            'Chekov',
            'Chenet',
            'Chesterfield',
            'Chewits',
            'Chiefs Totem',
            'Chio',
            'Chivas Regal',
            'Chocomel',
            'Chocomoment',
            'Christie',
            'Chrysal',
            'Chubby',
            'Chupa Chups',
            'Ciftlik',
            'Ciroc',
            'Cirro',
            'Claro',
            'Clearblue',
            'Clearly Canadian',
            'Clif',
            'Clinell',
            'Clipper',
            'Clorox',
            'Coca-Cola',
            'Cocio',
            'Coco',
            'Coffee Crisp',
            'Coffeefresh',
            'Coke',
            'Coleman',
            'Colgate',
            'Colt45',
            'Comet',
            'Completa',
            'Compton Orchard',
            'Conimex',
            'Contigo',
            'Contrex',
            'Coolbest',
            'Cooldrink',
            'Co-op',
            'Coopers',
            'Coors',
            'Coral',
            'Corn Nuts',
            'Cornetto',
            'Corona',
            'Costco',
            'Cotedor',
            'Cottonelle',
            'Courvoisier',
            'Cracker Jack',
            'Craft Nation',
            'Cravendale',
            'Crayola',
            'Crest',
            'Cricket',
            'Crispers',
            'Crispy Crunch',
            'Cristal',
            'Cristaline',
            'Croky',
            'Crown Royal',
            'Crunch',
            'Crunchie',
            'Crush',
            'Crystal',
            'Crystal Clear',
            'Culligan',
            'Cup-a-Soup',
            'Cutters Choice',
            'Cussons',
            'Dads',
            'Daelmans',
            'Daim',
            'Dairy Queen',
            'Dairyland',
            'Dairylea',
            'Dairymilk',
            'Dalga',
            'Dalphin',
            'Damel',
            'Damla',
            'Danio',
            'Danone',
            'Danoontje',
            'Dark Thunder',
            'Dasani',
            'Davidoff',
            'De Appelaere',
            'De Kleine Keuken',
            'De Kuyper',
            'De Lekkerste',
            'De Ruijter',
            'De Zaanse Hoeve',
            'Deal',
            'Decathlon',
            'Deen',
            'Degree',
            'Dekamarkt',
            'DeKlok',
            'Delisana',
            'Deliciously Ella',
            'Delmonte',
            'Dempsters',
            'Den Eelder',
            'Den Leeuw',
            'Dentalcare',
            'Dentalclinics',
            'Dentyne',
            'Depa',
            'Derby',
            'Desperados',
            'Dewit',
            'Dextro',
            'DeZevenGranen',
            'Dhl',
            'Diamond Mist',
            'Didi',
            'Dimple',
            'Dinnerlady',
            'Dirk',
            'Disco',
            'Discount',
            'Divine',
            'Djeep',
            'Djoezz',
            'Dock',
            'Dole',
            'Dollar Store',
            'Dollar Tree',
            'Dollarama',
            'Dolu',
            'Dominos',
            'Dommelsch',
            'Doom Bar',
            'Don Simon',
            'Dopper',
            'Doritos',
            'Dors',
            'Doublebubble',
            'Doublemint',
            'Douwe Egberts',
            'Dove',
            'Download',
            'Dr Foots',
            'Dr Pepper',
            'Dr. Toms',
            'Drakensberg',
            'Drinklicious',
            'Droste',
            'Drum',
            'Drumstick',
            'DrWitt',
            'Dubbeldrank',
            'Dubbelfrisss',
            'Dubbledutch',
            'Dujardin',
            'Dumaurier',
            'Dunhill',
            'Dunkin',
            'Duplo',
            'Duracell',
            'Dutch Crunch',
            'Durex',
            'Duvel',
            'Duyvis',
            'Dvtch',
            'Earth',
            'Eastmans',
            'Eat Natural',
            'Eatmore',
            'Edet',
            'Efes',
            'El Chapo Guzman',
            'El Tequito',
            'Elfbar',
            'ElkeMelk',
            'Elmos',
            'Elpicu',
            'Elux',
            'Embassy',
            'Emerge',
            'Emmi',
            'Empack',
            'Energizer',
            'Enkhuizer',
            'Enrgydrnk',
            'Ensure',
            'Erikli',
            'Eristoff',
            'Esbjaerg',
            'Esso',
            'Estathe',
            'Eti',
            'Etos',
            'Euro Shopper',
            'Eurofresh',
            'Everyday',
            'Evian',
            'Excel',
            'Export',
            'Export A',
            'Extra',
            'Extra Old Stock',
            'Extran',
            'Fa',
            'Fahnenbrau',
            'Famous Amos',
            'Family',
            'Fanta',
            'Fastgas',
            'Fatburger',
            'Faxe',
            'Febo',
            'Febreze',
            'Federal',
            'Fentimans',
            'Fernandes',
            'Festini',
            'Fever Tree',
            'Fiesta',
            'Fieten Oil',
            'Fiji Water',
            'Fin Carre',
            'Finesse',
            'Fini',
            'Finkbrau',
            'Finley',
            'FireMoon',
            'Firerose',
            'Fireup',
            'First Choice',
            'Fishermans Friend',
            'Fitness Beverwijk',
            'Fizz',
            'Flax',
            'Flipz',
            'Floralys',
            'Flow Bar',
            'Flugel',
            'Foco',
            'Focus',
            'For Goodness Shakes',
            'Ford',
            'Fortuna',
            'Fortune',
            'Four Loko',
            "Fox's",
            'Foxx',
            'Franziskaner',
            'Freal',
            'Freedent',
            'Freedoms Choice',
            'Freeway',
            'Freez',
            'Freezy',
            'Frenchs',
            'Freshlife',
            'Freshways',
            'Freybe',
            'Frezzh',
            'Fria',
            'Frisia',
            'Fristi',
            'Fritolay',
            'Fritt',
            'Frobishers',
            'Frontaal',
            'Frosted',
            'Fruit Shoot',
            'Fruit To Go',
            'Fruitfris',
            'Fruitnfun',
            'Fruitsations',
            'Fruittella',
            'Fudo',
            'Fuel 10k',
            'Fuerza',
            'Fulfil',
            'Full Throttle',
            'Fumot',
            'FuzeTea',
            'Gaasbeek',
            'Galereux',
            'Gall&gall (GallGall)',
            'Galpharm',
            'Gamma',
            'Gardinia',
            'Garnier',
            'Gato Negro',
            'Gatorade',
            'Gauloises',
            'Gaviscon',
            'Gazebo Cuisine',
            'Gazeuse',
            'Generator',
            'Gerardus',
            'Gerolsteiner',
            'GetMoreVits',
            'Gig',
            'Gillette',
            'Ginsters',
            'Gio',
            'Gio Coffee',
            'Glacier Ice',
            'Gladiator',
            'Glentalloch',
            'Glosette',
            'Glucerna',
            'Gobstopper',
            'Go Ahead',
            'Gofast',
            'Gofresh',
            'Gofrik',
            'Gogosqueez',
            'Gold',
            'Gold Bar',
            'Goldenpower',
            'Golden Wonder',
            'Golden Virginia',
            'Goldfield',
            'Goldfish',
            'Goldpeak',
            'Goldwing',
            'Goose Island',
            'Gordons',
            'Goreme',
            'Grace',
            'Grahams Family Dairy',
            'Graze',
            'Grazzano',
            'Great Value',
            'Greggs',
            'Grey Goose',
            'Grollz',
            'Grolsch',
            'Growers',
            'Gü',
            'Guarana',
            'Guinness',
            'Gulpener',
            'Gumus',
            'Gurpinar',
            'Gushers',
            'GVB',
            'Gwoon',
            'Häagen Dazs',
            'Halfords',
            'Hamidiye',
            'Hansaplast',
            'Hardthof',
            'Hardwheat',
            'Haribo',
            'Harlekijntjes',
            'Harnas',
            'Harrogate',
            'Hasbro',
            'Havana Club',
            'Hawai',
            'Hawkins',
            'Hayati',
            'Healthy People',
            'Heets',
            'Heineken',
            'Heinz',
            'Hell',
            'Hema',
            'Hennessy',
            'Henri Willig',
            'Hero',
            'Hersheys',
            'Hertog Jan',
            'HeyYall',
            'Hfc',
            'High5',
            'Highchew',
            'Highland Spring',
            'Highspeed',
            'Highway',
            'Hingham Bakery',
            'Hipro',
            'Hires',
            'Hm',
            'Hobgoblin',
            'Hoegaarden',
            'Hollandia',
            'Holsten',
            'Holtland',
            'Home Depot',
            'Hoop',
            'Hoppe',
            'Hornbach',
            'Hostess',
            'Hot Rod',
            'HP',
            'Huer',
            'Hugo',
            'Hula Hoops',
            'Huls',
            'Hypermalt',
            'Hyundai',
            'Icecappuccino',
            'Icelandic Glacial',
            'Ice Melt',
            'Ice Valley',
            'Iconix',
            'Idel',
            'IJwit',
            'Ikea',
            'Illy',
            'Indomie',
            'Innocent',
            'Insta Bar',
            'International Delight',
            'Intex',
            'Ipa',
            'Irn Bru',
            'Isero',
            'Island Farms',
            'Isostar',
            'Italiano',
            'Ivgbar',
            'J2o',
            'Jack Daniels',
            'Jack Links',
            "Jack's",
            'Jackson Triggs',
            "Jacob's",
            'Jakemans',
            'Jan Vet',
            'Jana',
            'Jarritos',
            'Jawbreaker',
            'Jello',
            'Jersey',
            'Jetgum',
            'Jillz',
            'Jim Beam',
            'Jimmys',
            'Jobecker',
            'Jofel Bier',
            'Johma',
            'John Player',
            'John Smiths',
            'John West',
            'Johnnie Walker',
            'Johnson&Johnson (JohnsonJohnson)',
            'Jolly Rancher',
            'Jordans',
            'Joseph Guy',
            'Jozo',
            'JPS',
            'JRS',
            'Juice Burst',
            'Jumbo',
            'Jupiler',
            'Just Juice',
            'Jxp',
            'K',
            'Ka',
            'Kaapse Draai',
            'Kaars',
            'Kahvealti',
            'Kanjers',
            'Karmi',
            'Kasteel',
            'Kasztelan',
            'Katja',
            'Kaubo',
            'Kebabish Express',
            'Keizerskroon',
            'Kelloggs',
            'Kellyloves',
            "Kelly's",
            'Kent',
            'Kerrs',
            'Kettle',
            'Keystone',
            'KFC',
            'KFC (Kooger Football Club)',
            'Kiddylicious',
            'Killa',
            'Kimberly Clark',
            'Kimura Ramune',
            'Kind',
            'Kinder',
            'KinderCola',
            'Kiosk',
            'Kirkland',
            'Kisko',
            'Kitkat',
            'Kizilay',
            'Kleenex',
            'Kleiner Klopfer',
            'Klene',
            'Klondike',
            'Klusdrop',
            'Knoppers',
            'Knorr',
            'Knuspi',
            'Knvb',
            'Koalakones',
            'Koetjesreep',
            'Kokanee',
            'Kompaan',
            'Kong Strong',
            'Konig Ludwig',
            'Koolaid',
            'Kopiko',
            'Kopparberg',
            'Kordaaat',
            'Kordaat',
            'Kornuit',
            'Kozel',
            'KP',
            'Kraax',
            'Kraft',
            'Krispy Kreme',
            'Kronenbourg',
            'Krolewskie',
            'Kruidvat',
            'Krupnik',
            'Kwaremont',
            'La Chouffe',
            'La Place',
            'La Versoie',
            'Labatt',
            'Labello',
            'Lactantia',
            'Lambert & Butler (LambertButler)',
            'Lager',
            'Landerbrau',
            'Langenekken',
            'Lark',
            'Laserlite',
            'Lavazza',
            'Lavish',
            'Lays',
            'LD Bold',
            'LD Standard',
            'Lech',
            'Lecoq',
            'Leffe',
            'Lemco',
            'Lemon',
            'Lemony',
            'Levi Roots',
            'Liberize',
            'Lichfields',
            'Licor43',
            'Lidl',
            'Liefmans',
            'Lifesavers',
            'Lifestyles',
            'Liga',
            'Lilt',
            'Limancellow',
            'Lindahls',
            'Lindt',
            'Linessa',
            'Lion',
            'Lipton',
            'Listerine',
            'L&M (LM)',
            'Locklock',
            'Loctite',
            'Loise',
            'Lomza',
            'London Drugs',
            'Lonka',
            'LookOLook',
            'Looza',
            'Lordco',
            'Lost Mary',
            'Lotus',
            'Louise',
            'Loveau',
            'Lovka',
            'Lowlander',
            'Lu',
            'Lucky',
            'Lucky Lager',
            'Lucky Strike',
            'Lukoil',
            'Lulu',
            'Lungo',
            'Luxus',
            'Lynx (beer)',
            'Lynx (deodorant)',
            'Lysol',
            'M&Ms (MMS)',
            'Maaza',
            'MacDonald',
            'Madri',
            'Maggi',
            'Magners',
            'Magnum',
            'Malibu',
            'Maltesers',
            'Maoam',
            'Marie',
            'Markant',
            'Marlboro',
            'Marmite',
            'Mars',
            'Marstons',
            'Martini',
            'Marx O Larrys',
            'Mascotte',
            'Master',
            'Mastermate',
            'Mastroianni',
            'Maurten',
            'Maxim',
            'Maynards-Bassetts',
            'Mazzetto',
            'McCain',
            "McCoy's",
            'McDonalds',
            "McEwan's",
            'McSweeneys',
            "McVitie's",
            'Megabiz',
            'Megaforce',
            'Meharis',
            'Melangedor',
            'Melkan',
            'Melkunie',
            'Melona',
            'Mentos',
            'Merba',
            'Merci',
            'Mexicanos',
            'Mezzomix',
            'Migo',
            'MikesHardLemonade',
            'Milbona',
            'Milk2Go',
            'Milka',
            'Milky Way',
            'Milwaukee',
            'Miller',
            'Milner',
            'Milsani',
            'Minkenhus',
            'Minute Maid',
            'Mirinda',
            'Miss Vickies',
            'Mixxedup',
            'Moersleutel',
            'Mogumogu',
            'Molson',
            'Molson Canadian',
            'Monster',
            'Monster Munch',
            'Moods',
            'Morrisons',
            'Most Wanted',
            'Mothers',
            'Motts',
            'Mountain Dew',
            'Mr. Cool',
            'Mr Freeze',
            'Mr Kipling',
            'Mr Noodles',
            'Mr Porky',
            'Mrs Freshleys',
            'Mug Root Beer',
            'Mullermilk',
            'Munchies',
            'Muskol',
            'Muszynianka',
            'Myhills',
            'My Protein',
            'Mythos',
            'Nakd',
            'Naked',
            'Nando\s',
            'Napoleon',
            'Natia',
            'Natural Cool',
            'Naturaqua',
            'Nature Valley',
            'Nerf',
            'Nescafe',
            'Nestea',
            'Nestle',
            'New York Pizza',
            'Next',
            'Ngine',
            'Nibs',
            'Nice',
            'Nigde',
            'Nightwatch',
            'Nike',
            'Nikon',
            'Nimm2',
            'Nisa',
            'Nitro',
            'Nivea',
            'Njoy',
            'No Fear',
            'No Name',
            'Nocco',
            'Noebron',
            'Nomadic Dairy',
            'Noppes Kringloop',
            'Nora',
            'Nordic Spirit',
            'Norfolk Catering',
            'NS',
            'Nude',
            'Number Seven',
            'Nussknacker',
            'Nutella',
            'Nutisal',
            'Nutricia',
            'Nutrigrain',
            'Nuts',
            'Nxt',
            'NXT Beverwijk',
            'NY Coffee',
            'O2Life',
            'Oasis',
            'Oblomov',
            'Oceans',
            'Octagon',
            'Odorex',
            'Oh Henry',
            'Ohso',
            'Oikos',
            'Oishi',
            'Okanagan',
            'Oke',
            'Okf',
            'Ola',
            'Old Dutch',
            'Old Holborn',
            'Old Jamaica',
            'Old Mill',
            'Old Milwaukee',
            'Old Mout',
            'Oldtimers',
            'Olvarit',
            'Olympic Hotel',
            'One Stop',
            'Optimel',
            'Optimum Nutrition',
            'OralB',
            'Orange',
            'Orangina',
            'Orange Julius',
            'Oreo',
            'Organics By Redbull',
            'Organix',
            'Orient Express',
            'Original Foods',
            'Oshee',
            'OTHER (For a Brand that is not in the picklist)',
            'Otrivin',
            'Pablo',
            'Pabst',
            'Pacific',
            'Pallmall',
            'Palm',
            'Palm Bay',
            'Palmolive',
            'Pampers',
            'Panadero',
            'Panadol',
            'Panago',
            'Panasonic',
            'Panter',
            'Papa Johns',
            'Parliament',
            'Partyballoons',
            'Pasante',
            'Passoa',
            'Paturain',
            'Paulaner',
            'Paynes Dairies',
            'PC Optimum',
            'Peace Tea',
            'Peachtree',
            'Pedialyte',
            'Pedigree',
            'Peijnenburg',
            'Pelican Rouge',
            'Peperami',
            'Pepsi',
            'Perla',
            'Perlenbacher',
            'Peroni',
            'Perrier',
            'Petro Canada',
            'Pfanner',
            'PG Tips',
            'Phillip Morris',
            'Piacetto',
            'Pickwick',
            'Picnic',
            'Piknik',
            'Pims',
            'Pinar',
            'Pine',
            'Pipers',
            'Piramide',
            'Pitt',
            'PixyStix',
            'Planters',
            'Players',
            'Playtex',
            'Pleinsud',
            'Plus',
            'Pocky',
            'Pokemon',
            'Polaroid',
            'Pombar',
            'Poms',
            'Popchips',
            'Popsicle',
            'Poptarts',
            'Pot Noodle',
            'Power',
            'Power Fist',
            'Powerade',
            'Powerbooster',
            'Powerup',
            'Praxis',
            'Precision Fuel',
            'Premium Dutch',
            'Premium Plus',
            'Presidents Choice',
            'Prevalin',
            'Prime',
            'Prince',
            'Princes Gate',
            'Princess',
            'Princess Auto',
            'Pringles',
            'Profile',
            'Promedix Medical Supplies',
            'Pucukharum',
            'Purdys',
            'Puff',
            'Pukka',
            "Purdey's",
            'Pure',
            'Pure Fruit',
            'Pure Life',
            'Pure Protein',
            'Puregold',
            'PurePlus',
            'Puresoft',
            'Purex',
            'Puschkin',
            'Pyke',
            'QNT Protein Shake',
            'Quaker',
            'Queens',
            'Quellbrunn',
            'Quemas',
            'Quiznos',
            'Raak',
            'Rademakers',
            'Radnor',
            'Raffaello',
            'Rainbow Fizz',
            'Rajec',
            'Ramune',
            'RandM',
            'Rani',
            'Rayban',
            'Rayovac',
            'Real Fruit',
            'RealLemon',
            'Realtropical',
            'Red Bull',
            'Red Square',
            'Red Thunder',
            'Redband',
            'Reeses',
            'Regina',
            'Reign',
            'Reissdorf Kolsch',
            'Relentless',
            'Remia',
            'Rescue',
            'Rexona',
            'Ribeaupierre',
            'Richmond',
            'Ricola',
            'Rijo42',
            'RingPop',
            'Rioba',
            'Ritter Sport',
            'Ritz',
            'Rivella',
            'River',
            'Rizla',
            'Robinsons',
            'Rochefort',
            'Rocheval',
            'Rockets',
            'Rockstar',
            'Rodeo',
            'Rogers',
            'RolledGoldBrand',
            'Romy',
            'Roosvicee',
            'Rothmans',
            'Royal Club',
            'Royal Dutch',
            'Ruba',
            'Rubbermaid',
            'Rubicon',
            'Ruffles',
            'Rumcola',
            'Rush',
            'Rustoleum',
            'R Whites',
            'SafetySalt',
            'Saguaro',
            'Sail Classic',
            'Saka',
            'Saklikoy',
            'Salento',
            'Salt',
            'Saltstick',
            'San Benedetto',
            'San Celestino',
            'San Miguel',
            'San Pellegrino',
            'Sanas',
            'Sanicup Medical Supplies',
            'Santa Emilia',
            'Sapporo',
            'Sappy',
            'Sarikiz',
            'Saskia',
            'SaveOnFoods',
            'Schick',
            'Schlitz',
            'Schneiders',
            'Schultenbrau',
            'Schutters',
            'Schwartzkopf',
            'Schweppes',
            'Scope',
            'Scotchman',
            'Scrumpy Jack',
            'Seabrook',
            'Sealed Air',
            'Seally',
            'Sears',
            'Second Cup',
            'Segafredo',
            'Selpak',
            'Sensx',
            'Serra De Estrela',
            'SesameSnaps',
            'Servero',
            'Shakeit',
            'Shaken Udder',
            'Shakezz',
            'Shakhsar',
            'Shakura',
            "Sharp's",
            'Sharpie',
            'Sheba',
            'Shein',
            'Shell',
            'Shepherd Neame',
            "Shepheard's Baa",
            'Sheppley Spring',
            'Shiraz',
            'Shoppers Drug Mart',
            'Sifres',
            'Sifto',
            'Siglitos',
            'Signature',
            'Sike',
            'Silifke',
            'Silver Spoon',
            'Simply Doughnuts',
            'Sinas',
            'Singha',
            'Sirma',
            'Sis',
            'Sisi',
            'Sketchers',
            'Skinny Bars',
            'Skips',
            'Skittles',
            'Skoal',
            'Skor',
            'Skyflakes',
            'Slammers',
            'Sleeman',
            'Slimpie',
            'Sloanes',
            'Slurp',
            'Slurpie',
            'Smart',
            'Smarties',
            'Smart Food',
            'Smart Water',
            'Smint',
            'Smirnoff',
            'Smiths',
            'Smoeltjes',
            'Smok',
            'Smoking',
            'Smooth',
            'Smuldier',
            'Smullers',
            'Snack A Jacks',
            'Snackrite',
            'Snaktastic',
            'Snelle Jelle',
            'Snickers',
            'Sol',
            'Solan',
            'Solevita',
            'SolMar',
            'Solo',
            'Solo Pro',
            'Somersby',
            'Sondey',
            'Sonnema',
            'Soplica',
            'Soreen',
            'Soulwater',
            'Sourcy',
            'Sour Patch Kids',
            'Sovereign',
            'Spa',
            'Spacepack',
            'Spam',
            'Span',
            'Spar',
            'Spargo',
            'Specialice',
            'Speedstar',
            'Split',
            'Sportlife',
            'Sportwater',
            'Sprite',
            'Stanley',
            'Starbucks',
            'Staropramen',
            'Starsbar',
            'Statesman',
            'Stella Artois',
            'Stelz',
            'Sterling',
            'Stimorol',
            "Stone Willy's",
            'Stowford Press',
            'Straffe Hendrik',
            'Strathrowan',
            'Strepsils',
            'Strike',
            'Strings & Things (StringsThings)',
            'Strongbow',
            'Stuyvesant',
            'Subway',
            'Sugarland',
            'Sultana',
            'Sun Bites',
            'Sun Lolly',
            'Supermalt',
            'Surango',
            'Svyturys',
            'Swan',
            'Swizzels',
            'Tadim',
            'Taft',
            'Taiko',
            'Take Off',
            'Takis',
            'Taksi',
            'Tango',
            'Tarczyn',
            'Tasting Good Vitamin Drink',
            'Tastino',
            'Tasty Tubs',
            'Tchibo',
            'Tdk',
            'Teasers',
            'Teksüt',
            'Tempo',
            "Terry's",
            'Tequila',
            'Texas',
            'Texels',
            'Theakstons',
            'The Bulldog',
            'The Energy Drink',
            'Thuisbezorgd',
            'Tictac',
            'Tiger',
            'Tikkels',
            'Tikkie',
            'Tim Hortons',
            'Time Out',
            'Tinq',
            'Toblerone',
            'Toffifee',
            'Toffix',
            'Tonys',
            'Topdrop',
            'Topo',
            'Topo Chico',
            'Torq',
            'Total',
            'Totem',
            'Trancetto',
            'Trek',
            'Tribes',
            'Trip',
            'Triplex',
            'Trocadero',
            'Trolli',
            'Troost',
            'Tropical',
            'Tropicana',
            'Troppie',
            'Tuborg',
            'Tuc',
            "Tunnock's",
            'Turner',
            'Tutku',
            'TwistedTea',
            'Twix',
            'Two Chefs Brewing',
            'Two To One',
            'Tymbark',
            'Tysk',
            'Tyskie',
            'Ufit',
            'Uggo',
            'Ukcr',
            'Ultra Chloraseptic',
            'Uludag',
            'Umbro',
            'Una',
            'Upbar',
            'Urban Eat',
            'Valeo',
            'Valvoline',
            'van A naar Beter',
            'Van Pur',
            'Vangils',
            'Vangilse',
            'Vanmelle',
            'Vannelle',
            'Vape Spot',
            'Varta',
            'Vega Libre',
            'Veltins',
            'Venco',
            'Veneto',
            'Vergers Gourmands',
            'Verkade',
            'Vezet',
            'Vicks',
            'Vidal',
            'Vidisic',
            'Vifit',
            'Viking',
            'Vimto',
            'Vio',
            'Viper',
            'Vishandel Plat',
            'Vitacoco',
            'Vitamin Well Awake',
            'Vitamin Well Defence',
            'Vitamin Well Focus',
            'Vitamin Well Hydrate',
            'Vitamin Well Refresh',
            'Vitamin Well Reload',
            'Vitamin Well Upgrade',
            'VitaminWell',
            'Viteau',
            'Vities',
            'Vittel',
            'Vive',
            'Vlugge Japie',
            'Vluggertje',
            'Vogue',
            'Volt',
            'Volvic',
            'Vomar',
            'Vozol',
            'Vrieslollies',
            'Vuse',
            "Wall's (ice cream)",
            "Wall's (meat)",
            'Warheads',
            'Warka',
            'Warrior',
            'Warsteiner',
            'Wasa',
            'Weduwe Visser',
            'Weetabix',
            'Wenlock Spring',
            'Werthers Original',
            'West',
            'West Cornwall Pasty',
            'Westminster',
            'Westphalia',
            'Whiskas',
            'White Claw',
            'White Rock',
            'White Storm',
            'Wicky',
            'Wieckse',
            'Wild Bean Cafe',
            'Wilhelmina',
            'William Lawsons',
            'Wilkinson Sword',
            'Wilko',
            'Winston',
            'Wisla',
            'Wkd',
            'Wrigleys',
            'Wybert',
            'Wyborowa',
            'Xbull',
            'Xixo',
            'Xplosade',
            'Xoozz',
            'Xxx',
            'XXX Amsterdam Genetics',
            'Xylifresh',
            'Yakult',
            'Yazoo',
            'Yeo Valley',
            'Yogho Yogho',
            'Yoki',
            'Yono',
            'Yoohoo',
            'Yoplait',
            'Yummys',
            'Yuoto',
            'Yutty',
            'Zaanse Hoeve',
            'Zatecky',
            'Zbyszko',
            'Zeeman',
            'Zewa',
            'Zoda Drinks',
            'Zonnatura',
            'Zotadkowa',
            'Zubr',
            'Zuivelhoeve',
            'Zwitsal',
            'Zyn',
            'Zywiec',
            'ZywiecZdroj',
        ];
    }
}
