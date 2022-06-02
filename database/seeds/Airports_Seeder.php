<?php

use Illuminate\Database\Seeder;

class Airports_Seeder extends Seeder
{

    public function run()
    {
        $keys = collect([
            'name',
            'keywords',

        ]);
        $values = [
            [
                'Junagadh',
                'jnd'

            ],
            [
                'Rajkot',
                'rjkt'
            ]
        ];


        foreach ($values as $key => $value) {
            $data = $keys->combine($value);
            DB::table('airports')->insert($data->all());
        }
    }
}
