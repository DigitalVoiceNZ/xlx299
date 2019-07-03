<div class="row" id="modtablecontainer">
  <div class="col-md-6">
    <h2>Modules</h2>
<?php
$modulesmd = file_get_contents(dirname(__FILE__) . '/modules.md');

if ($modulesmd !== FALSE) {
    require_once('./pgs/Parsedown.php');

    $Parsedown = new Parsedown();
    echo $Parsedown->text($modulesmd);
} else {
    echo "<p>Can not read modules.md</p>";
}
?>
  </div>
</div>
<script>
clearTimeout(PageRefresh);

var el = document.getElementById('modtablecontainer').getElementsByTagName('table')[0];
el.classList.add('table');
el.classList.add('table-striped');
</script>

