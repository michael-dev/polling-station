<?php

if(!function_exists('str_putcsv')) {
  function str_putcsv($input, $delimiter = ',', $enclosure = '"')
  {
    $fp = fopen('php://temp', 'r+');
    fputcsv($fp, $input, $delimiter, $enclosure);
    rewind($fp);
    $data = "";
    while (!feof($fp))
      $data .= fread($fp, 4096);
    fclose($fp);
    return rtrim($data, "\n");
  }
}

