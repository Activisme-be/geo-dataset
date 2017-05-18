<?php 

namespace ActivismeBe\Scraper\Commands;

use PDO;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CitiesCommand
 *
 * @package ActivismeBe\Scraper\Commands
 */
class CitiesCommand extends BaseCommand
{
    /**
     * Command configuration.
     *
     * @return void
     */
    protected function configure() 
    {
        $this->setName('scrape:cities')->setDescription('Export all the Belgian cities');
    }

    /**
     * Command execution.
     *
     * @param  InputInterface $input    An symfony InputInterface instance. 
     * @param  OutputInterface $output  An symfony OutputInterface instance.
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $query = 'SELECT t1.id, t1.postal_code, t1.city_name, t1.lat_num, t1.lng_num, 
                         t2.province_name_nl, t2.province_name_fr, t2.province_name_de
                    FROM activisme_dev.cities AS t1
                    JOIN activisme_dev.provinces as t2
                         ON t1.province_id = t2.id
                   ORDER BY t1.city_name ASC;';

        if (! $this->connection->query($query)) { // Determine if we can run the query.
            throw new RuntimeException();
        }

        $geoJson = [ // Build GeoJSON feature collection array
            'type'      => 'FeatureCollection',
            'features'  => [],
        ];

        // From here we loop through rows to build feature arrays.

        $getData = $this->connection->prepare($query);
        $getData->execute();


        while ($row = $getData->fetch(PDO::FETCH_ASSOC)) { // Loop through the results.
            $feature = [
                'id'    => $row['id'], 
                'type'  => 'Feature', 
                'geometry' => [
                    'type' => 'Point', 
                    'coordinates' => [
                        $row['lng_num'], // Latitude
                        $row['lat_num'], // Longtitude
                    ]
                ],
                'properties' => [
                    'name'        => $row['city_name'],
                    'postal_code' => $row['postal_code']
                ]
            ];

            array_push($geoJson['features'], $feature); // Add feature array to feature collection array.
        }

        // header('Content-type: application/json');
        // echo json_encode($geojson, JSON_NUMERIC_CHECK);

        $this->connection = null; // Close database collection
        $fileFullPath = __DIR__ . '/../../../dataset/json/belgain-cities.json';

        if (! file_put_contents($fileFullPath, json_encode($geoJson, JSON_NUMERIC_CHECK))) { // Try to write the file.
            throw new RuntimeException();
        }

        $output->writeln('<info>The data has been converted to a json file.</info>');
    }
}
