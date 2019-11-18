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
        'lah' => ['dipol' => '2', 'dwres' => '51600'],
        'barg' => ['dipol' => '3', 'dwres' => '51100'],
        'iva' => ['dipol' => '4', 'dwres' => '51400'],
        'ber' => ['dipol' => '5', 'dwres' => '51300'],
        'bars' => ['dipol' => '6', 'dwres' => '51200'],
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
        $this->logger = $logger;
    }

    public function updateGeoCoords($region = null)
    {
        $regions = $this->srcFPdo
            ->from('PDEPARTMENTS')
            ->select('PCONCERN_CODE, PRUP_CODE, PFAS_CODE, PDEPARTMENT_CODE, PDEPARTMENT_NAME, PDEPARTMENT_TYPE');
        $regions = $regions->fetchAll();

        $substations = $this->srcFPdo
            ->from('PSUBSTATIONS')
            ->select('PRUP_CODE, PFAS_CODE, P_CODE, P_NAME, P_VOLTAGE, P_TYPE, LATITUDE_X3, LONGITUDE_X3');
        $substations = $substations->fetchAll();

        foreach ($substations as $substation) {
            $substationForUpdate = $this->dstFPdo
                ->from('energoObject')
                ->where('type', 'ПС')
                ->where('name', $substation['P_NAME'])
                ->select('code_region, code, name')
                ->fetch();
            if (!empty($substationForUpdate)) {
                $this->logger->info('обновляем ПС ' . $substation['P_NAME']);
                //обновляем координаты ПС
                $coordsUpdated = $this->dstFPdo
                    ->update('geoCoords', ['latitude' => $substation['LATITUDE_X3'], 'longitude' => $substation['LONGITUDE_X3']])
                    ->where('code_energoObject', $substationForUpdate['code'])
                    ->execute();
                if (!$coordsUpdated) {
                    $this->dstFPdo
                    ->insertInto('geoCoords',
                        [
                            'code_energoObject' => $substationForUpdate['code'],
                            'latitude' => $substation['LATITUDE_X3'],
                            'longitude' => $substation['LONGITUDE_X3']
                        ])
                    ->execute();
                }
                $this->dstFPdo
                    ->update('energoObject', ['voltage' => $substation['P_VOLTAGE']])
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