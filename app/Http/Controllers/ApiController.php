<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiController extends Controller
{

    public function migrationToSql() {
        $migrationToConvert = request()->input('migration_to_convert');
        $migrationsPath = base_path("database/migrations");

        // Generate a random Request ID and use as name of migration
        $migration = strtolower($this->random_letters(15));
        Artisan::call("make:migration $migration");
        $output = Artisan::output();
        $migrationFileName = trim(explode(":",$output)[1]);
        $migrationFile = $migrationsPath . "/" . $migrationFileName . ".php";

        $lines = file($migrationFile);
        $lines[15] = $migrationToConvert;
        $newMigrationContent = implode("",$lines);
        file_put_contents($migrationFile,$newMigrationContent);

        Artisan::call("migrate --pretend");
        $commandResponse = Artisan::output();
        $splitResponse = explode(":",$commandResponse);

        // get the request ID
        $requestID = $splitResponse[0];
        $sql = $splitResponse[1];
        unlink($migrationFile);

        //clean the response

        $sql = str_replace("\n","",$sql);

        return response( array( "message" => "SQL Generated.", "data" => [
            "request_id" => $requestID,
            "sql" => $sql
            ]  ), 200 );

    }

    public function random_letters($length, $keyspace = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

}
