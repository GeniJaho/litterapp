<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Item::query()->insert($this->format([
            '4/6-pack yokes',
            '4/6-pack yokes (100m-Ospar-1)',
            'Aluminium Foil',
            'Aluminium Foil (100m-Ospar-81)',
            'Animal (dead)',
            'Ashtray',
            'Auto (Body Part)',
            'Auto (Fanbelt)',
            'Auto (Hubcap)',
            'Auto (Motor Oil Container)',
            'Auto (Oil Filter)',
            'Auto (Tie Down Strap)',
            'Auto (Tire)',
            'Auto (Tire Tread)',
            'Auto (Wiperblade)',
            'Auto (Wiper Fluid Container)',
            'Bag',
            'Bag (100m-Ospar-60)',
            'Bag (Fast Food)',
            'Bag (for dog Poo/faeces)',
            'Bag (small, e.g. freezer bags)',
            'Bag (small, e.g. freezer bags) (100m-Ospar-3)',
            'Bag (of Litter)',
            'Bag (Reusable)',
            'Bag (Shopping)',
            'Bag (Shopping) (100m-Ospar-2)',
            'Bag (with Poo/faeces)',
            'Bag (with Poo/faeces) (100m-Ospar-121)',
            'Bag Ends',
            'Bag Ends (100m-Ospar-112)',
            'Ball',
            'Balloon',
            'Balloon with ribbon',
            'Balloon, plastic valve, ribbons etc (100m-Ospar-49)',
            'Bar (Energy Bar)',
            'Battery',
            'BBQ, Disposable',
            'BBQ, Disposable (100m-Ospar-120)',
            'Bicycle',
            'Bicyclelock',
            'Bin',
            'Bin (Adopted)',
            'Bin (Exploded)',
            'Bin (Overflowing)',
            'Bone',
            'Bonuscard',
            'Book',
            'Boot',
            'Boot (100m-Ospar-50)',
            'Bottle',
            'Bottle (Glass, 100m-Ospar-91)',
            'Bottle (Alcohol)',
            'Bottle (Beer)',
            'Bottle (Cleaner)',
            'Bottle (Cleaner) (100m-Ospar-5)',
            'Bottle (Energy Drink)',
            'Bottle (Icetea)',
            'Bottle (Juice)',
            'Bottle (Milk)',
            'Bottle (Oil < 50cm)',
            'Bottle (Oil < 50cm) (100m-Ospar-8)',
            'Bottle (Oil > 50cm)',
            'Bottle (Oil > 50cm) (100m-Ospar-9)',
            'Bottle (Other, 100m-Ospar-12)',
            'Bottle (Plastic, 100m-Ospar-4)',
            'Bottle (Protein Drink)',
            'Bottle (Shampoo)',
            'Bottle (Shampoo) (100m-Ospar-7)',
            'Bottle (Shower Gel)',
            'Bottle (Shower Gel) (100m-Ospar-7)',
            'Bottle (Sirop)',
            'Bottle (Soda)',
            'Bottle (Spirit)',
            'Bottle (Sportsdrink)',
            'Bottle (Sun Lotion)',
            'Bottle (Sun Lotion) (100m-Ospar-7)',
            'Bottle (Thermos)',
            'Bottle (Water)',
            'Bottle (Wine)',
            'Bottle Cap',
            'Bottle Cap/Lid (Metal, 100m-Ospar-77)',
            'Bottle Cap/Lid (Plastic, 100m-Ospar-15)',
            'Bowl',
            'Box',
            'Box (Cigars)',
            'Box (Matches)',
            'Box (Pizza)',
            'Bra',
            'Bread',
            'Brush',
            'Brush (100m-Ospar-18)',
            'Brush (Paint)',
            'Brush (Paint) (100m-Ospar-73)',
            'Bucket',
            'Bucket (100m-Ospar-38)',
            'Building Insulation',
            'Bullet',
            'Bullet (Casing)',
            'Float/Buoy',
            'Float/Buoy (100m-Ospar-37)',
            'Float/Buoy (1000m-Ospar-1)',
            'Cable Tie/Tie Wrap',
            'Can',
            'Can (100m-Ospar-78)',
            'Can (Alcohol)',
            'Can (Beer)',
            'Can (Coffee)',
            'Can (Dairy)',
            'Can (Deposit: No)',
            'Can (Deposit: Yes)',
            'Can (Energy Drink)',
            'Can (Food)',
            'Can (Food) (100m-Ospar-82)',
            'Can (Ice Tea)',
            'Can (Juice)',
            'Can (Milk)',
            'Can (Soda)',
            'Can (state: crushed, deposit: No)',
            'Can (state: crushed, deposit: Yes)',
            'Can (state: dented, deposit: No)',
            'Can (state: dented, deposit: Yes)',
            'Can (state: intact, deposit: No)',
            'Can (state: intact, deposit: Yes)',
            'Can (state: shattered, deposit: No)',
            'Can (state: shattered, deposit: Yes)',
            'Can (Water)',
            'Can (Wine)',
            'Cap (Joint Tube Cap)',
            'Cap (Nitrous Canister Cap)',
            'Cassette Tape',
            'Cd',
            'Cigar Butt',
            'Cigarette Butt',
            'Cigarette Butt (100m-Ospar-64)',
            'Cigarette Filter',
            'Clothes Hanger',
            'Clothes pin',
            'Clothing',
            'Clothing (100m-Ospar-54)',
            'Clothing (1000m-Ospar-20)',
            'Coaster',
            'Coffeepad',
            'Comb',
            'Comb (100m-Ospar-18)',
            'Condom',
            'Condom (100m-Ospar-97)',
            'Confetti',
            'Confetti Cannon',
            'Construction Material e.g. tiles',
            'Construction Material e.g. tiles (100m-Ospar-94)',
            'Container',
            'Container (Antifreeze)',
            'Container (Cleaning Supplies)',
            'Container (Medication)',
            'Cooling Element',
            'Cork',
            'Cork (100m-Ospar-68)',
            'Cotton Bud Stick/Ear Swab',
            'Cotton Bud Stick/Ear Swab (100m-Ospar-98)',
            'Covid Selftest',
            'Crab/lobster pots',
            'Crab/lobster pots (Metal, 100m-Ospar-87)',
            'Crab/lobster pots (Plastic, 100m-Ospar-26)',
            'Crab/lobster pots (Wood, 100m-Ospar-71)',
            'Crab/lobster pots (Wood, 1000m-Ospar-12)',
            'Crate',
            'Crate (Plastic, 100m-Ospar-13)',
            'Crate (Wood, 100m-Ospar-70)',
            'Crate (Wood, 1000m-Ospar-13)',
            'Creamer Cup',
            'Cup',
            'Cup (Paper, Oscar-65)',
            'Cup (Plastic, Oscar-21)',
            'Cup (Beer)',
            'Cup (Coffee)',
            'Cup (Dairy)',
            'Cup (Ice)',
            'Cup (Lid still attached)',
            'Cup (Milk)',
            'Cup (Milkshake)',
            'Cup (Soda)',
            'Cup (Yoghurt)',
            'Cutlery',
            'Cutlery (Chips fork) (Wood, 100m-Ospar-72)',
            'Cutlery (100m-Ospar-22)',
            'Cutlery (Fork)',
            'Cutlery (Knife)',
            'Cutlery (Spoon)',
            'Cutlery (Spork)',
            'De-Icer',
            'Deodorant',
            'Deodorant (100m-Ospar-7)',
            'DepositInBin',
            'Diaper',
            'Dog Tag',
            'Drink Carton',
            'Drink Carton (100m-Ospar-62)',
            'Drink Carton (Icetea)',
            'Drink Carton (Juice)',
            'Drink Carton (Milk)',
            'Drink Carton (Milk) (100m-Ospar-118)',
            'Drink Carton (Water)',
            'Drink Carton (Wine)',
            'Drink Pouch',
            'Drone',
            'Drug Test',
            'Drug Test (DrugWipe)',
            'Duck Decoy',
            'Ear Plug',
            'Elastic Band',
            'Electric Appliance',
            'Electric Appliance (100m-Ospar-79)',
            'Electric Wire',
            'Event Bracelet',
            'Facemask',
            'Fertiliser/animal feed bags (100m-Ospar-23)',
            'Fireworks',
            'Fireworks (Knetterbal)',
            'Fish boxes',
            'Fish boxes (Plastic, 100m-Ospar-34)',
            'Fish boxes (Plastic, 1000m-Ospar-2)',
            'Fish boxes (Wood, 100m-Ospar-119)',
            'Fish boxes (Wood, 1000m-Ospar-24)',
            'Fishing Line (angling)',
            'Fishing Line (angling) (100m-Ospar-35)',
            'Fishing Line (angling) (1000m-Ospar-6)',
            'Fishing Weight',
            'Fishing Weight (100m-Ospar-35)',
            'Flyer',
            'Food',
            'Fruit',
            'Fruit (Apple)',
            'Fruit (Banana)',
            'Furnishing',
            'Furnishing (100m-Ospar-55)',
            'Gas Tank',
            'Gel',
            'Gel (Carbo Gel)',
            'Gel (Energy Gel)',
            'Glass',
            'Glasses',
            'Glove',
            'Glove (typical washing up gloves)',
            'Glove (typical washing up gloves) (100m-Ospar-25)',
            'Glove (industrial/professional gloves)',
            'Glove (industrial/professional gloves) (100m-Ospar-113)',
            'Glove (industrial/professional gloves) (1000m-Ospar-22)',
            'Golf Ball',
            'Gum',
            'Hair Tie',
            'Hand Sanitizer',
            'Hard Hat',
            'Hard Hat (100m-Ospar-42)',
            'Hub Cap',
            'Industrial packaging, plastic sheeting',
            'Industrial packaging, plastic sheeting (100m-Ospar-40)',
            'Industrial packaging, plastic sheeting (1000m-Ospar-3)',
            'Industrial Scrap',
            'Industrial Scrap (100m-Ospar-83)',
            'Inhaler',
            'Injection Gun Container',
            'Injection Gun Container (100m-Ospar-11)',
            'Jar',
            'Jerry Can',
            'Jerry Can (100m-Ospar-10)',
            'Jerry Can (1000m-Ospar-5)',
            'Joint',
            'Joint Tube',
            'Key',
            'Key Chain',
            'Knife',
            'label',
            'Label (for Bottle)',
            'Light Bulb/tube',
            'Light Bulb/tube (100m-Ospar-92)',
            'Light Stick',
            'Light Stick (100m-Ospar-36)',
            'Lobster and fish tags (100m-Ospar-114)',
            'Lego',
            'Lid',
            'Lid (Coffee Cup)',
            'Lid (Cup)',
            'Lid (Dairy Cup)',
            'Lid (Ice Cup)',
            'Lid (Jar)',
            'Lid (Milk Cup)',
            'Lid (Sauce Packet)',
            'Lid (Straw still attached)',
            'Lid (Yoghurt Cup)',
            'Light',
            'Light (Bicycle)',
            'Lighter',
            'Lighter (100m-Ospar-16)',
            'Lollipop',
            'Mash Vegetable Bags (100m-Ospar-24)',
            'Medicine Strip/Containers/tubes',
            'Medicine Strip/Containers/tubes (100m-Ospar-103)',
            'Mirror',
            'Mirror (broken)',
            'Money',
            'Nail',
            'Nail File',
            'Napkin',
            'Net',
            'Net and pieces of net (less than 50 cm)',
            'Net and pieces of net (less than 50 cm) (100m-Ospar-115)',
            'Net and pieces of net (less than 50 cm) (1000m-Ospar-6)',
            'Net and pieces of net (more than 50 cm)',
            'Net and pieces of net (more than 50 cm) (100m-Ospar-116)',
            'Net and pieces of net (more than 50 cm) (1000m-Ospar-6)',
            'Net (Birdfood)',
            'Net (Fishing)',
            'Newspaper or magazine',
            'Newspaper or magazine (100m-Ospar-66)',
            'Nicopods',
            'Nitrous Canister',
            'Numberplate',
            'Nurdle',
            'Octopus Pot (Ceramics, 100m-Ospar-95)',
            'Octopus Pot (Plastic, 100m-Ospar-27)',
            'Oil Drum',
            'Oil Drum (Metal, 100m-Ospar-84)',
            'Oil Drum (Plastic, 1000m-Ospar-7)',
            'Oil Drum (Metal, 1000m-Ospar-10)',
            'OTHER (For something that is not in the picklist)',
            'Other Ceramic/pottery items (100-Ospar-96)',
            'Other Glass items (100-Ospar-93)',
            'Other items (100-Ospar-111)',
            'Other Medical items (100-Ospar-105)',
            'Other Metal items (< 50cm) (100-Ospar-89)',
            'Other Metal items (> 50cm) (100-Ospar-90)',
            'Other Metal items (large) (1000-Ospar-11)',
            'Other Paper items (100-Ospar-67)',
            'Other Plastic/Polystyrene items (100-Ospar-48)',
            'Other Plastic/Polystyrene items (large) (1000-Ospar-9)',
            'Other Rubber items (100-Ospar-53)',
            'Other Rubber items (large) (1000-Ospar-18)',
            'Other Sanitary items (100-Ospar-102)',
            'Other Textile/Cloth items (100-Ospar-59)',
            'Other Textile/Cloth items (1000-Ospar-21)',
            'Other Wood items (< 50cm) (100-Ospar-74)',
            'Other Wood items (> 50cm) (100-Ospar-75)',
            'Other Wood items (large) (1000-Ospar-15)',
            'Oyster net or mussel bag including plastic stopper (100m-Ospar-28)',
            'Oyster trays (round from oyster cultures) (100m-Ospar-29)',
            'Sheeting from mussel culture (Tahitians) (100m-Ospar-30)',
            'Pacifier/Soother',
            'Pack (Cigarette)',
            'Pack (Cigarette) (100m-Ospar-63)',
            'Packaging',
            'Packaging/Food Containers (100m-Ospar-6)',
            'Packaging (Balloon)',
            'Packaging (Bubblewrap)',
            'Packaging (Candy/Sweet Wrapper)',
            'Packaging (Candy/Sweet Wrapper) (100m-Ospar-19)',
            'Packaging (Cans)',
            'Packaging (Chips Bag)',
            'Packaging (Chips Bag) (100m-Ospar-19)',
            'Packaging (Cigarette Box Wrapper)',
            'Packaging (Clam Shell)',
            'Packaging (Condom)',
            'Packaging (Facemasks)',
            'Packaging (Folded Drugs Wrapper)',
            'Packaging (Gum Container)',
            'Packaging (Gum Strip)',
            'Packaging (Lollipop Wrapper)',
            'Packaging (Nicopods)',
            'Packaging (Nitrous Canister)',
            'Packaging (Rolling papers)',
            'Packaging (Straw)',
            'Packaging (Straw, Straw still inside)',
            'Packaging (Tissue)',
            'Packaging (Vape Pen)',
            'Paint Tin',
            'Paint Tin (100m-Ospar-86)',
            'Pallet (100m-Ospar-69)',
            'Pallet (1000m-Ospar-14)',
            'Part of something',
            'Part of something (Bicycle)',
            'Part of something (Car)',
            'Part of something (Car) (100m-Ospar-14)',
            'Part of something (Motor)',
            'Part of something (Phone)',
            'Part of something (Vape Pen)',
            'Pellet (Airsoft)',
            'Pen',
            'Pen (100m-Ospar-17)',
            'Pen (Ballpoint)',
            'Pen (Marker)',
            'Pencap',
            'Pencil',
            'Phone',
            'Phoneholder',
            'Piece of Fibre Glass',
            'Piece of Fibre Glass (100m-Ospar-41)',
            'Piece of Plastic/Polystyrene pieces (0-2,5cm)',
            'Piece of Plastic/Polystyrene pieces (0-2,5cm) (100m-Ospar-117)',
            'Piece of Plastic/Polystyrene pieces (2,5-50cm)',
            'Piece of Plastic/Polystyrene pieces (2,5-50cm) (100m-Ospar-46)',
            'Piece of Plastic/Polystyrene pieces (> 50cm)',
            'Piece of Plastic/Polystyrene pieces (> 50cm) (100m-Ospar-47)',
            'Piece of wax/paraffin (0-1 cm)',
            'Piece of wax/paraffin (0-1 cm) (100m-Ospar-108)',
            'Piece of wax/paraffin (1-10 cm)',
            'Piece of wax/paraffin (1-10 cm) (100m-Ospar-109)',
            'Piece of wax/paraffin (>10 cm)',
            'Piece of wax/paraffin (>10 cm) (100m-Ospar-110)',
            'Plate',
            'Polluted Area',
            'Poo',
            'Poo (Cat)',
            'Poo (Dog)',
            'Poster',
            'Pot',
            'Propane Tank',
            'Propane Tank (1lbs)',
            'Propane Tank (20lbs)',
            'Pull Ring',
            'Receipt',
            'Reflector',
            'Ribbon',
            'Rolling Papers',
            'Rope/string/cord (diameter < 1cm)',
            'Rope/string/cord (diameter < 1cm) (100m-Ospar-32)',
            'Rope/string/cord (diameter < 1cm) (1000m-Ospar-23)',
            'Rope/string/cord (diameter > 1cm)',
            'Rope/string/cord (diameter > 1cm) (100m-Ospar-31)',
            'Rope/string/cord (diameter > 1cm) (1000m-Ospar-4)',
            'Sachet',
            'Sachet (Creamer)',
            'Sachet (Ketchup)',
            'Sachet (Mayonaise)',
            'Sachet (Mustard)',
            'Sachet (Salt)',
            'Sachet (Sugar)',
            'Sacking',
            'Sacking (100m-Ospar-56)',
            'Sandpaper',
            'Sanitary Towels/Panty Liners/Backing Strips',
            'Sanitary Towels/Panty Liners/Backing Strips (100m-Ospar-99)',
            'Saucepacket',
            'Screw',
            'Shoe',
            'Shoes/Sandals (Leather, 100m-Ospar-57)',
            'Shoes/Sandals (Plastic, 100m-Ospar-44)',
            'Shoes/Sandals (1000m-Ospar-20)',
            'Shopping Cart',
            'Shotglass',
            'Shotgun Cartridge',
            'Shotgun Cartridge (100m-Ospar-43)',
            'Sigar',
            'Sleeve (for Bottle)',
            'Snus',
            'Sock',
            'Sponge',
            'Sponge (Foam Sponge) 100m-Ospar-45)',
            'Spray Can/Aerosol',
            'Spray Can/Aerosol (100m-Ospar-76)',
            'Spray Can (Cockpitspray)',
            'Spray Can (Paint)',
            'Stick',
            'Stick (Icelolly)',
            'Stick (Lolly)',
            'Stick (Lolly) (Plastic, 100m-Ospar-19)',
            'Stick (Lolly) (Wood, 100m-Ospar-72)',
            'Sticker',
            'Stirrer',
            'Strapping band',
            'Strapping band (100m-Ospar-39)',
            'Strapping band (1000m-Ospar-8)',
            'Straw',
            'Straw (100m-Ospar-22)',
            'Styrofoam',
            'Syringe',
            'Syringe (100m-Ospar-104)',
            'Tampon/Tampon Applicator',
            'Tampon/Tampon Applicator (100m-Ospar-100)',
            'Tangled nets/cord/rope and string',
            'Tangled nets/cord/rope and string (100m-Ospar-33)',
            'Tape',
            'Tape (Caution)',
            'Tape (Duct Tape)',
            'Tape (Electrical)',
            'Tape (Flagging)',
            'Teabag',
            'Ticket',
            'Ticket (Bus)',
            'Ticket (Parking)',
            'Ticket (Train)',
            'Tile',
            'Tire',
            'Tissue',
            'Tobacco Pouch',
            'Toilet Freshener',
            'Toilet Freshener (100m-Ospar-101)',
            'Token',
            'Toothbrush',
            'Toothpaste',
            'Toothpick',
            'Toy',
            'Toy & Party Poppers (100m-Ospar-20)',
            'Toy (Nerf Bullet)',
            'Toy (Waterpistol)',
            'Tray',
            'Tray (100m-Ospar-22)',
            'Tyre',
            'Tyre and belts (100m-Ospar-52)',
            'Tyre and belts (1000m-Ospar-17)',
            'Umbrella',
            'Unidentified/Unknown',
            'Vape Oil',
            'Vape Pen',
            'Wallet',
            'Wet Wipes',
            'Wire, Wire Mesh, Barbed Wire',
            'Wire, Wire Mesh, Barbed Wire (100m-Ospar-88)',
            'Wrapper',
            'Zip Bag',
        ]));
    }

    private function format(array $items): array
    {
        return array_map(fn (string $item) => [
            'name' => $item,
            'slug' => Str::slug($item, dictionary: ['<' => 'lt', '>' => 'gt']),
            'created_at' => now(),
            'updated_at' => now(),
        ], $items);
    }
}
