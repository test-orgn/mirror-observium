<?php

$scale_min = "0";
$scale_max = "100";

include("includes/graphs/common.inc.php");

$rrd_options .= " -b 1024";


  $iter = "1";

  $rrd_options .= " COMMENT:'                    Size      Free   % Used\\n'";

    $hostname = gethostbyid($storage['device_id']);
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; $iter = "0"; }
    $descr = substr(str_pad($storage[storage_descr], 12),0,12);
    $descr = str_replace(":","\:",$descr);
    $rrd_options .= " DEF:$storage[storage_id]used=$rrd_filename:used:AVERAGE";
    $rrd_options .= " DEF:$storage[storage_id]free=$rrd_filename:free:AVERAGE";
    $rrd_options .= " CDEF:$storage[storage_id]size=$storage[storage_id]used,$storage[storage_id]free,+";
    $rrd_options .= " CDEF:$storage[storage_id]perc=$storage[storage_id]used,$storage[storage_id]size,/,100,*";
    $rrd_options .= " LINE1.25:$storage[storage_id]perc#" . $colour . ":'$descr'";
    $rrd_options .= " GPRINT:$storage[storage_id]size:LAST:%6.2lf%sB";
    $rrd_options .= " GPRINT:$storage[storage_id]free:LAST:%6.2lf%sB";
    $rrd_options .= " GPRINT:$storage[storage_id]perc:LAST:%5.2lf%%\\\\n";
    $iter++;

?>
