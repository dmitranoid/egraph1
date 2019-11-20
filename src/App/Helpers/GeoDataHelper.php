<?php
declare(strict_types=1);

namespace App\Helpers;


use Webmozart\Assert\Assert;

class GeoDataHelper
{
    /**
     * @var array
     */
    private $boundRect;
    /**
     * @var array
     */
    private $viewPointCoords;

    /**
     * @var float $scale coords transformation scale
     */
    private $scale;

    /**
     * GeoDataHelper constructor.
     * @param array $boundRect
     * [
     *  'lefttop'=>['latitude'=> 53.350,'longitude'=> 24.650],
     *  'rightbottom'=>['latitude'=> 52.400,'longitude'=> 25.185],
     * ]
     * @param array $viewPointCoords
     * ['width'=>1920, 'height'=>1080]
     */
    public function __construct(Array $boundRect, Array $viewPointCoords)
    {
        Assert::keyExists($boundRect, 'lefttop', 'неправильный входной массив');
        Assert::keyExists($boundRect, 'rightbottom', 'неправильный входной массив');
        Assert::keyExists($viewPointCoords, 'width', 'неправильный входной массив');
        Assert::keyExists($viewPointCoords, 'height', 'неправильный входной массив');

        $this->boundRect = $boundRect;
        $this->viewPointCoords = $viewPointCoords;
        $this->scale = $this->calcScale($boundRect, $viewPointCoords);
    }

    private function calcScale(Array $boundRect, Array $viewPointCoords): float
    {
        $widthScale = $viewPointCoords['width'] / abs($boundRect['rightbottom']['latitude'] - $boundRect['lefttop']['latitude']);
        $heightScale = $viewPointCoords['height'] / abs($boundRect['rightbottom']['longitude'] - $boundRect['lefttop']['longitude']);
        return max($widthScale, $heightScale);
    }

    /**
     * @param $latitude
     * @param $longitude
     * @return array | null
     * ['x'=>xScreenCoord, 'y'=>yScreenCoord]
     */
    public function coords2pixels($latitude, $longitude): ?array
    {
        if (empty($latitude) || empty($longitude)) {
            return null;
        }
        $x = floor(($longitude - $this->boundRect['lefttop']['longitude'] ) * $this->scale);
        $y = floor(($this->boundRect['lefttop']['latitude'] - $latitude) * $this->scale);
        return ['x' => $x, 'y' => $y];
    }
}