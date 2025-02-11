<?php
header("content-type:text/plain");
if (php_sapi_name() != "cli") {
	echo "This program is to be called via commandline\n\n";
	echo "Usage: php /path/to/script/convert.php input.gpx output.rtz step width\n";
	echo "option: step width\n\tstep: distant between each waypoint\n\twidth: route width";
	exit;
}

if(!isset($argv[1])){
echo "Error: Need input file name\nUsage: php /path/to/script/convert.php input.gpx output.rtz\n\n";
	exit;
}
if(!isset($argv[2])){
echo "Error: Need output file name\nUsage: php /path/to/script/convert.php input.gpx output.rtz\n\n";
	exit;
}
if (isset($argv[3])){$space=$argv[3];}else{$space=0;}
if (isset($argv[4])){$width=$argv[4];}else{$width=0.37*2;}

$xml=simplexml_load_file($argv[1]);

$op="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!--route node-->\n<route version=\"1.0\" xmlns=\"http://www.cirm.org/RTZ/1/0\">\n  <!--route node-->\n";
$op.="  <routeInfo routeName=\"";
$op.=$xml->trk->name;
$op.='" optimizationMethod="MAX speed">
    <extensions>
      <extension manufacturer="Furuno" name="AdditionalRouteInfo" version="1.0">
        <property income="0" channelLimitMode="0" safetyContour="8" ukcLimit="30.000000"/>
      </extension>
    </extensions>
  </routeInfo>
  <!--waypoints node-->
  <waypoints>';
$wpt_num=1;
foreach ($xml->trk->trkseg->trkpt as $wpt){
	if ($wpt_num==1){
		$op.='
    <!--No.'.$wpt_num.' waypoint-->
    <waypoint id="'.$wpt_num.'" name="" radius="0.010000">
      <position lat="';
      $op.=$wpt['lat'];
      $op.='" lon="';
      $op.=$wpt['lon'];
      $op.='"/>
    </waypoint>';
    	$lastlat=$wpt['lat'];
    	$lastlon=$wpt['lon'];
    	$wpt_num++;
		}
	else {
		$dlat = abs($wpt['lat']-$lastlat);
		$dlon = abs($wpt['lon']-$lastlon);
		$dist = sqrt(($dlat*$dlat) + ($dlon*$dlon))*60;
		if ($dist >= $space){
			$op.='
    <!--No.'.$wpt_num.' waypoint-->
    <waypoint id="'.$wpt_num.'" name="" radius="0.010000">
      <position lat="';
      $op.=$wpt['lat'];
      $op.='" lon="';
      $op.=$wpt['lon'];
      $op.='"/>
      <leg portsideXTD="';
      $op.=$width/2;
      $op.='" starboardXTD="';
      $op.=$width/2;
      $op.='" safetyContour="8" geometryType="Loxodrome" speedMax="11.000000" draughtForward="10.000000" draughtAft="10.000000" staticUKC="30.000000"/>
      <extensions>
        <extension manufacturer="Furuno" name="AdditionalLegInfo" version="1.0">
          <property margin="0.000000" parallelLine1="0.000000" parallelLine2="0.000000"/>
        </extension>
      </extensions>
    </waypoint>';
    		$lastlat=$wpt['lat'];
    		$lastlon=$wpt['lon'];
    		$wpt_num++;
			}
		} // end else

	} //end foreach

$op.='
  </waypoints>
  <!--schedules node-->
  <schedules/>
</route>';

$f_op=fopen($argv[2],'w');
fwrite($f_op,$op);
fclose($f_op);
echo "Convert ".$xml->trk->name."\n";
echo "Convert $wpt_num waypoints\n";
echo "Data save to ".$argv[2]."\n\n";
echo "Use data as your own risk ^_^\n\n";


