<?php

include("includes/graphs/common.inc.php");

$i = 0;
if($width > "500")
{
  $descr_len=18;
} else {
  $descr_len=8;
  $descr_len += round(($width - 260) / 9.5);
}

$unit_text = "Bits/sec";

if($width > "500")
{
  $rrd_options .= " COMMENT:'".substr(str_pad($unit_text, $descr_len+5),0,$descr_len+5)."    Current      Average     Maximum      '";
  if (!$nototal) { $rrd_options .= " COMMENT:'Total      '"; }
  $rrd_options .= " COMMENT:'\l'";
} else {
  $rrd_options .= " COMMENT:'".substr(str_pad($unit_text, $descr_len+5),0,$descr_len+5)."     Now         Ave          Max\l'";

}

if(!isset($multiplier)) { $multiplier = "8"; }

foreach ($rrd_list as $rrd)
{
  if (!$config['graph_colours'][$colours_in][$iter] || !$config['graph_colours'][$colours_out][$iter]) { $iter = 0; }

  if(strlen($rrd['colour_in']))  { $colour_in  = $rrd['colour_in'];  } else { $colour_in  = $config['graph_colours'][$colours_in][$iter]; }
  if(strlen($rrd['colour_out'])) { $colour_out = $rrd['colour_out']; } else { $colour_out = $config['graph_colours'][$colours_out][$iter]; }

  if (isset($rrd['descr_in']))
  {
    $descr     = rrdtool_escape($rrd['descr_in'], $descr_len) . "  In";
  } else {
    $descr     = rrdtool_escape($rrd['descr'], $descr_len) . "  In";
  }
  $descr_out = rrdtool_escape($rrd['descr_out'], $descr_len) . " Out";
  $descr     = str_replace("'", "", $descr); // FIXME does this mean ' should be filtered in rrdtool_escape? probably...
  $descr_out = str_replace("'", "", $descr_out);

  $rrd_options .= " DEF:".$in.$i."=".$rrd['filename'].":".$ds_in.":AVERAGE ";
  $rrd_options .= " DEF:".$out.$i."=".$rrd['filename'].":".$ds_out.":AVERAGE ";
  $rrd_options .= " CDEF:inB".$i."=in".$i.",$multiplier,* ";
  $rrd_options .= " CDEF:outB".$i."=out".$i.",$multiplier,*";
  $rrd_options .= " CDEF:outB".$i."_neg=outB".$i.",-1,*";
  $rrd_options .= " CDEF:octets".$i."=inB".$i.",outB".$i.",+";

  $in_thing .= $seperator . $in.$i . ",UN,0," . $in.$i . ",IF";
  $out_thing .= $seperator . $out.$i . ",UN,0," . $out.$i . ",IF";
  $pluses .= $plus;
  $seperator = ",";
  $plus = ",+";

  if (!$args['nototal'])
  {
    $rrd_options .= " VDEF:totin".$i."=inB".$i.",TOTAL";
    $rrd_options .= " VDEF:totout".$i."=outB".$i.",TOTAL";
    $rrd_options .= " VDEF:tot".$i."=octets".$i.",TOTAL";
  }

  if ($i) { $stack="STACK"; }

  $rrd_options .= " AREA:inB".$i."#" . $colour_in . ":'" . $descr . "':$stack";
  $rrd_options .= " GPRINT:inB".$i.":LAST:%6.2lf%s$units";
  $rrd_options .= " GPRINT:inB".$i.":AVERAGE:%6.2lf%s$units";
  $rrd_options .= " GPRINT:inB".$i.":MAX:%6.2lf%s$units";

  if (!$nototal && $width > "500") { $rrd_options .= " GPRINT:totin".$i.":%6.2lf%s$total_units"; }

  $rrd_options .= " 'COMMENT:\\n'";

  $rrd_options  .= " 'HRULE:0#" . $colour_out.":".$descr_out."'";
  $rrd_optionsb .= " 'AREA:outB".$i."_neg#" . $colour_out . "::$stack'";
  $rrd_options  .= " GPRINT:outB".$i.":LAST:%6.2lf%s$units";
  $rrd_options  .= " GPRINT:outB".$i.":AVERAGE:%6.2lf%s$units";
  $rrd_options  .= " GPRINT:outB".$i.":MAX:%6.2lf%s$units";

  if (!$nototal && $width > "500") { $rrd_options .= " GPRINT:totout".$i.":%6.2lf%s$total_units"; }
  $rrd_options .= " 'COMMENT:\\n'";

  $i++; $iter++;
}

  $rrd_options .= " CDEF:".$in."octets=" . $in_thing . $pluses;
  $rrd_options .= " CDEF:".$out."octets=" . $out_thing . $pluses;
  $rrd_options .= " CDEF:doutoctets=outoctets,-1,*";
  $rrd_options .= " CDEF:inbits=inoctets,8,*";
  $rrd_options .= " CDEF:outbits=outoctets,8,*";
  $rrd_options .= " CDEF:doutbits=doutoctets,8,*";
  $rrd_options .= " VDEF:95thin=inbits,95,PERCENT";
  $rrd_options .= " VDEF:95thout=outbits,95,PERCENT";
  $rrd_options .= " VDEF:d95thout=doutbits,5,PERCENT";

  $rrd_options .= " LINE1.25:inbits#005500:'Total In '";
  $rrd_options .= " GPRINT:inbits:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:inbits:AVERAGE:%6.2lf%s";
  $rrd_options .= " GPRINT:inbits:MAX:%6.2lf%s";
  $rrd_options .= " GPRINT:95thin:%6.2lf%s\\\\n";

  $rrd_options .= " LINE1.25:doutbits#000055:'".str_pad("Total Out", $descr_len+5)."'";
  $rrd_options .= " GPRINT:outbits:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:outbits:AVERAGE:%6.2lf%s";
  $rrd_options .= " GPRINT:outbits:MAX:%6.2lf%s";
  $rrd_options .= " GPRINT:95thout:%6.2lf%s\\\\n";

if ($custom_graph) { $rrd_options .= $custom_graph; }

$rrd_options .= $rrd_optionsb;
$rrd_options .= " HRULE:0#999999";

?>
