#EXTM3U
#EXT-X-TARGETDURATION:10
#EXT-X-VERSION:4
#EXT-X-MEDIA-SEQUENCE:<?php printf("%d\n",$count); ?>
<?php

/**
 * @var $segments \Model\archive\Segment[]
 */

if(count($segments)>0){
    $datetime = new DateTime();
    $datetime->setTimestamp(intval($segments[0]->getStartTime(),0));
    echo '#EXT-X-PROGRAM-DATE-TIME:'.$datetime->format('Y-m-d\TH:i:sP').PHP_EOL;
}
foreach ($segments as $segment) {
    $format = "#EXTINF:%f,\n";
    $format_bytes = "#EXT-X-BYTERANGE:%s@%s\n";
    printf($format_bytes, ($segment->getEndByte() + 1 - $segment->getStartByte()),$segment->getStartByte());
    printf($format, $segment->getDuration());
    echo $segment->getPath() . $segment->getFileName()."\n";
}
?>