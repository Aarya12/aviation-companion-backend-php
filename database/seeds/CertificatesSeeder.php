<?php

use Illuminate\Database\Seeder;

class CertificatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $keys = collect([
            'name',
        ]);
        $values = [
            [
                'Certificate For 500ft Fly'
            ],
            [
                'Certificate For 200ft Fly'
            ],
            [
                'Certificate For 100ft Fly'
            ]
        ];


        foreach ($values as $key => $value) {
            $data = $keys->combine($value);
            DB::table('certificates')->insert($data->all());
        }
    }
}
