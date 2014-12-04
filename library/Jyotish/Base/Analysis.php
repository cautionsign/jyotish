<?php
/**
 * @link      http://github.com/kunjara/jyotish for the canonical source repository
 * @license   GNU General Public License version 2 or later
 */

namespace Jyotish\Base;

use Jyotish\Graha\Graha;
use Jyotish\Varga\Varga;
use Jyotish\Tattva\Karaka;

/**
 * Main class for analysis of horoscopes.
 *
 * @author Kunjara Lila das <vladya108@gmail.com>
 */
class Analysis {
    /**
     * Parashara system
     */
    const SYSTEM_PARASHARA = 'parashara';
    /**
     * Jaimini system
     */
    const SYSTEM_JAIMINI = 'jaimini';
    
    /**
     * Analyzed data.
     * 
     * @var array
     */
    protected $ganitaData = array();

    /**
     * Varga data.
     * 
     * @var array
     */
    protected $vargaData = array();


    /**
     * Constructor
     * 
     * @param \Jyotish\Base\Data|array $data
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($data) {
        if(
            (is_object($data) && !($data instanceof \Jyotish\Base\Data)) ||
            (!is_object($data) && !is_array($data))
        ){
            throw new Exception\InvalidArgumentException(
                "Data should be an array or instance of Jyotish\\Base\\Data"
            );
        }

        if (is_object($data)) {
            $this->ganitaData = $data->getData();
        }else{
            $this->ganitaData = $data;
        }
    }

    /**
     * Get chara karaka.
     * 
     * @param bool $reverse
     * @param string $system Jyotish system
     * @return array
     * @see Maharishi Parashara. Brihat Parashara Hora Shastra. Chapter 32, Verse 13-17.
     * @see Maharishi Jaimini. Jaimini Upadesha Sutras. Chapter 1, Quarter 1, Verse 11-18
     */
    public function getCharaKaraka($reverse = false, $system = self::SYSTEM_PARASHARA)
    {
        $grahas = $this->ganitaData['graha'];
        unset($grahas[Graha::KEY_KE]);
        switch($system){
            case self::SYSTEM_JAIMINI:
                unset($grahas[Graha::KEY_RA]);
                break;
            case self::SYSTEM_PARASHARA:
            default:
                $grahas[Graha::KEY_RA]['degree'] = 30 - $grahas[Graha::KEY_RA]['degree'];
        }

        uasort($grahas, 
            function ($d1, $d2){
                if($d1['degree'] == $d2['degree']) {
                    return 0;
                }else{
                    return ($d1['degree'] < $d2['degree']) ? 1 : -1;
                }
            }
        );
        
        $i = 0;
        $karakas = Karaka::karakaList($system);
        foreach($grahas as $key => $data){
            $grahaKaraka[$key] = $karakas[$i];
            $i += 1;
        }

        if($reverse){
            return array_flip($grahaKaraka);
        }else{
            return $grahaKaraka;
        }
    }

    /**
     * Get karakamsha.
     * 
     * @return int
     * @see Maharishi Parashara. Brihat Parashara Hora Shastra. Chapter 33, Verse 1.
     */
    public function getKarakamsha()
    {
        $d9Data = $this->getVargaData();
        $atmaKaraka = $this->getCharaKaraka(true)[Karaka::NAME_ATMA];

        return $d9Data['graha'][$atmaKaraka]['rashi'];
    }

    /**
     * Get lagnamsha.
     * 
     * @return int
     */
    public function getLagnamsha()
    {
        $d9Data = $this->getVargaData();

        return $d9Data['extra'][Graha::KEY_LG]['rashi'];
    }

    /**
     * Get varga data.
     * 
     * @param string $varga
     * @return array
     */
    public function getVargaData($varga = 'd9')
    {
        $v = strtoupper($varga);
        if(!isset($this->vargaData[$v])){
            $Varga = Varga::getInstance(($v));
            $this->vargaData[$v] = $Varga->getVargaData($this->ganitaData);
        }
        return $this->vargaData[$v];
    }
}
