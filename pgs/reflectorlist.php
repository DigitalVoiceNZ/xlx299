<?php

function GetReflectorList() {
    // if caching enabled, try to get cached version
    if (extension_loaded('apcu')) {
        $rl = apcu_fetch('reflectorlist', $success);
        if ($success) {
            return $rl;
        }
        $rl = FetchReflectorList();
        if(!empty($rl)) {
            // on successful fetch, cache for a few seconds
            apcu_store('reflectorlist', $rl, 900);
        }
        return $rl;
    }
    return FetchReflectorList();
}

function FetchReflectorList() {
    global $CallingHome;
    $Result = @fopen($CallingHome['ServerURL']."?do=GetReflectorList", "r");
    // return an empty array on failure
    if (!$Result) return Array();

    $INPUT = "";
    while (!feof ($Result)) {
        $INPUT .= fgets ($Result, 1024);
    }
    fclose($Result);

    $rl = [];
    $XML = new ParseXML();
    $Reflectorlist = $XML->GetElement($INPUT, "reflectorlist");
    $Reflectors    = $XML->GetAllElements($Reflectorlist, "reflector");
    for ($i=0;$i<count($Reflectors);$i++) {
        $rl[$XML->GetElement($Reflectors[$i], "name")] = [
            'country'      => $XML->GetElement($Reflectors[$i], "country"),
            'lastcontact'  => $XML->GetElement($Reflectors[$i], "lastcontact"),
            'comment'      => $XML->GetElement($Reflectors[$i], "comment"),
            'dashboardurl' => $XML->GetElement($Reflectors[$i], "dashboardurl"),
        ];
    }
    return $rl;
}

?>
