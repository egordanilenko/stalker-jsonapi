#EXTM3U
#EXT-X-TARGETDURATION:10
#EXT-X-VERSION:4
#EXT-X-MEDIA-SEQUENCE:<?php printf("%d\n",$count); ?>
<?php

foreach ($segments as $segment) {
    $format = "#EXTINF:%f,\n";
    $format_bytes = "#EXT-X-BYTERANGE:%s@%s\n";
    printf($format_bytes, ($segment->getEndByte() + 1 - $segment->getStartByte()),$segment->getStartByte());
    printf($format, $segment->getDuration());
    echo $segment->getPath() . $segment->getFileName()."\n";
}
?>