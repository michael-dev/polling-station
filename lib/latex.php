<?php

function sanitizeLatex($txt) {
  $needle = Array();
  $replace = Array();

  $needle[] = "\\";
  $replace[] = "\\textbackslash";

  $needle[] = "#";
  $replace[] = "\\#";

  $needle[] = "\$";
  $replace[] = "\\\$";

  $needle[] = "%";
  $replace[] = "\\%";

  $needle[] = "&";
  $replace[] = "\\&";

  $needle[] = "_";
  $replace[] = "\\_";

  $needle[] = "{";
  $replace[] = "\\{";

  $needle[] = "}";
  $replace[] = "\\{";

  $needle[] = "~";
  $replace[] = "\\~{}";

  $needle[] = "[";
  $replace[] = "\\[";

  $needle[] = "]";
  $replace[] = "\\]";

  $needle[] = "(";
  $replace[] = "\\(";

  $needle[] = ")";
  $replace[] = "\\)";

  $needle[] = "<";
  $replace[] = "\\langle";

  $needle[] = ">";
  $replace[] = "\\rangle";

  $needle[] = "§";
  $replace[] = "\\S";

  $needle[] = "^";
  $replace[] = "\\^{}";

  for ($i=0; $i < count($needle); $i++)
    $txt = str_replace($needle[$i], $replace[$i], $txt);

  return $txt;
}

?>
