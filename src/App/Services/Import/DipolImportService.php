<?php
declare(strict_types=1);

namespace App\Services\Import;


use Envms\FluentPDO\Query;
use PDO;
use Psr\Log\LoggerInterface;

final class DipolImportService implements ImportServiceInterface
{
    protected $regionMatching = [
        'gan' => ['dipol' => '1', 'dwres' => '51500'],
        'lah' => ['dipol' => '2', 'dwres' => '111112'],
//        'lah' => ['dipol' => '2', 'dwres' => '51600'],
        'barg' => ['dipol' => '3', 'dwres' => '111113'],
//        'barg' => ['dipol' => '3', 'dwres' => '51100'],
        'iva' => ['dipol' => '4', 'dwres' => '51400'],
        'ber' => ['dipol' => '5', 'dwres' => '111115'],
//        'ber' => ['dipol' => '5', 'dwres' => '51300'],
        'bars' => ['dipol' => '6', 'dwres' => '111116'],
//        'bars' => ['dipol' => '6', 'dwres' => '51200'],
    ];
    /** @var Query */
    private $srcFPdo;
    /** @var Query */
    private $dstFPdo;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(PDO $srcPdo, PDO $dstPdo, LoggerInterface $logger)
    {
        $this->srcFPdo = new Query($srcPdo);
        $this->dstFPdo = new Query($dstPdo);
        // TODO костыль sqlite3 uppercase
        $dstPdo->sqliteCreateFunction('php_upper', function($string){return mb_strtoupper($string);}, 1, PDO::SQLITE_DETERMINISTIC);
        $dstPdo->sqliteCreateFunction('php_lower', function($string){return mb_strtolower($string);}, 1, PDO::SQLITE_DETERMINISTIC);
        $dstPdo->sqliteCreateFunction('php_strcmp',
            function($string1, $string2){
                $result = strcmp(mb_strtolower($string1), mb_strtolower($string2));
                return $result;
            },
            2,
            PDO::SQLITE_DETERMINISTIC
        );
        $this->logger = $logger;
    }

    public function updateGeoCoords($region = null)
    {
        $regionsDipol = $this->srcFPdo
            ->from('PDEPARTMENTS')
            ->select('PCONCERN_CODE, PRUP_CODE, PFAS_CODE, PDEPARTMENT_CODE, PDEPARTMENT_NAME, PDEPARTMENT_TYPE');
        $regionsDipol = $regionsDipol->fetchAll();

        $substationsDipol = $this->srcFPdo
            ->from('PSUBSTATIONS')
            ->select('PRUP_CODE, PFAS_CODE, P_CODE, P_NAME, P_VOLTAGE, P_TYPE, LATITUDE_X3, LONGITUDE_X3');
        $substationsDipol = $substationsDipol->fetchAll();

        foreach ($substationsDipol as $substDipol) {
            $substationForUpdate = $this->dstFPdo
                ->from('energoObject')
                ->where('type', 'ПС')
                ->where('php_strcmp(name, ?) = 0', $substDipol['P_NAME'])
                ->select('code_region, code, name')
                ->fetch();
            if (!empty($substationForUpdate)) {
                $this->logger->info('обновляем ПС ' . $substDipol['P_NAME']);
                //обновляем координаты ПС
                if (!(empty($substDipol['LATITUDE_X3'])||empty( $substDipol['LONGITUDE_X3']))) {
                    $coordsUpdated = $this->dstFPdo
                        ->update('geoCoords', ['latitude' => $substDipol['LATITUDE_X3'], 'longitude' => $substDipol['LONGITUDE_X3']])
                        ->where('code_energoObject', $substationForUpdate['code'])
                        ->execute();
                    if (!$coordsUpdated) {
                        $this->dstFPdo
                            ->insertInto('geoCoords',
                                [
                                    'code_energoObject' => $substationForUpdate['code'],
                                    'latitude' => $substDipol['LATITUDE_X3'],
                                    'longitude' => $substDipol['LONGITUDE_X3']
                                ])
                            ->execute();
                    }
                } else {
                    $this->logger->error('не заполнены координаты для ' . $substationForUpdate['code']);
                }
                $this->dstFPdo
                    ->update('energoObject', ['voltage' => $substDipol['P_VOLTAGE']])
                    ->where('code', $substationForUpdate['code'])
                    ->execute();
            }
        }

    }

    private function dwres2dipol($code): ?string
    {
        foreach ($this->regionMatching as $name => $item) {
            if (strcmp($item['dwres'], $code)) {
                return $item['dipol'];
            }
        }
        return null;
    }

    private function dipol2dwres($code): ?string
    {
        foreach ($this->regionMatching as $name => $item) {
            if (strcmp($item['dipol'], $code)) {
                return $item['dwres'];
            }
        }
        return null;
    }
}