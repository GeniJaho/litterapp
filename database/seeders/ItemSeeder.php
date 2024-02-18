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
        Item::query()->insertOrIgnore($this->format([
            '4/6-pack yokes',
            'Aluminium Foil',
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
            'Bag (Fast Food)',
            'Bag (for dog Poo/faeces)',
            'Bag (Fertiliser/animal feed)',
            'Bag (Mash Vegetable Bags)',
            'Bag (small, e.g. freezer bags)',
            'Bag (of Litter)',
            'Bag (Reusable)',
            'Bag (Shopping)',
            'Bag (with Poo/faeces)',
            'Bag Ends',
            'Ball',
            'Balloon',
            'Balloon with ribbon',
            'Bar (Energy Bar)',
            'Battery',
            'BBQ, Disposable',
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
            'Bottle',
            'Bottle (Alcohol)',
            'Bottle (Beer)',
            'Bottle (Cleaner)',
            'Bottle (Energy Drink)',
            'Bottle (Icetea)',
            'Bottle (Juice)',
            'Bottle (Milk)',
            'Bottle (Oil < 50cm)',
            'Bottle (Oil > 50cm)',
            'Bottle (Protein Drink)',
            'Bottle (Shampoo)',
            'Bottle (Shower Gel)',
            'Bottle (Sirop)',
            'Bottle (Soda)',
            'Bottle (Spirit)',
            'Bottle (Sportsdrink)',
            'Bottle (Sun Lotion)',
            'Bottle (Thermos)',
            'Bottle (Water)',
            'Bottle (Wine)',
            'Bottle Cap/Lid',
            'Bowl',
            'Box',
            'Box/Pack (Cigars)',
            'Box (Matches)',
            'Box (Pizza)',
            'Bra',
            'Bread',
            'Brush',
            'Brush (Paint)',
            'Bucket',
            'Building Insulation',
            'Bullet',
            'Bullet (Casing)',
            'Float/Buoy',
            'Cable Tie/Tie Wrap',
            'Can',
            'Can (Alcohol)',
            'Can (Beer)',
            'Can (Coffee)',
            'Can (Dairy)',
            'Can (Deposit: No)',
            'Can (Deposit: Yes)',
            'Can (Energy Drink)',
            'Can (Food)',
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
            'Cigarette Filter',
            'Clothes Hanger',
            'Clothes pin',
            'Clothing',
            'Coaster',
            'Coffeepad',
            'Comb',
            'Condom',
            'Confetti',
            'Construction Material e.g. tiles',
            'Container',
            'Container (Antifreeze)',
            'Container (Cleaning Supplies)',
            'Container (Medication)',
            'Cooling Element',
            'Cork',
            'Cotton Bud Stick/Ear Swab',
            'Covid Selftest',
            'Crab/lobster pots',
            'Crate',
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
            'Cutlery (Fork)',
            'Cutlery (Knife)',
            'Cutlery (Spoon)',
            'Cutlery (Spork)',
            'De-Icer',
            'Deodorant',
            'DepositInBin',
            'Diaper',
            'Dog Tag',
            'Drink Carton',
            'Drink Carton (Icetea)',
            'Drink Carton (Juice)',
            'Drink Carton (Milk)',
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
            'Electric Wire',
            'Event Bracelet',
            'Facemask',
            'Fireworks',
            'Fireworks (Knetterbal)',
            'Fish boxes',
            'Fishing Line (angling)',
            'Fishing Weight',
            'Flyer',
            'Food',
            'Fruit',
            'Fruit (Apple)',
            'Fruit (Banana)',
            'Furnishing',
            'Gas Tank',
            'Gel',
            'Gel (Carbo Gel)',
            'Gel (Energy Gel)',
            'Glass',
            'Glasses',
            'Glove',
            'Glove (typical washing up gloves)',
            'Glove (industrial/professional gloves)',
            'Golf Ball',
            'Gum',
            'Hair Tie',
            'Hand Sanitizer',
            'Hard Hat',
            'Hub Cap',
            'Industrial packaging, plastic sheeting',
            'Industrial Scrap',
            'Inhaler',
            'Injection Gun Container',
            'Jar',
            'Jerry Can',
            'Joint',
            'Joint Tube',
            'Key',
            'Key Chain',
            'Knife',
            'label',
            'Label (for Bottle)',
            'Light Bulb/tube',
            'Light Stick',
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
            'Lollipop',
            'Medical (Other Medical items)',
            'Medicine Strip/Containers/tubes',
            'Mirror',
            'Mirror (broken)',
            'Money',
            'Nail',
            'Nail File',
            'Napkin',
            'Net',
            'Net and pieces of net (less than 50 cm)',
            'Net and pieces of net (more than 50 cm)',
            'Net (Birdfood)',
            'Net (Fishing)',
            'Newspaper or magazine',
            'Nicopods',
            'Nitrous Canister',
            'Numberplate',
            'Nurdle',
            'Oil Drum',
            'OTHER (For something that is not in the picklist)',
            'Other Sanitary items',
            'Other Textile/Cloth items',
            'Oyster net or mussel bag including plastic stopper',
            'Oyster trays (round from oyster cultures)',
            'Sheeting from mussel culture (Tahitians)',
            'Pacifier/Soother',
            'Pack/Box (Cigarette)',
            'Packaging',
            'Packaging (Balloon)',
            'Packaging (Bubblewrap)',
            'Packaging (Candy/Sweet Wrapper)',
            'Packaging (Cans)',
            'Packaging (Chips Bag)',
            'Packaging (Cigarette Box Wrapper)',
            'Packaging (Clam Shell)',
            'Packaging (Condom)',
            'Packaging (Facemasks)',
            'Packaging (Folded Drugs Wrapper)',
            'Packaging (Food Container)',
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
            'Pallet',
            'Part of something',
            'Part of something (Bicycle)',
            'Part of something (Car)',
            'Part of something (Motor)',
            'Part of something (Phone)',
            'Part of something (Vape Pen)',
            'Pellet (Airsoft)',
            'Pen',
            'Pen (Ballpoint)',
            'Pen (Marker)',
            'Pencap',
            'Pencil',
            'Phone',
            'Phoneholder',
            'Piece of <add material>',
            'Piece of <add material> (0 - 1cm)',
            'Piece of <add material> (1 - 2,5cm)',
            'Piece of <add material> (2,5 - 10cm)',
            'Piece of <add material> (10 - 50cm)',
            'Piece of <add material> (50cm-..)',
            'Plate',
            'Polluted Area',
            'Poo',
            'Poo (Cat)',
            'Poo (Dog)',
            'Poster',
            'Pot',
            'Pot (Octopus Pot)',
            'Propane Tank',
            'Propane Tank (1lbs)',
            'Propane Tank (20lbs)',
            'Pull Ring',
            'Receipt',
            'Reflector',
            'Ribbon',
            'Rolling Papers',
            'Rope/string/cord (diameter < 1cm)',
            'Rope/string/cord (diameter > 1cm)',
            'Sachet',
            'Sachet (Creamer)',
            'Sachet (Ketchup)',
            'Sachet (Mayonaise)',
            'Sachet (Mustard)',
            'Sachet (Salt)',
            'Sachet (Sugar)',
            'Sacking',
            'Sandpaper',
            'Sanitary Towels/Panty Liners/Backing Strips',
            'Saucepacket',
            'Screw',
            'Shoe/Sandal',
            'Shopping Cart',
            'Shotglass',
            'Shotgun Cartridge',
            'Sigar',
            'Sleeve (for Bottle)',
            'Snus',
            'Sock',
            'Sponge',
            'Spray Can/Aerosol',
            'Spray Can (Cockpitspray)',
            'Spray Can (Paint)',
            'Stick',
            'Stick (Icelolly)',
            'Stick (Lolly)',
            'Sticker',
            'Stirrer',
            'Strapping band',
            'Straw',
            'Styrofoam',
            'Syringe',
            'Tag (Lobster and fish tags)',
            'Tampon/Tampon Applicator',
            'Tangled nets/cord/rope and string',
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
            'Token',
            'Toothbrush',
            'Toothpaste',
            'Toothpick',
            'Toy',
            'Toy (Nerf Bullet)',
            'Toy (Party Poppers/Confetti Cannon)',
            'Toy (Waterpistol)',
            'Tray',
            'Tyre',
            'Umbrella',
            'Unidentified/Unknown/Other',
            'Vape Oil',
            'Vape Pen',
            'Wallet',
            'Wet Wipes',
            'Wire, Wire Mesh, Barbed Wire',
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
