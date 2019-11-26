<?php
declare(strict_types=1);

namespace App\Services\Import;

use Envms\FluentPDO\Query;
use \PDO;


class OduDictImportService
{
    /**
     * @var Query
     */
    private $srcFPdo;
    /**
     * @var Query
     */
    private $dstFPdo;

    public function __construct(PDO $srcPdo, PDO $dstPdo)
    {
        $this->srcFPdo = new Query($srcPdo);
        $this->dstFPdo = new Query($dstPdo);
    }

    /**
     * импорт всех данных
     */
    public function import()
    {

    }

    public function importSubstations()
    {
        // TODO переменный фильтр Эренгосистемы
        $acceptedCodeFes = '101110'; // только Брестэнерго
        $oduSubstations = $this->srcFPdo
            ->from('gpops')
            ->where('code_region', $acceptedCodeFes)
            ->fetchAll();

        foreach ($oduSubstations as $substation) {
            if (0 != $this->dstFPdo
                ->from('energoObject')
                ->where('code', $substation['code'])
                ->count()
            ){
                continue;
            }

            $this->dstFPdo->insertInto('energoObject', [
                'code_region' => $acceptedCodeFes,
                'code' => $substation['code'],
                'name' => $substation['name'],
                'type' => $substation['type'],
                'voltage' => $substation['u'],
                'status' => '',
                'localcode' => $substation['code'],
            ])->execute();
        }

    }

    /**
     * импорт данных соединений между ПС/РП
     */
    public function importLinks()
    {
        $oduLinks = $this->srcFPdo
            ->from('gpovl')
            ->fetchAll();

        foreach ($oduLinks as $link) {
            // TODO переменный фильтр Эренгосистемы
            $acceptedCodeFes = '101110'; // только Брестэнерго
            if (($link['code_fes1'] != $acceptedCodeFes) && ($link['code_fes2'] != $acceptedCodeFes) && ($link['code_fes3'] != $acceptedCodeFes)) {
                // не входит в список энергосистемм линии
                continue;
            }
            $srcConn = $link['code_ps_start'] . '.' . $link['code'];
            $dstConn = $link['code_ps_end'] . '.' . $link['code'];
            $isLinkExists = (0 != $this->dstFPdo
                    ->from('energoLink')
                    ->where('code_srcConnection', $srcConn)
                    ->where('code_dstConnection', $dstConn)
                    ->count());

            if ($isLinkExists) {
                continue;
            }
            // точка подключения к ПС источник
            $this->dstFPdo
                ->insertInto('energoConnection', [
                    'code_energoObject' => $link['code_ps_start'],
                    'code' => $srcConn,
                    'name' => $srcConn,
                    'voltage' => $link['u'],
                    'direction' => '',
                    'status' => '',
                ])->execute();
            // точка подключения к ПС назначение
            $this->dstFPdo
                ->insertInto('energoConnection', [
                    'code_energoObject' => $link['code_ps_end'],
                    'code' => $dstConn,
                    'name' => $dstConn,
                    'voltage' => $link['u'],
                    'direction' => '',
                    'status' => '',
                ])->execute();

            $this->dstFPdo
                ->insertInto('energoLink', [
                    'code_srcConnection' => $srcConn,
                    'code_dstConnection' => $dstConn,
                    'code_region' => $acceptedCodeFes, // TODO ставим Брестэнерго, надо подумать что делать
                    'code' => $acceptedCodeFes . '' . $link['code'],
                    'name' => $link['name'],
                    'status' => '',
                    'localcode' => $link['code'],
                ])->execute();

        }

    }

}