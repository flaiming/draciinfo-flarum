<?php

function draci2bbcode($text) {
    // remove *a*
    $text = preg_replace("#\*/?a\*#", "", $text);

    $text = preg_replace("#(?<!\[)\*(/?)c\*#", "[\\1center]", $text);
    // url
    //$text = preg_replace("#\[[\w]+\]#", "", $text);
    $text = preg_replace_callback("#(?<!\*|>|/|\"|=|;|\])(?:\[(.[^\]|\[]+)\])(http://|https://|ftp://|www.)([\w-]+\.[\w.-]+([^\s\)]+)?)(?!\*|<)#", 
        function($matches) {
            return '[url='.($matches[2]=='www.'?'http://':'').$matches[2].$matches[3].']'.($matches[1]!=''?$matches[1]:((strlen($matches[2].$matches[3])>58)?(substr($matches[2].$matches[3],0,55).'...'):$matches[2].$matches[3])).'[/url]';
        },
        $text);
    // format tag and img combination
    $text = preg_replace("#[\*img[\d]*\*([^*]+)\*/img\*](http://|https://|ftp://|www.)([\w-]+\.[\w.-]+([^\s\)]+)?)#", "[url=\\2\\3][img]\\1[/img][/url]", $text);
    // format tag and img
    $text = preg_replace("#(?<!\[)\*(/?(?:b|i|u|p|s|img))[\d]*\*#", "[\\1]", $text);
    // smajliky
    $text = preg_replace("#\*([0-9]{1,2})\*#", "[img]/assets/smiles/\\1.gif[/img]", $text);
    return $text;
}

if (isset($_POST['text'])) {
    echo "Obsah následujícího pole použijte při vytváření nové dískuze na Flaru.<br />
    <textarea style='width: 100%; height: 200px;'>" . draci2bbcode($_POST['text']). "</textarea><br /><br />";
}

?>

<form method="post">
<label>Zadejte text s formátováním starého Draci.info pro převedení na formátování Flara:</label>
<textarea name="text" style="width: 100%; height: 200px;"></textarea>
<br />
<input type="submit" value="Odeslat"/>
</form>
