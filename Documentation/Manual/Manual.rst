#######
SUBMENU
#######

PI2
***

Plugin 'Infobox' for the 'nkwsubmenu' extension.

HOOK
====

Die Extension submenu verfügt in class.tx_nkwsubmenu_pi2, der Extension für die
rechte Sidebar, einen Hook um aus anderen Extensions Inhalte, wie Anker, Links
etc. in die Sidebar unter dem Punkt Inhaltsverzeichnis einfügen zu können.
Es stehen dabei zwei Möglichkeiten zu Verfügung, entweder kann Inhalt an die
bestehende Inhaltsliste angehängt oder diese durch Überschreiben komplett neu
generiert werden.
Neu dazu: Folgende Hooks sind nun nutzbar

* Zur Erweiterung des Inhaltsverzeichnisses der Seite
  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendTOC']
* Zur Erweiterung des Menü-Bilder auf der Seite $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['addImages']
* Zur Erweiterung der 'Mehr auf diesen Seiten'-Section der Seite $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendMoreOnThesePages']

Funktionsweise
--------------

Die Extension submenu prüft jedesmal wenn sie eingebunden wird, ob ein Eintrag
(userFunction) für sie vorliegt, führt diese (ggf.) aus, und übernimmt den Rück-
gabewert der Funktion in die temp. Variable $tmp.
Die Variable $tmp enthält bereits den Standard-Listeninhalt für das Inhaltsver-
zeichnis, dieser kann also weitergenutzt werden, wenn er erhalten bleiben soll
oder einfach überschrieben werden. Über die Variable $this wird die aktuelle
Klassen-Instanz mit an die UserFunction übergeben.

*class.tx_nkwsubmenu_pi2*

.. code-block:: php

	$tmp = "<li>...</li>\n<li>...</li>";
	// hook to extend table of contents (add anchors etc.)
	if(isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendTOC']))    {
		foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendTOC'] as $userFunc) {
			if($userFunc)   {
				t3lib_div::callUserFunction($userFunc, $tmp, $this);
			}
		}
	}
	if ($tmp) {
			$contentContent .= '<ul>'. $tmp . '</ul>';
	}

Beispiel
--------

Ein Beispiel für das Nutzen eines Hooks ist in der Extension patenschaften zu finden.

In der ext_localconf.php der Extension muss zuerst eine entsprechende Zeile angehängt
werden um die UserFunction in Typo3 zu registrieren.

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['nkwsubmenu']['extendTOC'][$_EXTKEY] = 'EXT:'.$_EXTKEY.'/pi1/class.tx_patenschaften_pi1.php:tx_patenschaften_pi1->hookFunc';


Die UserFunction kann dann einfach in class.tx_patenschaften_pi1.php implementiert
werden:

*class.tx_patenschaften_pi1.php*

.. code-block:: php

	public function hookFunc(&$tmp, &$obj) {
		$tmp .= '<li>';
		$tmp .= $obj->pi_linkTP($object->pi_getLL('infobox_previousbook'), array('tx_patenschaften_pi1[showBook]' => $books[$id-1]['uid']), 1);
		$tmp .= '</li>'."\n".'<li>';
	}
