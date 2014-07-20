<?php

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$db_file = __DIR__ . '/../database.sqlite';
$createSchema = false;

if ( ! file_exists($db_file)) {
    touch($db_file);
    $createSchema = true;
}

$capsule->addConnection([
    'driver'   => 'sqlite',
    'database' => $db_file,
    'prefix'   => '',
]);

// Set the event dispatcher used by Eloquent models...
$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods...
$capsule->setAsGlobal();

// Setup the Eloquent ORM...
$capsule->bootEloquent();

if ($createSchema === true) {
    Capsule::schema()->create('gamertags', function($table) {
        $table->increments('id');
        $table->string('gamertag', 255)->index()->unique();
        $table->string('xuid', 255)->nullable()->index()->unique();
        $table->text('error')->nullable();
        $table->timestamps();
    });
}

// Setup our relationship models
class Gamertag extends Model {
    protected $table = 'gamertags';
    protected $hidden = [];
    protected $fillable = ['gamertag', 'xuid', 'error'];
}

if ($createSchema === true) {
    $dir = realpath(__DIR__ . '/../');
    $file = "{$dir}/" . GAMERTAGS_CSV;

    try {
        $csv = new SplFileObject($file, 'r');
    } catch (RuntimeException $e) {
        printf("Error opening .csv: %s\n", $e->getMessage());
        exit;
    }

    while(!$csv->eof() && ($row = $csv->fgetcsv()) && $row[0] !== null)
    {
        print "Importing gamertag: {$row[0]}\n";

        Gamertag::create([
            'gamertag' => $row[0],
        ]);
    }

    $count = Gamertag::all()->count();
    print "\nImported {$count} gamertags\n\n";
}
