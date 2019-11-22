<?php
declare(strict_types=1);

namespace App\Services\Import;


use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Query;
use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

final class DipolImportService implements ImportServiceInterface
{
    protected $regionMatching = [
        'gan' => ['dipol' => '1', 'dipolFull' => '50515101', 'dwres' => '111111'],
        'lah' => ['dipol' => '2', 'dipolFull' => '50515102', 'dwres' => '111112'],
        'barg' => ['dipol' => '3', 'dipolFull' => '50515103', 'dwres' => '111113'],
        'iva' => ['dipol' => '4', 'dipolFull' => '50515104', 'dwres' => '111114'],
        'ber' => ['dipol' => '5', 'dipolFull' => '50515105', 'dwres' => '111115'],
        'bars' => ['dipol' => '6', 'dipolFull' => '50515106', 'dwres' => '111116'],
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
        // TODO костыль sqlite3 uppercase, вынести в отдельный модуль
        $dstPdo->sqliteCreateFunction('php_upper', function ($string) {
            return mb_strtoupper($string);
        }, 1, PDO::SQLITE_DETERMINISTIC);
        $dstPdo->sqliteCreateFunction('php_lower', function ($string) {
            return mb_strtolower($string);
        }, 1, PDO::SQLITE_DETERMINISTIC);
        $dstPdo->sqliteCreateFunction('php_strcmp',
            function ($string1, $string2) {
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
        $this->updateSubstCoordsFromDipol($region);
        $this->updateTpCoordsFromDipol($region);
    }

    /**
     * Обновить в основной БД координаты ПС из Dipol
     * @param string|null $region код района ЭС или null для всех
     * @throws Exception
     */
    private function updateSubstCoordsFromDipol($region = null)
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
                ->select('code_region, code, name');
            if (!empty($region)) {
                // по РЭС
                $substationForUpdate->where('code_region', [$region]);
            }
            $substationForUpdate = $substationForUpdate->fetch();

            if (!empty($substationForUpdate)) {
                //обновляем координаты ПС
                if (!(empty($substDipol['LATITUDE_X3']) || empty($substDipol['LONGITUDE_X3']))) {
                    $this->updateCoordsInDb(
                        $substationForUpdate['code'],
                        $substDipol['LATITUDE_X3'],
                        $substDipol['LONGITUDE_X3']
                    );
                } else {
                    $this->logger->warning(
                        'не заполнены координаты в Dipol для ПС \'pscode\'',
                        ['pscode' => $substationForUpdate['code'] . '-' . $substationForUpdate['name']]
                    );
                }
                // TODO пока класс напряжения берем из Dipol, надо подумать откуда правильнее
                try {
                    $this->dstFPdo
                        ->update('energoObject', ['voltage' => $substDipol['P_VOLTAGE']])
                        ->where('code', $substationForUpdate['code'])
                        ->execute();
                } catch (PDOException $e) {
                    $this->logger->error(
                        'ошибка SQL при обновлении координат ПС \'pscode\' :error',
                        [
                            'pscode' => $substationForUpdate['code'],
                            'error' => $e->getMessage()
                        ]
                    );
                }
            }
        }
    }

    /**
     * Обновить или создать коодинаты объекта в БД
     * @param $codeEnergoObject
     * @param $latitude
     * @param $longitude
     * @throws Exception
     */
    private function updateCoordsInDb($codeEnergoObject, $latitude, $longitude): void
    {
        // TODO возможно нужен код РЭС
        Assert::notEmpty($codeEnergoObject);
        Assert::notEmpty($longitude);
        Assert::notEmpty($latitude);
        $coordsUpdated = $this->dstFPdo
            ->update('geoCoords', ['latitude' => $latitude, 'longitude' => $longitude])
            ->where('code_energoObject', $codeEnergoObject)
            ->execute();
        if (!$coordsUpdated) {
            $this->dstFPdo
                ->insertInto('geoCoords',
                    [
                        'code_energoObject' => $codeEnergoObject,
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ])
                ->execute();
        }

    }

