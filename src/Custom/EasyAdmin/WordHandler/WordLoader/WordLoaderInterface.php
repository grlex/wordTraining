<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 30.11.2017
 * Time: 13:39
 */
namespace Custom\EasyAdmin\WordHandler\WordLoader;

use Symfony\Component\HttpFoundation\File\File;

interface WordLoaderInterface {
    const DIALECT_UK = 1;
    const DIALECT_US = 2;
    /**
     * @param string $textSpelling
     * @return string
     */
    public function loadTranslation($textSpelling);
    /**
     * @param string $textSpelling
     * @return string
     */
    public function loadTranscription($textSpelling, $dialect = self::DIALECT_UK);
    /**
     * @param string $textSpelling
     * @return File
     */
    public function loadPronounce($textSpelling, $dialect = self::DIALECT_UK);
}