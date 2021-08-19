<?php


namespace arajcany\PrePressTricks\Graphics\Common;


use arajcany\PrePressTricks\Graphics\Callas\CallasCommands;
use arajcany\PrePressTricks\Graphics\Ghostscript\GhostscriptCommands;

/**
 * Class GetCommands
 *
 * A simple class that provides a static function to get either a GS or Callas Prepress Commands instance.
 *
 * @package arajcany\PrePressTricks\Graphics\Common
 */
class GetCommands
{
    /**
     * BaseCommands constructor.
     */
    public function __construct()
    {

    }

    /**
     * Return an instance of either Ghostscript or Callas Commands
     *
     * @param string $preferred 'callas' or 'ghostscript'
     * @param bool $fallback fallback to the non-preferred if preferred is not available
     * @return CallasCommands|GhostscriptCommands|false
     */
    public static function getPrepressCommands($preferred = 'ghostscript', $fallback = true)
    {
        $gs = new GhostscriptCommands();
        $callas = new CallasCommands();

        if (strtolower($preferred) == 'ghostscript') {
            if ($gs->isAlive()) {
                return $gs;
            } elseif ($fallback && $callas->isAlive()) {
                return $callas;
            } else {
                return false;
            }
        } elseif (strtolower($preferred) == 'callas') {
            if ($callas->isAlive()) {
                return $callas;
            } elseif ($fallback && $gs->isAlive()) {
                return $gs;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}