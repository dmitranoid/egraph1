<?php
declare(strict_types=1);

namespace App\Services\Import;


use App\Exceptions\ApplicationException;
use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Query;
use PDO;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

final class DipolImportService implements ImportServiceInterface
{
    protected $regionMatching = [
        'gan' =>  ['dipol' => '1', 'dipolFull' => '50515101', 'dwres' => '111111'],
        'lah' =>  ['dipol' => '2', 'dipolFull' => '50515102', 'dwres' => '111112'],
        'barg' => ['dipol' => '3', 'dipolFull' => '50515103', 'dwres' => '111113'],
        'iva' =>  ['dipol' => '4', 'dipolFull' => '50515104', 'dwres' => '111114'],
        'ber' =>  ['dipol' => '5', 'dipolFull' => '50515105', 'dwres' => '111115'],
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

    /**
     * Обновить кооридинаты всего что можно, получив их из Dipol
     * @param string $region
     * @throws ApplicationException
     */
    public function updateGeoCoords($region)
    {
        Assert::notEmpty($region);
        $this->updateSubstCoordsFromDipol($region);
        $this->updateTpCoordsFromDipol($region);
    }

    /**
     * Обновить в основной БД координаты ПС из Dipol
     * @param string $region код района ЭС
     * @throws Exception
     */
    private function updateSubstCoordsFromDipol($region)
    {
        Assert::notEmpty($region);
        Assert::string($region);

        $substationsDipol = $this->srcFPdo
            ->from('PSUBSTATIONS')
            ->select('PRUP_CODE, PFAS_CODE, P_CODE, P_NAME, P_VOLTAGE, P_TYPE, LATITUDE_X3, LONGITUDE_X3');
        $substationsDipol = $substationsDipol->fetchAll();

        $topLevelObjectTypes = ['ТП', 'РУ'];
        foreach ($substationsDipol as $substDipol) {
            $substationForUpdate = $this->dstFPdo
                ->from('energoObject')
                ->where('type', $topLevelObjectTypes)
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
                    $this->pushCoordsToDb(
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
                } catch (Exception $e) {
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
    private function pushCoordsToDb($codeEnergoObject, $latitude, $longitude): void
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
     * Обновить в основной БД коридинаты ТП, взяв их из Dipol
     * @param string $region
     * @throws Exception
     */
    private function updateTpCoordsFromDipol($region)
    {
        // работать с массивом РЭС неудобно, нет возможности фильтровать ошибки поиска
        // поэтому делаем по одному РЭС, цикл по ним вне класса
        Assert::notNull($region);

        // получаем ТП из диполя
        // TODO Фидер может быть но только в VL-10 но и CL-10 !!! доделать запрос
        $tpDipolList = $this->srcFPdo
            ->from('tp')
            ->leftJoin('pdocs tpdoc on tpdoc.doc_code = tp.doc_code')
            ->leftJoin('tp_vl_10 on tp.doc_code = tp_vl_10.doc_code')
            ->leftJoin('pdocs vl10doc on vl10doc.doc_code = tp_vl_10.line_doc_code')
            ->leftJoin('tp_sys_types on tp.substation_type_id = tp_sys_types.id')
            ->select(null, true)
            ->select('tp.doc_code, type_txt tp_type, tpdoc.doc_name tp_no, address, tp_num, line_voltage, latitude_x3, longitude_x3')
            ->select('vl10doc.doc_name fider_name')
            ->where('tpdoc.template_code', 'TP')
            ->where('tp.doc_code like ?', $this->dwres2dipolFullCode($region) . '%');
        $tpDipolList = $tpDipolList->fetchAll();

        foreach ($tpDipolList as $tpDipol) {
            $tpDipol = array_map('trim', $tpDipol);
            // TODO !!! узнать как вносят в диполь делают в остальных РЭС
            // так как в dipol нет возможности выбрать просто ТП, вместо него используют ЗТП (городской РЭС)
            // поэтому исправляем ЗПТ->ТП
            if ('ЗТП' == $tpDipol['TP_TYPE']) {
                $tpDipol['TP_TYPE'] = 'ТП';
            }

            $tpDipolCode = sprintf('%s-%s', $tpDipol['FIDER_NAME'], $tpDipol['TP_NO']);
            $tpForUpdate = $this->dstFPdo
                ->from('energoObject')
                ->where('type', ['ТП', 'РП'])
                ->where('php_strcmp(localcode, ?) = 0', $tpDipolCode)
                ->where('code_region', $region)
                ->select('code, name');

            $tpForUpdate = $tpForUpdate->fetch();

            if (empty($tpForUpdate)) {
                $this->logger->warning(
                    'ТП из БД Dipol \'dipol_doc_code\' (dipol_code) не найдено в БД dwres',
                    [
                        'dipol_code' => $tpDipolCode,
                        'dipol_doc_code' => $tpDipol['DOC_CODE']
                    ]
                );
            } else {
                try {
                    if (empty($tpDipol['LATITUDE_X3']) || empty($tpDipol['LONGITUDE_X3'])) {
                        $this->logger->warning(
                            'не заполнены координаты в Dipol для \'tpname\' (tpcode, region)',
                            [
                                'tpname' => $tpForUpdate['name'],
                                'tpcode' => $tpForUpdate['code'],
                                'region' => $region,
                            ]
                        );
                    } else {
                        $this->pushCoordsToDb(
                            $tpForUpdate['code'],
                            $tpDipol['LATITUDE_X3'],
                            $tpDipol['LONGITUDE_X3']
                        );
                    }
                } catch (Exception $e) {
                    $this->logger->error(
                        'ошибка SQL при обновлении координат ТП \'tpcode\' error',
                        [
                            'tpcode' => $tpDipolCode,
                            'error' => $e->getMessage()
                        ]
                    );
                }
            }
        }
    }

    private function dwres2dipolFullCode($code): ?string
    {
        foreach ($this->regionMatching as $name => $item) {
            if (0 == strcmp($item['dwres'], $code)) {
                return $item['dipolFull'];
            }
        }
        return null;
    }

    private function dipol2dwresCode($code): ?string
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
     * Извлекает код РЭС из поля DOC_CODE Dipol
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
    private function dipolFull2dwresCode($code): ?string
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