    /**
     * @param $region
     * @throws Exception
     */
    private function updateTpCoordsFromDipol($region)
    {
        // все РЭСы сразу неудобно, нет возможности фильтровать ошибки
        // поэтому делаем только по СЭС
        Assert::notNull($region);

        $regionsDipol = $this->srcFPdo
            ->from('PDEPARTMENTS')
            ->select('PCONCERN_CODE, PRUP_CODE, PFAS_CODE, PDEPARTMENT_CODE, PDEPARTMENT_NAME, PDEPARTMENT_TYPE');
        $regionsDipol = $regionsDipol->fetchAll();

        //$this->dwres2dipolFull($region);

        // получаем ТП из диполя
        $tpDipolList = $this->srcFPdo
            ->from('tp')
            ->leftJoin('pdocs on pdocs.doc_code = tp.doc_code')
            ->leftJoin('tp_sys_types on tp.substation_type_id = tp_sys_types.id')
            ->select('tp.doc_code, type_txt tp_type, pdocs.doc_name tp_no, address, tp_num, line_voltage, latitude_x3, longitude_x3')
            ->where('template_code', 'TP')
            ->where('tp.doc_code like ?', $this->dwres2dipolFull($region) . '%');
        $tpDipolList = $tpDipolList->fetchAll();

        foreach ($tpDipolList as $tpDipol) {
            $tpDipol = array_map('trim', $tpDipol);
            // так как в dipol нет возможности выбрать просто ТП, вместо него используют ЗТП (городской РЭС)
            // поэтому исправляем ЗПТ->ТП
            if ('ЗТП' == $tpDipol['TP_TYPE']) {
                // TODO !!! узнать как вносят в диполь делают в остальных РЭС
                $tpDipol['TP_TYPE'] = 'ТП';
            }

            $tpDipolCode = sprintf('%s-%s', $tpDipol['TP_TYPE'], $tpDipol['TP_NO']);
            $tpForUpdate = $this->dstFPdo
                ->from('energoObject')
                ->where('type', ['ТП', 'РП'])
                ->where('php_strcmp(name, ?) = 0', $tpDipolCode)
                ->where('code_region', $region)
                ->select('code, name');

            $tpForUpdate = $tpForUpdate->fetch();

            if (empty($tpForUpdate)) {
                $this->logger->warning(
                    'ТП из БД Dipol на найден в БД dwres',
                    [
                        'dipol_code' => $tpDipolCode,
                        'dipol_doc_code' => $tpDipol['DOC_CODE']
                    ]
                );
            } else {
                try {
                    if (empty($tpDipol['LATITUDE_X3']) || empty($tpDipol['LONGITUDE_X3'])) {
                        $this->logger->warning(
                            'не найдены координаты в Dipol для \'tpcode\'',
                            [
                                'tpname' => $tpForUpdate['name'],
                                'tpcode' => $tpForUpdate['code'],
                                'region' => $region,
                            ]
                        );
                    } else {
                        $this->updateCoordsInDb(
                            $tpForUpdate['code'],
                            $tpDipol['LATITUDE_X3'],
                            $tpDipol['LONGITUDE_X3']
                        );
                    }
                } catch (PDOException $e) {
                    $this->logger->error(
                        'ошибка SQL при обновлении координат ТП \'tpcode\' :error',
                        [
                            'tpcode' => $tpDipolCode,
                            'error' => $e->getMessage()
                        ]
                    );
                }
            }
        }
    }

    private function dwres2dipolFull($code): ?string
    {
        foreach ($this->regionMatching as $name => $item) {
            if (0 == strcmp($item['dwres'], $code)) {
                return $item['dipolFull'];
            }
        }
        return null;
    }

    private function dipol2dwres($code): ?string
    {
        Assert::notEmpty($code);
        foreach ($this->regionMatching as $name => $item) {
            if (0 == strcmp($item['dipol'], $code)) {
                return $item['dwres'];
            }
        }
        return null;
    }

    /**
     * Возвращает код РЭС взятый из кода документа Dipol
     *
     * например '50515101-TP-279' -> '50515101'
     *
     * @param $docCode
     * @return string|null
     */
    private function dipolExtractRegionFromDocCode($docCode): ?string
    {
        $code = substr($docCode, 0, strpos($docCode, '-') - 1);
        // TODO на всякий случай не забыть проверить на разных РЭСах
        return strlen($code) >= 6 ? $code : null;
    }

    private function dwres2dipol($code): ?string
    {
        Assert::notEmpty($code);
        foreach ($this->regionMatching as $name => $item) {
            if (0 == strcmp($item['dwres'], $code)) {
                return $item['dipol'];
            }
        }
        return null;
    }

    /**
     * Преобразовывает полный код диполь в код dwres (ОДУ)
     *
     * например '5051513' -> '111113'
     *
     * @param $code
     * @return string|null
     */
    private function dipolFull2dwres($code): ?string
    {
        Assert::notEmpty($code);
        foreach ($this->regionMatching as $name => $item) {
            if (0 == strcmp($item['dipolFull'], $code)) {
                return $item['dwres'];
            }
        }
        return null;
    }
}