<?php

/**
 * Modules/FocusCmsCoreShortcodes/config/init.php
 */

return [

    /**
     * A modul aktiválásakor az alábbi konfigurációs kulcsok alapján
     * opciók kerülnek létrehozásra az `options` táblában, ha még nem léteznek.
     *
     * A megadott értékek konfigurációs útvonalak (config path).
     *
     * Feldolgozási szabályok:
     *
     * - Ha a config path `validation_rules.options.*` formátumú,
     *   akkor a konfigurációban található kulcsok kerülnek létrehozásra
     *   az `options` táblában, alapértelmezett értékként `null`-lal.
     *
     * - Egyéb konfiguráció esetén a config tömb kulcsai kerülnek
     *   létrehozásra az `options` táblában, a konfigurációban megadott
     *   értékkel.
     *
     * Példa:
     *
     * 'initialized_options' => [
     *     'module.focuscmscoreshortcodes.validation_rules.options.shortcodes',
     *     'module.focuscmscoreshortcodes.settings',
     * ]
     *
     * A modul eltávolításakor (`module:remove`) az itt felsorolt
     * konfigurációk alapján létrehozott opciók törlésre kerülnek.
     */
    'initialized_options' => [

    ]

];