<?php
function QRZ($c) {
	return preg_replace('/((\d[A-Z]\d{1,3}[A-Z]{1,4})|([A-Z]{1,2}\d{1,3}[A-Z]{1,4}))/',
		"<a target=\"_blank\" rel=\"noreferrer\" href=\"https://qrz.com/db/$1\">$1</a>",
                 $c);
}
?>

<div class="container">
<div class="row">
<p>&nbsp;</p>
</div>
<div class="row">
  <div class="col">
    <h2>Acknowledgements</h2>
<?php
if (($handle = fopen(dirname(__FILE__) . "/thanks.txt", "r")) !== FALSE) {
    echo '<table class="table table-striped" style="max-width: 30em">';
    $col1 = '';
    $col2 = '';
    while (($data = fgets($handle)) !== FALSE) {
        $data = trim($data);
	if ($data == '') {
	    echo "<tr><td>$col1</td><td>$col2</td></tr>\n";
            $col1 = '';
	    $col2 = '';
	} elseif ($data[0] == ';') {
	    continue;	// ignore comment lines
	} elseif ($col1 == '') {
	    $col1 = QRZ($data);
	} else {
	    $col2 .= $data . "\n";
	}
    }
    if ($col1 !== '') {
        echo "<tr><td>$col1</td><td>$col2</td></tr>\n";
    }
    fclose($handle);
    echo "</table>";
} else {
    echo "<p>Unable to read data from thanks.txt</p>";
}
?>
  </div>
</div>
</div>
<script>
  clearTimeout(PageRefresh);
</script>

