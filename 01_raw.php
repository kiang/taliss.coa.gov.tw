<?php
$layers = array(
  '農業設施_104' => '{"source":{"type":"dataLayer","dataSource":{"type":"table","workspaceId":"LANDUSE","dataSourceName":"AgriFaci"}}}',
  '集村農舍_103' => '{"source":{"type":"dataLayer","dataSource":{"type":"table","workspaceId":"LANDUSE","dataSourceName":"VFH103"}}}',
  '不利農業經營得設綠能農地_106' => '{"source":{"type":"dataLayer","dataSource":{"type":"table","workspaceId":"LANDUSE","dataSourceName":"得設置綠能106"}}}',
  '有機農場分布_98' => '{"source":{"type":"dataLayer","dataSource":{"type":"table","workspaceId":"LANDUSE","dataSourceName":"OrganicF"}}}',
  '養殖漁業_103' => '{"source":{"type":"dataLayer","dataSource":{"type":"table","workspaceId":"LANDUSE","dataSourceName":"Fish"}}}',
  '畜牧場分布_103' => '{"source":{"type":"dataLayer","dataSource":{"type":"table","workspaceId":"LANDUSE","dataSourceName":"Animal1"}}}',
  '休閒農業區_104' => '{"source":{"type":"dataLayer","dataSource":{"type":"table","workspaceId":"LANDUSE","dataSourceName":"Leisure"}}}',
  '休閒農場_104' => '{"source":{"type":"dataLayer","dataSource":{"type":"table","workspaceId":"LANDUSE","dataSourceName":"休閒農場"}}}',
  '農業經營專區_104' => '{"source":{"type":"dataLayer","dataSource":{"type":"table","workspaceId":"LANDPOLITIC","dataSourceName":"ABS"}}}',
  '農作_104' => '{"source":{"type":"dataLayer","dataSource":{"type":"table","workspaceId":"LANDPOLITIC","dataSourceName":"Trace_Ag"}}}',
  '漁業_104' => '{"source":{"type":"dataLayer","dataSource":{"type":"table","workspaceId":"LANDPOLITIC","dataSourceName":"Trace_Fi"}}}',
  '農村再生社區_104' => '{"source":{"type":"dataLayer","dataSource":{"type":"table","workspaceId":"LANDPOLITIC","dataSourceName":"農村再生社區範圍"}}}',
);
foreach($layers AS $layerName => $layerParam) {
  $layerId = $layerName;
  $idFile = __DIR__ . '/raw/' . $layerId . 'Id';
  $layerPath = __DIR__ . '/raw/' . $layerId;
  if(!file_exists($layerPath)) {
    mkdir($layerPath, 0777, true);
  }
  if(!file_exists($idFile)) {
    file_put_contents($idFile, '0');
  }
  $layerParam = urlencode($layerParam);
  $lastId = intval(file_get_contents($idFile));
  $objects = array();

  while(++$lastId) {
    $objects[] = $lastId;
    if($lastId % 200 === 0) {
      $targetFile = $layerPath . '/data_' . $lastId . '.json';
      if(!file_exists($targetFile)) {
        $q = implode(',', $objects);
        $json = gzdecode(shell_exec("curl -k 'http://taliss.coa.gov.tw/arcgis/rest/services/talis_dynalayer/MapServer/dynamicLayer/query?layer={$layerParam}&objectIds={$q}&outFields=*&returnGeometry=true&f=json' -H 'Host: taliss.coa.gov.tw' -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:54.0) Gecko/20100101 Firefox/54.0' -H 'Accept: */*' -H 'Accept-Language: en-US,en;q=0.5' -H 'Accept-Encoding: gzip, deflate, br' -H 'Content-Type: application/x-www-form-urlencoded' -H 'Referer: http://taliss.coa.gov.tw/ALIES/index.aspx' -H 'Connection: keep-alive'"));
        $obj = json_decode($json, true);
        if(!isset($obj['features'][0])) {
          file_put_contents($idFile, $lastId);
          echo "{$layerId} done";
          break;
        }
        echo "processing {$layerId}/{{$lastId}}\n";
        file_put_contents($targetFile, $json);
      }
      $objects = array();
      file_put_contents($idFile, $lastId);
    }
  }
}
