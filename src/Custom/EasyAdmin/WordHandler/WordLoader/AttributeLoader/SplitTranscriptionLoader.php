<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 19.01.2018
 * Time: 18:17
 */

namespace Custom\EasyAdmin\WordHandler\WordLoader\AttributeLoader;


use Custom\EasyAdmin\WordHandler\WordLoader\WordLoaderInterface;

class SplitTranscriptionLoader implements WordAttributeLoaderInterface {
    private $wrappedTranscriptionLoader;
    public function __construct(WordAttributeLoaderInterface $wrappedTranscriptionLoader){
        $this->wrappedTranscriptionLoader = $wrappedTranscriptionLoader;
    }
    public function load($spelling, $dialect = WordLoaderInterface::DIALECT_UK)
    {
        $transcription = $this->wrappedTranscriptionLoader->load($spelling, $dialect);
        if ($transcription == false) {
            $spellingParts = preg_split('/-|,|\.|;|:|\s|\//', $spelling);
            if (count($spellingParts) > 1) {

                $transcriptionParts = [];
                foreach ($spellingParts as $spellingPart) {
                    $spellingPart = trim($spellingPart, "()\x0B\t\r\n\"'");
                    if ($spellingPart == '') continue;
                    $spellingPart = preg_replace("/'s$/", '', $spellingPart);
                    $transcriptionPart = $this->wrappedTranscriptionLoader->load($spellingPart, $dialect);
                    $transcriptionPart = $transcriptionPart ?: '---';
                    array_push($transcriptionParts, $transcriptionPart);

                }
                $transcription = join(' ', $transcriptionParts);
            }
        }
        return $transcription;
    }
} 